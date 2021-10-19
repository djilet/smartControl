<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonApiCollection;
use App\Models\BuildingWork;
use App\Models\CheckList;
use App\Models\CheckListItem;
use App\Models\CheckListItemStatusHistory;
use App\Models\Contractor;
use App\Models\ContractorFile;
use App\Models\ContractorItem;
use App\Models\ContractorItemEliminationDate;
use App\Models\TemporaryFile;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ContractorController extends Controller
{
    /**
     * Список подрядных организаций
     *
     * @param Request $request
     * @return JsonApiCollection
     */
    public function all(Request $request)
    {
        $filter = $request->validate([
            'building_id' => 'int',
            'work_id' => 'int',
            'contractor_id' => 'int',
        ]);

        /** @type string $query Строковый поисковый запрос */
        $query = $request->get('query', '');

        $builder = Contractor::with('items')->select(
            'contractors.*',
            DB::raw('MIN(IFNULL(`buildings`.`closed`, 1)) as `building_closed`')
        );
        $builder->leftJoin('contractor_items', 'contractors.id', 'contractor_items.contractor_id');
        $builder->leftJoin('buildings', 'contractor_items.building_id', 'buildings.id');

        if (empty($filter) == false) {
            if ($buildingId = $filter['building_id'] ?? null) {
                $builder->where('contractor_items.building_id', $buildingId);
            }
            if ($workId = $filter['work_id'] ?? null) {
                $builder->where('contractor_items.work_id', $workId);
            }
            if ($contractorId = $filter['contractor_id'] ?? null) {
                $builder->where('contractors.id', $contractorId);
            }
        }

        if ($query != '') {
            $builder->where(function ($builder) use ($query) {
                $builder->where('buildings.title', 'LIKE', '%' . $query . '%');
                $builder->orWhere('contractors.title', 'LIKE', '%' . $query . '%');
                $builder->orWhere('contractors.username', 'LIKE', '%' . $query . '%');
            });
        }

        $builder->groupBy('contractors.id', 'contractors.title',
            'contractors.username', 'contractors.email', 'contractors.phone',
            'contractors.created_at', 'contractors.updated_at');
        $builder->orderBy('building_closed', 'asc');
        $builder->orderBy('title', 'asc');

        $perPage = $request->get('per_page', 25);
        $contractors = $builder->paginate($perPage);
        return new JsonApiCollection($contractors);
    }

    /**
     * Создание подрядной организации
     *
     * @param Request $request
     * @return Contractor
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',

            'items.*.building_id' => 'required|int',
            'items.*.work_id' => 'required|int',
            'items.*.cost' => 'required|string',

            'items.*.elimination_dates.*.date' => 'required|date',
            'items.*.elimination_dates.*.sum' => 'required|string',

            'items.*.files' => 'array',
            'items.*.files.*' => 'string',
        ]);

        $contractor = new Contractor($validatedData);
        $contractor->created_at = Carbon::now();
        $contractor->updated_at = Carbon::now();
        $contractor->save();

        $storage = Storage::disk('local');
        $items = $validatedData['items'] ?? [];
        foreach ($items as $itemData) {
            $this->createContractorItem($itemData, $contractor, $storage);

            //добавляем подрядную организацию в объект, если ее еще нет
            BuildingWork::firstOrCreate([
                'building_id' => $itemData['building_id'],
                'work_id' => $itemData['work_id'],
                'contractor_id' => $contractor->id,
            ]);
        }

        return $contractor;
    }


    /**
     * @param array $itemData
     * @param Contractor $contractor
     * @param Filesystem $storage
     */
    private function createContractorItem(array $itemData, Contractor $contractor, Filesystem $storage): ContractorItem
    {
        $contractorItem = new ContractorItem($itemData);
        $contractor->items()->save($contractorItem);

        $files = $itemData['files'] ?? [];
        foreach ($files as $filename) {
            $tmpFile = TemporaryFile::find($filename);
            $path = 'tmp/' . $filename;
            if ($tmpFile == null || !$storage->exists($path)) {
                continue;
            }

            $storage->move($path, 'files/contractors/' . $filename);
            $image = new ContractorFile([
                'filename' => $filename,
                'user_filename' => $tmpFile->user_filename,
            ]);
            $contractorItem->files()->save($image);
        }

        $dates = $itemData['elimination_dates'] ?? [];
        foreach ($dates as $dateData) {
            $eliminationDate = new ContractorItemEliminationDate($dateData);
            $contractorItem->eliminationDates()->save($eliminationDate);
        }

        return $contractorItem;
    }

    /**
     * Просмотр подрядной организации
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        return Contractor::with('items')->findOrFail($id);
    }

    /**
     * Редактирование подрядной организации
     *
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function edit(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'title' => 'string',
            'username' => 'string',
            'email' => 'email',
            'phone' => 'string',

            'items.*.id' => 'int',
            'items.*.building_id' => 'required|int',
            'items.*.work_id' => 'required|int',
            'items.*.cost' => 'required|string',
            'items.*.editable' => 'boolean',

            'items.*.elimination_dates.*.id' => 'int',
            'items.*.elimination_dates.*.date' => 'required|date',
            'items.*.elimination_dates.*.sum' => 'required|string',

            'items.*.files' => 'array',
            'items.*.files.*' => 'string',

            'removed_items' => 'array',
            'removed_items.*' => 'int',
        ]);

        $contractor = Contractor::findOrFail($id);
        $contractor->update($validatedData);

        $storage = Storage::disk('local');
        $items = $validatedData['items'] ?? [];

        // Удаляем те элементы, ид которых не пришли
        $newIds = Arr::pluck($items, 'id');
        $removedIds = $contractor
            ->items
            ->except($newIds)
            ->pluck('id')
            ->all();
        ContractorItem::whereIn('id', $removedIds)->delete();

        foreach ($items as $itemData) {
            $itemId = $itemData['id'] ?? 0;
            if ($itemId == 0) { // новые данные
                $contractorItem = $this->createContractorItem($itemData, $contractor, $storage);
            } else {
                $contractorItem = ContractorItem::findOrFail($itemId);
                $contractorItem->update($itemData);

                $files = $itemData['files'] ?? [];
                $filesDb = $contractorItem->files;
                foreach ($filesDb as $fileDb) {
                    if (in_array($fileDb->filename, $files) == false) {
                        $fileDb->delete();
                        $storage->delete('files/contractors/' . $fileDb);
                    }
                    if (($key = array_search($fileDb->filename, $files)) !== false) {
                        unset($files[$key]);
                    }
                }

                foreach ($files as $filename) {
                    $tmpFile = TemporaryFile::find($filename);
                    $path = 'tmp/' . $filename;
                    if ($tmpFile == null || !$storage->exists($path)) {
                        continue;
                    }

                    $storage->move($path, 'files/contractors/' . $filename);
                    $image = new ContractorFile([
                        'filename' => $filename,
                        'user_filename' => $tmpFile->user_filename,
                    ]);
                    $contractorItem->files()->save($image);
                }
            }


            $dates = $itemData['elimination_dates'] ?? [];

            // Удаляем те элементы, ид которых не пришли
            $newIds = Arr::pluck($dates, 'id');
            $removedIds = $contractorItem
                ->eliminationDates
                ->except($newIds)
                ->pluck('id')
                ->all();
            ContractorItemEliminationDate::whereIn('id', $removedIds)->delete();

            foreach ($dates as $dateData) {
                if (isset($dateData['id'])) {
                    $date = ContractorItemEliminationDate::findOrFail($dateData['id']);
                    $date->update($dateData);
                } else {
                    $eliminationDate = new ContractorItemEliminationDate($dateData);
                    $contractorItem->eliminationDates()->save($eliminationDate);
                }
            }

            //добавляем подрядную организацию в объект, если ее еще нет
            BuildingWork::firstOrCreate([
                'building_id' => $itemData['building_id'],
                'work_id' => $itemData['work_id'],
                'contractor_id' => $contractor->id,
            ]);
        }


        $items = $validatedData['removed_items'] ?? [];
        $removedItems = ContractorItem::find($items);
        foreach ($removedItems as $removeItem) {
            foreach ($removedItems->files as $filename) {
                $storage->delete('files/contractors/' . $fileDb);
            }
            $removeItem->delete();
        }


        return $contractor;
    }

    /**
     * ФИО руководителей
     */
    public function names()
    {
        $list = Contractor::select('username')
            ->distinct()
            ->pluck('username')
            ->sort();

        return $list;
    }

    public function analytic(int $id, int $buildingId)
    {
        $contractor = Contractor::setEagerLoads([])->findOrFail($id);

        // Список работ с маркерами
        $worksAnalytics = $contractor->worksAnalytic($buildingId)->get();
        $contractor->works_analytic = $worksAnalytics;

        foreach ($worksAnalytics as $item) {
            $markers = CheckListItem::select('check_list_items.*', 'check_lists.floor')
                ->distinct()
                ->join('check_lists', 'check_lists.id', 'check_list_items.check_list_id')
                ->where('check_lists.contractor_id', $contractor->id)
                ->where('check_lists.work_id', $item->id)
                ->setEagerLoads([])
                ->with('demands')
                ->get();
            $item->markers = $markers;
        }

        // Список нарушений и процент
        $demands = $contractor->demandsAnalytic($buildingId);
        $demandsCount = $demands->count();
        $demandsGrouped = $demands->groupBy('regulatory');

        $result = [];
        foreach ($demandsGrouped as $key => $items) {
            $result[] = [
                'regulatory' => $key,
                'percent' => (count($items) / $demandsCount) * 100,
                'work_title' => $items[count($items) - 1]['work_title'],
            ];
        }

        $contractor->demands_analytics = $result;


        // Время на устранение замечаний
        foreach ($worksAnalytics as $item) {
            $durations = collect();
            foreach ($item->markers as $marker) {
                $datediff = DB::raw('DATEDIFF(MAX(CASE WHEN `status`="green" THEN `created_at` END),
                        MIN(CASE WHEN `status`="red" THEN `created_at` END)
                    ) AS `duration`');

                $result = CheckListItemStatusHistory::select($datediff)
                    ->where('check_list_item_id', $marker->id)
                    ->first();

                if ($result && ($duration = $result->duration) !== NULL) {
                    $durations->push($duration);
                }
            }

            $contractor->duration_min = $durations->min();
            $contractor->duration_max = $durations->max();
            $contractor->duration_avg = $durations->avg();
        }

        // Данные для финансовой аналитики - План
        $contractor->finance_plan = ContractorItemEliminationDate::select(DB::raw('SUM(`sum`) as `sum`'))
            ->join('contractor_items', 'contractor_items.id', 'contractor_item_elimination_dates.contractor_item_id')
            ->where('contractor_items.contractor_id', $contractor->id)
            ->where('contractor_items.building_id', $buildingId)
            ->first()
            ->sum;

        // Данные для финансовой аналитики - План на текущую дату
        $contractor->current_finance_plan = ContractorItemEliminationDate::select(DB::raw('SUM(`sum`) as `sum`'))
            ->join('contractor_items', 'contractor_items.id', 'contractor_item_elimination_dates.contractor_item_id')
            ->where('contractor_items.contractor_id', $contractor->id)
            ->where('contractor_items.building_id', $buildingId)
            ->where('contractor_item_elimination_dates.date', '<=', Carbon::today())
            ->first()
            ->sum;

        // Данные для финансовой аналитики - Факт
        $contractor->finance_fact = CheckList::select(DB::raw('SUM(`sum_ks`) as `sum_ks`'))
            ->setEagerLoads([])
            ->where('contractor_id', $contractor->id)
            ->where('building_id', $buildingId)
            ->whereIn('status', ['accepted', 'accepted_part'])
            ->first()
            ->sum_ks;

        // Дата последнего платежа
        $lastPayment = CheckList::select('created_at')
            ->setEagerLoads([])
            ->where('contractor_id', $contractor->id)
            ->where('building_id', $buildingId)
            ->whereIn('status', ['accepted', 'accepted_part'])
            ->orderBy('created_at', 'desc')
            ->first();
        $contractor->finance_last_payment = $lastPayment ? $lastPayment->created_at : null;

        return $contractor;
    }

    public function finance(int $id, int $buildingId)
    {
        $contractor = Contractor::setEagerLoads([])->with('items')->findOrFail($id);

        // Данные для финансовой аналитики - План
        $financePlan = ContractorItemEliminationDate::select('date', DB::raw('SUM(`sum`) as `sum`'))
            ->join('contractor_items', 'contractor_items.id', 'contractor_item_elimination_dates.contractor_item_id')
            ->where('contractor_items.contractor_id', $contractor->id)
            ->where('contractor_items.building_id', $buildingId)
            ->groupBy('date')
            ->get()
            ->groupBy(function($item) {
                return $item->date->format('d.m.Y');
            })
            ->map
            ->sum('sum')
            ->toArray();

        // Данные для финансовой аналитики - Факт
        $financeFact = CheckList::select('date', DB::raw('SUM(`sum_ks`) as `sum_ks`'))
            ->setEagerLoads([])
            ->where('contractor_id', $contractor->id)
            ->where('building_id', $buildingId)
            ->whereIn('status', ['accepted', 'accepted_part'])
            ->groupBy('date')
            ->get()
            ->groupBy(function($item) {
                return $item->date->format('d.m.Y');
            })
            ->map
            ->sum('sum_ks')
            ->toArray();

        // Неустраненные замечания
        $financeMarkers = CheckListItem::select('date', DB::raw('COUNT(check_list_items.id) as items_count'))
            ->join('check_lists', 'check_lists.id', 'check_list_items.check_list_id')
            ->where('check_lists.contractor_id', $contractor->id)
            ->where('check_lists.building_id', $buildingId)
            ->whereIn('check_list_items.status', ['red', 'yellow'])
            ->whereNotNull('check_lists.work_id')
            ->groupBy('date')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->date)->format('d.m.Y');
            })
            ->map
            ->sum('items_count')
            ->toArray();

        $dates = array_unique(array_merge(array_keys($financePlan), array_keys($financeFact)));
        usort($dates, function($lhs, $rhs) {
            return strtotime($lhs) <=> strtotime($rhs);
        });

        $contractor->financial_analytics = collect();
        $growingPlan = 0;
        $growingFact = 0;
        foreach ($dates as $date) {
            $growingPlan += $financePlan[$date] ?? 0;
            $growingFact += $financeFact[$date] ?? 0;
            $contractor->financial_analytics->push([
                'date' => $date,
                'plan' => $financePlan[$date] ?? null,
                'fact' => $financeFact[$date] ?? null,
                'growing_plan' => $growingPlan,
                'growing_fact' => $growingFact,
                'markers_count' => $financeMarkers[$date] ?? null,
            ]);
        }

        return $contractor;
    }

    public function delete(int $id)
    {
        $contractor = Contractor::setEagerLoads([])->findOrFail($id);
        Gate::authorize('delete', $contractor);

        $result = $contractor->delete();
        return response()->json([
            'status' => $result ? 'success' : 'error',
        ]);
    }

}
