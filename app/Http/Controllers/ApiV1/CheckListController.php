<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonApiCollection;
use App\Mail\CheckListMail;
use App\Models\CheckList;
use App\Models\CheckListDemand;
use App\Models\CheckListImage;
use App\Models\CheckListItem;
use App\Models\CheckListRenouncement;
use App\Models\Contractor;
use App\Models\TemporaryFile;
use App\Helpers\PdfHelper;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckListController extends Controller
{

    /**
     * Список предписаний
     *
     * @param Request $request
     * @return JsonApiCollection
     */
    public function all(Request $request)
    {
        $where = $request->validate([
            'work_id' => 'int',
            'contractor_id' => 'int',
            'building_id' => 'int',
            'floor' => 'string',
        ]);

        $sort = $request->validate([
            'filter_markers' => 'bool',
            'filter_date_from' => 'date',
            'filter_date_to' => 'date',
        ]);

        $perPage = $request->get('per_page', 25);
        $query = CheckList::where($where);
        if (isset($sort['filter_markers'])) {
            $query->select(
                'check_lists.*',
                DB::raw('MIN(check_list_items.date_elimination) as `date_elimination_min`'),
                DB::raw('COUNT(check_list_items.id) as `markers_count`')
            );
            $query
                ->join('check_list_items', 'check_lists.id', '=', 'check_list_items.check_list_id')
                ->whereIn('check_list_items.status', ['red', 'yellow']);

            $query->groupBy('check_lists.id', 'check_lists.date', 'check_lists.type',
                'check_lists.building_id', 'check_lists.contractor_id', 'check_lists.floor',
                'check_lists.work_id', 'check_lists.number_ks', 'check_lists.sum_ks',
                'check_lists.date_from', 'check_lists.date_to', 'check_lists.status',
                'check_lists.created_at', 'check_lists.updated_at', 'creator_id',
                'contractor_representative', 'pdf_filename');

            $query
                ->orderBy(DB::raw('date_elimination_min IS NULL, date_elimination_min'), 'asc')
                ->orderBy('markers_count', 'desc');
        } else {
            $query->orderBy('date', 'desc');
        }

        if (empty($sort['filter_date_from']) == false) {
            $query->where('date', '>=', $sort['filter_date_from']);
        }

        if (empty($sort['filter_date_to']) == false) {
            $query->where('date', '<=', $sort['filter_date_to']);
        }

        $list = $query->paginate($perPage);
        return new JsonApiCollection($list);
    }

    /**
     * Создание Предписания(он же акт, он же проверка)
     *
     * items - массив маркеров
     * check_list_id - список актов(предписания) для формирования отказа от КС
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|string|in:operating,acceptance',
            'building_id' => 'required|int|exists:buildings,id',
            'contractor_id' => 'int|exists:contractors,id',
            'work_id' => 'int|exists:works,id',
            'floor' => 'required|string',
            'contractor_representative' => 'string',
            'number_ks' => 'string',
            'sum_ks' => 'string',
            'date_from' => 'date',
            'date_to' => 'date',
            'status' => 'string|in:with_comments,without_comments,open,accepted,accepted_part,canceled,draft',

            'items' => 'array',
            'items.*.image' => 'required|string',
            'items.*.image_size' => 'required|string',
            'items.*.image_crop' => 'string',
            'items.*.coor' => 'required|string',
            'items.*.desc' => 'string',
            'items.*.status' => 'required|string|in:red,green,yellow,grey',
            'items.*.date_elimination' => 'date',
            'items.*.scale' => 'numeric',
            'items.*.images' => 'array',
            'items.*.unloaded_images' => 'array',
            'items.*.unloaded_images.*.image_base_64' => 'required|base64image',
            'items.*.unloaded_images.*.filename' => 'required|string',
            'items.*.demands' => 'array',
            'items.*.demands.*.description' => 'required|string',
            'items.*.demands.*.regulatory' => 'required|string',

            'check_list_id' => 'array',
            'check_list_id.*' => 'integer',
            
            'draft_id' => 'integer',
        ]);

        $checkList = new CheckList($validatedData);
        $checkList->date = Carbon::now();
        $checkList->creator_id = Auth::user()->id;
        $checkList->save();

        $storage = Storage::disk('local');
        $items = $validatedData['items'] ?? [];
        foreach ($items as $itemData) {

            /** @warning Выпилить после отладки по image_size */
            $itemData['scale'] = $itemData['scale'] ?? 1;

            $item = new CheckListItem($itemData);
            $checkList->items()->save($item);

            // сохранить незагруженные ранее картинки замечаний
            $unloadedImages = $itemData['unloaded_images'] ?? [];
            foreach ($unloadedImages as $image) {
                $encodeImage = explode( ',', $image['image_base_64'])[1];
                $encodeImage = str_replace(' ', '+', $encodeImage);
                $decodeImage = base64_decode($encodeImage);

                $pathInfo = pathinfo($image['filename']);
                $filename = Str::uuid() . '.' . $pathInfo['extension'];
                Storage::disk('local')->put('tmp/' . $filename, $decodeImage);

                $tmpFileDb = new TemporaryFile();
                $tmpFileDb->id = $filename;
                $tmpFileDb->user_filename = $image['filename'];
                $tmpFileDb->created_at = Carbon::now();
                $tmpFileDb->save();

                $itemData['images'][] = $filename;
            }

            $images = $itemData['images'] ?? [];
            foreach ($images as $image) {
                $filename = $image;
                $userFilename = $image;
                if(isset($image['serverFileName'])) {
                    $path = explode('/', $image['serverFileName']);
                    $filename = $path[count($path) - 1];
                    $userFilename = $image['filename'];
                }
                
                $tmpFile = TemporaryFile::find($filename);
                $path = 'tmp/'.$filename;
                if ($tmpFile != null && $storage->exists($path)) {
                    $storage->move($path, 'files/markers/'.$filename);
                    $image = new CheckListImage([
                        'filename' => $filename,
                        'user_filename' => $tmpFile->user_filename,
                    ]);
                    $item->files()->save($image);
                }
                else if($storage->exists('files/markers/'.$filename)){ // copy file from draft check-list
                    $pathInfo = pathinfo($filename);
                    $newFilename = Str::uuid() . '.' . $pathInfo['extension'];
                    $storage->copy('files/markers/'.$filename, 'files/markers/'.$newFilename);
                    $image = new CheckListImage([
                        'filename' => $newFilename,
                        'user_filename' => $userFilename,
                    ]);
                    $item->files()->save($image);
                }
            }

            $demands = $itemData['demands'] ?? [];
            foreach ($demands as $demandData) {
                $demand = new CheckListDemand($demandData);
                $item->demands()->save($demand);
            }
        }

        // Создание pdf файла, если есть замечания
        if ($checkList->status !== 'without_comments' && $checkList->status !== 'draft' && count($items) > 0) {
            $filename = $checkList->createPdf();

            if ($filename != false) {
                $checkList->update(['pdf_filename' => $filename]);
            }
        }

        // Проверка, нужно ли создать Отказ от подписания КС
        if ($checkList->status == 'canceled' && empty($validatedData['check_list_id']) == false) {
            $renouncement = new CheckListRenouncement();
            $renouncement->user_id = Auth::user()->id;
            $renouncement->check_list_id = $checkList->id;
            $renouncement->save();

            $list = CheckList::find($validatedData['check_list_id']);
            $renouncement->items()->saveMany($list);

            $filename = $renouncement->createPdf();

            if ($filename != false) {
                $renouncement->update(['pdf_filename' => $filename]);
            }
        }
        
        // Удалить черновик, по которому создана проверка
        if (empty($validatedData['draft_id']) == false) {
            $checkList = CheckList::setEagerLoads([])->findOrFail($validatedData['draft_id']);
            $checkList->delete();
        }

        return response()->json($checkList->fresh(['items']), 201);
    }
    
    /**
     * Просмотр предписания
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        return CheckList::with('items')->findOrFail($id);
    }

    /**
     * Обновление предписания
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'building_id' => 'int|exists:buildings,id',
            'contractor_id' => 'int|exists:contractors,id',
            'floor' => 'string',
            'work_id' => 'int|exists:works,id',
            'contractor_representative' => 'string',
            'number_ks' => 'string',
            'sum_ks' => 'string',
            'date_from' => 'date',
            'date_to' => 'date',
            'status' => 'string|in:with_comments,without_comments,open,accepted,accepted_part,canceled,draft',
            
            'items' => 'array',
            'items.*.id' => 'integer',
            'items.*.image' => 'required|string',
            'items.*.image_size' => 'required|string',
            'items.*.image_crop' => 'string',
            'items.*.coor' => 'required|string',
            'items.*.desc' => 'string',
            'items.*.status' => 'required|string|in:red,green,yellow,grey',
            'items.*.date_elimination' => 'date',
            'items.*.scale' => 'numeric',
            'items.*.images' => 'array',
            'items.*.unloaded_images' => 'array',
            'items.*.unloaded_images.*.image_base_64' => 'required|base64image',
            'items.*.unloaded_images.*.filename' => 'required|string',
            'items.*.demands' => 'array',
            'items.*.demands.*.description' => 'required|string',
            'items.*.demands.*.regulatory' => 'required|string',

            'check_list_id' => 'array',
            'check_list_id.*' => 'integer',
        ]);

        $checkList = CheckList::findOrFail($id);
        $checkList->update($validatedData);

        if ($checkList->status == 'canceled' && empty($validatedData['check_list_id']) == false) {
            DB::delete('DELETE i FROM `check_list_renouncement_items` as i
                INNER JOIN `check_list_renouncements` as c ON i.renouncement_id = c.id
                WHERE c.check_list_id=?', [$id]);

            $renouncement = CheckListRenouncement::where('check_list_id', $id)->first();
            $list = CheckList::find($validatedData['check_list_id']);
            $renouncement->items()->saveMany($list);
        }

        return response()->json($checkList, 201);
    }

    /**
     * Добавление маркера
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'image' => 'required|string',
            'image_size' => 'required|string',
            'image_crop' => 'string',
            'coor' => 'required|string',
            'desc' => 'string',
            'status' => 'required|string|in:red,green,yellow,grey',
            'date_elimination' => 'date',
            'scale' => 'numeric',
            'images' => 'array',
            'demands' => 'array',
            'demands.*.description' => 'required|string',
            'demands.*.regulatory' => 'required|string',
        ]);
        $validatedData['scale'] = $validatedData['scale'] ?? 1;

        $item = new CheckListItem($validatedData);
        $item->check_list_id = $id;
        $item->save();

        $storage = Storage::disk('local');
        $images = $validatedData['images'] ?? [];
        foreach ($images as $filename) {
            $tmpFile = TemporaryFile::find($filename);
            $path = 'tmp/'.$filename;
            if ($tmpFile == null || !$storage->exists($path)) {
                continue;
            }

            $storage->move($path, 'files/markers/'.$filename);
            $image = new CheckListImage([
                'filename' => $filename,
                'user_filename' => $tmpFile->user_filename,
            ]);
            $item->files()->save($image);
        }

        $demands = $validatedData['demands'] ?? [];
        foreach ($demands as $demand) {
            $checkListDemand = new CheckListDemand($demand);
            $item->demands()->save($checkListDemand);
        }

        return response()->json($item->fresh('files'), 201);
    }

    /**
     * Просмотр маркера
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showItem(int $id)
    {
        $item = CheckListItem::with('building', 'contractor', 'work')->findOrFail($id);
        return response()->json($item);
    }

    /**
     * Обновление маркера
     *
     * @param Request $request
     * @param int $id   Ид маркера
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'image' => 'string',
            'image_size' => 'string',
            'image_crop' => 'string',
            'coor' => 'string',
            'desc' => 'string',
            'status' => 'string|in:red,green,yellow,grey',
            'date_elimination' => 'date',
            'scale' => 'numeric',
        ]);

        $item = CheckListItem::findOrFail($id);
        $item->update($validatedData);

        $status = $item->getChanges()['status'] ?? null;
        if (empty($status) == false) {
            $checkList = CheckList::find($item->check_list_id);
            if ($checkList->type == 'operating') {
                $closeStatus = 'without_comments';
                $openStatus = 'with_comments';

                if ($status == 'green') {
                    // Если все маркеры зеленые, то изменяем статус проверки на Принят
                    $count = $checkList->items->filter(function ($value, $key) {
                        return $value->status != 'green';
                    })->count();
                    if ($count == 0 && $checkList->status != $closeStatus) {
                        $checkList->update(['status' => $closeStatus]);
                    }
                } else if ($checkList->status != $openStatus) {
                    $checkList->update(['status' => $openStatus]);
                }
            }
        }

        $item->load('building', 'contractor', 'work');

        return response()->json($item->refresh(['statusHistory']));
    }

    /**
     * Обновление статуса у нескольких маркеров
     *
     * ids - массив ид маркеров
     * status - новый статус
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusItems(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'status' => 'string|in:red,green,yellow,grey',
        ]);

        $list = CheckListItem::findOrFail($validatedData['ids']);
        $list->each(function($item) use ($validatedData) {
            $item->update(['status' => $validatedData['status']]);
        });

        return response()->json(['status' => 'success']);
    }

    /**
     * Схема для маркера
     * @param int $id   Ид маркера
     */
    public function schemaItem(int $id)
    {
        $item = CheckListItem::findOrFail($id);
        $checkList = CheckList::setEagerLoads([])->findOrFail($item->check_list_id);
        $checkList->items = collect([$item]);

        $pdf = PDF::loadView('pdf.schema', ['prescription' => $checkList]);
        $filename = "Схема для маркера №{$item->id} предписания №П_{$checkList->id}.pdf";
        $filenameLat = "Shema dlya markera №{$item->id} predpisaniya №P_{$checkList->id}.pdf";

        return response($pdf->output(), 200, [
                'Content-Encoding' => 'UTF-8',
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename*=utf-8\'\''.rawurlencode($filename).'; filename="'.$filenameLat.'"',
            ]
        );
    }

    /**
     * Получение PDF предписания
     *
     * @param int $id Ид предписания
     */
    public function pdf(int $id)
    {
        $checkList = CheckList::findOrFail($id);
        $filename = "Предписание №П_{$checkList->id}.pdf";
        $filenameLat = "Predpisanie №P_{$checkList->id}.pdf";
        $storage = Storage::disk('local');
        $path = 'files/check-lists/'.$checkList->pdf_filename;

        if ($checkList->pdf_filename == null || $storage->exists($path) == false) {
            return response()->json(['errors' => 'File not found'], 404);
        }

        return response($storage->get($path), 200, [
                'Content-Encoding' => 'UTF-8',
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename*=utf-8\'\''.rawurlencode($filename).'; filename="'.$filenameLat.'"',
            ]
        );
    }

    /**
     * Формирование PDF. Только схема с маркерами и таблицей
     *
     * @param int $id
     */
    public function schema(int $id)
    {
        $item = CheckList::findOrFail($id);
        $pdf = PDF::loadView('pdf.schema', ['prescription' => $item]);
        $filename = "Схема предписания №П_{$item->id}.pdf";
        $filenameLat = "Shema predpisaniya №P_{$item->id}.pdf";

        return response($pdf->output(), 200, [
                'Content-Encoding' => 'UTF-8',
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename*=utf-8\'\''.rawurlencode($filename).'; filename="'.$filenameLat.'"',
            ]
        );
    }

    /**
     * Получение данных по отказу от подписания КС
     *
     * @param int $id Ид отказа
     */
    public function showRenouncement(int $id)
    {
        return CheckListRenouncement::findOrFail($id);
    }

    /**
     * Получение PDF отказа от подписания КС
     *
     * @param int $id Ид предписания, для которого сделан отказ
     */
    public function renouncementPdf(int $id)
    {
        $renouncement = CheckListRenouncement::where(['check_list_id' => $id])->firstOrFail();
        $filename = "Отказ от подписания КС_№{$renouncement->id}.pdf";
        $filenameLat = "Otkaz ot podpisaniya KS_№{$renouncement->id}.pdf";
        $storage = Storage::disk('local');
        $path = 'files/renouncements/'.$renouncement->pdf_filename;

        if ($renouncement->pdf_filename == null || $storage->exists($path) == false) {
            return response()->json(['errors' => 'File not found'], 404);
        }

        return response($storage->get($path), 200, [
                'Content-Encoding' => 'UTF-8',
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename*=utf-8\'\''.rawurlencode($filename).'; filename="'.$filenameLat.'"',
            ]
        );
    }

    /**
     * Создание отказа от подписания КС
     *
     * @param Request $request
     * @param int $id Ид предписания, для которого делается отказ
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createRenouncement(Request $request, int $id)
    {
        $validatedDate = $request->validate([
            'check_list_id' => 'required|array',
            'check_list_id.*' => 'integer',
        ]);

        CheckList::find($id)->update(['status' => 'canceled']);

        $renouncement = CheckListRenouncement::setEagerLoads([])->firstOrNew([
            'check_list_id' => $id,
        ]);
        if ($renouncement->exists == false) {
            $renouncement->user_id = Auth::user()->id;
        }
        $renouncement->save();

        DB::delete('DELETE FROM `check_list_renouncement_items`
                WHERE renouncement_id=?', [$renouncement->id]);
        $list = CheckList::find($validatedDate['check_list_id']);
        $renouncement->items()->saveMany($list);

        if ($renouncement->pdf_filename == null) {
            $filename = $renouncement->createPdf();

            if ($filename != false) {
                $renouncement->update(['pdf_filename' => $filename]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Список Представителей подрядной организации
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function contractorRepresentative(Request $request)
    {
        $validatedData = $request->validate([
            'query' => 'string|min:3',
        ]);

        if (empty($validatedData['query']) == false) {
            $list = CheckList::contractorRepresentativeSearch($validatedData['query'])->get();
        } else {
            $list = CheckList::select('contractor_representative')
                ->distinct()
                ->setEagerLoads([])
                ->where('contractor_representative', '<>', '')
                ->get();
        }

        $list = $list->pluck('contractor_representative');

        return response()->json($list);
    }

    /**
     * Список ранее вводимых описаний требования маркера
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function demands()
    {
        $list = CheckListDemand::select('description')
            ->distinct()
            ->get()
            ->pluck('description');

        return response()->json($list);
    }

    /**
     * Список этажей, которые есть в предписаниях
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function floors()
    {
        $list = CheckList::select('floor')
            ->setEagerLoads([])
            ->distinct()
            ->get()
            ->pluck('floor');

        return response()->json($list);
    }

    /**
     * Список Подрядных организаций, которые есть в предписаниях
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function contractors()
    {
        $checkList = new CheckList();
        $contractor = new Contractor();

        $list = $checkList->select($contractor->getTable().'.id', $contractor->getTable().'.title')
            ->setEagerLoads([])
            ->distinct()
            ->join($contractor->getTable(), $checkList->getTable().'.contractor_id', $contractor->getTable().'.id')
            ->get();

        return response()->json($list);
    }


    /**
     * Отправка предписания или отказа на Email
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function prescriptionMail(int $id)
    {
        $checkList = CheckList::findOrFail($id);
        $sendTo = $checkList->contractor->email;
        if (empty($sendTo)) {
            return response()->json([
                'status' => 'error',
                'message' => 'E-mail подрядчика не найден'
            ]);
        }

        Mail::to($sendTo)->queue(new CheckListMail($id));

        return response()->json(['status' => 'success']);
    }

    public function delete(int $id)
    {
        $checkList = CheckList::setEagerLoads([])->findOrFail($id);
        Gate::authorize('delete', $checkList);

        $result = $checkList->delete();
        return response()->json([
            'status' => $result ? 'success' : 'error',
        ]);
    }

}
