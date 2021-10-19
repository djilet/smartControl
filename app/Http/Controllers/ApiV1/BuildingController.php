<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonApiCollection;
use App\Models\Building;
use App\Models\BuildingFile;
use App\Models\BuildingWork;
use App\Models\CheckList;
use App\Models\CheckListItem;
use App\Models\Contractor;
use App\Models\ContractorItem;
use App\Models\TemporaryFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BuildingChangedMail;

class BuildingController extends Controller
{
    public function show(int $id, Request $request)
    {
        $validatedData = $request->validate([
            'is_actual_files' => 'bool',
        ]);
        $isOnlyActualFiles = $validatedData['is_actual_files'] ?? true;

        if ($isOnlyActualFiles) {
            $building = Building::with(['works.files' => function($query) {
                    $query->where('is_actual', true);
                }])
                ->findOrFail($id);
        } else {
            $building = Building::findOrFail($id);
        }

        return response()->json($building);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonApiCollection
     */
    public function all(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $buildings = Building::orderBy('closed', 'asc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        return new JsonApiCollection($buildings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'address' => 'required|string',
            'floors' => 'required|int',
            'floors_ext' => 'array',
            'floors_ext.*' => 'string',
            'responsible' => 'required|string',
            'area' => 'required|numeric',
            'closed' => 'boolean',
            'works' => 'json',
        ]);
        $validatedData['closed'] = $validatedData['closed'] ?? 0;

        $building = new Building($validatedData);
        $building->user_created = $request->user()->id;
        $building->created_at = Carbon::now();
        $building->updated_at = Carbon::now();
        $building->save();

        $storage = Storage::disk('local');
        $works = json_decode($validatedData['works'] ?? '[]', true);
        foreach ($works as $row) {
            $work_id = $row['work'] ?? 0;
            $contractor_id = $row['contractor'] ?? 0;
            if ($work_id == 0 || $contractor_id == 0) {
                continue;
            }

            $buildingWork = new BuildingWork();
            $buildingWork->building_id = $building->id;
            $buildingWork->work_id = $work_id;
            $buildingWork->contractor_id = $contractor_id;
            $building->works()->save($buildingWork);

            //добавляем объект в подрядную организацию, если его еще нет
            if ($work_id && $contractor_id) {
                $contractorItem = ContractorItem::where([
                    ['building_id', $building->id],
                    ['work_id', $work_id],
                    ['contractor_id', $contractor_id],
                ])->get();

                if ($contractorItem->isEmpty()) {
                    ContractorItem::create([
                        'building_id' => $building->id,
                        'work_id' => $work_id,
                        'contractor_id' => $contractor_id,
                        'cost' => 0,
                    ]);
                }
            }

            $files = $row['files'] ?? [];
            foreach ($files as $file) {
                if (is_array($file) == false) {
                    $file = [
                        'filename' => $file,
                        'is_actual' => true
                    ];
                }

                $filename = $file["filename"];
                $path = 'tmp/' . $filename;
                if (!$storage->exists($path)) {
                    continue;
                }

                $tmpFileDb = TemporaryFile::find($filename);

                $storage->move($path, 'files/building/' . $filename);
                $buildingFile = new BuildingFile();
                $buildingFile->building_work_id = $buildingWork->id;
                $buildingFile->filename = $filename;
                $buildingFile->user_filename = $tmpFileDb->user_filename;
                $buildingFile->is_actual = $file["is_actual"] ?? true;
                $buildingWork->files()->save($buildingFile);

                $tmpFileDb->delete();
            }
        }

        return response()->json($building->fresh(['works']), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'title' => 'string',
            'address' => 'string',
            'floors' => 'int',
            'floors_ext' => 'array',
            'floors_ext.*' => 'string',
            'responsible' => 'string',
            'area' => 'numeric',
            'closed' => 'boolean',
            'works' => 'json',
        ]);

        $building = Building::findOrFail($id);
        $building->update($validatedData);

        $removedBuildingFiles = [];
        $addedBuildingFiles = [];

        if (isset($validatedData['works'])) {
            $works = json_decode($validatedData['works'] ?? '[]', true);
            $files = Arr::flatten(Arr::pluck($works, 'files.*.filename'));

            foreach ($building->works as $work) {
                foreach ($work->files as $file) {
                    if (in_array($file->filename, $files) == false) {
                        $removedBuildingFiles[] = $file;
                        $file->delete();
                    }
                }

                if ($this->workInArray($work, $works) == false) {
                    $work->delete();
                }
            }

            $storage = Storage::disk('local');
            foreach ($works as $row) {
                $work_id = $row['work'] ?? 0;
                $contractor_id = $row['contractor'] ?? 0;
                if ($work_id == 0 || $contractor_id == 0) {
                    continue;
                }

                $buildingWork = BuildingWork::firstOrCreate([
                    'building_id' => $id,
                    'work_id' => $work_id,
                    'contractor_id' => $contractor_id,
                ]);

                //добавляем объект в подрядную организацию, если его еще нет
                if ($work_id && $contractor_id) {
                    $contractorItem = ContractorItem::where([
                        ['building_id', $building->id],
                        ['work_id', $work_id],
                        ['contractor_id', $contractor_id],
                    ])->get();

                    if ($contractorItem->isEmpty()) {
                        ContractorItem::create([
                            'building_id' => $building->id,
                            'work_id' => $work_id,
                            'contractor_id' => $contractor_id,
                            'cost' => 0,
                        ]);
                    }
                }

                $files = $row['files'] ?? [];
                foreach ($files as $file) {
                    if (is_array($file) == false) {
                        $file = [
                            'filename' => $file,
                            'is_actual' => true
                        ];
                    }

                    $filename = $file["filename"];
                    if ($buildingFile = $buildingWork->files->firstWhere('filename', $filename)) {
                        $buildingFile->is_actual = $file["is_actual"] ?? true;
                        $buildingFile->save();
                        continue;
                    }

                    $path = 'files/building/' . $filename;
                    if ($storage->exists($path)) {
                        $buildingFile = new BuildingFile();
                        $buildingFile->building_work_id = $buildingWork->id;
                        $buildingFile->filename = $filename;
                        $buildingFile->user_filename = $filename;
                        $buildingFile->is_actual = $file["is_actual"] ?? true;
                        $buildingWork->files()->save($buildingFile);
                        $addedBuildingFiles[] = $buildingFile;
                        continue;
                    }

                    $path = 'tmp/' . $filename;
                    if ($storage->exists($path)) {
                        $tmpFileDb = TemporaryFile::find($filename);

                        $storage->move($path, 'files/building/' . $filename);
                        $buildingFile = new BuildingFile();
                        $buildingFile->building_work_id = $buildingWork->id;
                        $buildingFile->filename = $filename;
                        $buildingFile->user_filename = $tmpFileDb->user_filename;
                        $buildingFile->is_actual = $file["is_actual"] ?? true;
                        $buildingWork->files()->save($buildingFile);

                        $addedBuildingFiles[] = $buildingFile;
                        $tmpFileDb->delete();
                    }
                }
            }
        }

        if (empty($removedBuildingFiles) == false || empty($addedBuildingFiles) == false) {
            $contractorEmails = $building->works
                ->pluck('contractor.email')
                ->filter(function ($value, $key) {
                    return empty($value) == false;
                });

            $userEmails = User::select('email')
                ->where('id', '!=', Auth::user()->id)
                ->get()
                ->pluck('email');

            $mails = $contractorEmails->merge($userEmails)
                ->unique()
                ->all();

            if (empty($mails) == false) { 
                $buildingChangedMail = new BuildingChangedMail(
                    $removedBuildingFiles, 
                    $addedBuildingFiles,
                    Auth::user(),
                    $building
                );
                Mail::to($mails)->queue($buildingChangedMail);
            }
        }

        return response()->json($building->fresh(['works']), 200);
    }

    private function workInArray(BuildingWork $work, array $items): bool
    {
        foreach ($items as $item) {
            $work_id = $item['work'] ?? 0;
            $contractor_id = $item['contractor'] ?? 0;
            if ($work_id == 0 || $contractor_id == 0) {
                continue;
            }

            if ($work->work_id == $work_id && $work->contractor_id == $contractor_id) {
                return true;
            }
        }

        return false;
    }

    public function analytics(Request $request, int $id = 0)
    {
        $builder = Building::setEagerLoads([]);
        if ($id == 0) {
            $perPage = $request->get('per_page', 25);
            $result = $builder->with('markers')->paginate($perPage);

            return new JsonApiCollection($result);
        }

        $result = $builder->with('worksAnalytic')->findOrFail($id);

        foreach ($result->worksAnalytic as $item) {
            $markers = CheckListItem::select('check_list_items.*', 'check_lists.floor')
                ->distinct()
                ->join('check_lists', 'check_lists.id', 'check_list_items.check_list_id')
                ->where('check_lists.building_id', $result->id)
                ->where('check_lists.work_id', $item->id)
                ->whereNull('check_lists.deleted_at',)
                ->join('contractors', 'contractors.id', 'check_lists.contractor_id')
                ->whereNull('contractors.deleted_at')
                ->groupBy('check_list_items.id')
                ->setEagerLoads([])
                ->get();
            $item->markers = $markers;

            $contractors = Contractor::select('contractors.id', 'contractors.title', 'check_lists.floor')
                ->distinct()
                ->join('check_lists', 'contractors.id', 'check_lists.contractor_id')
                ->where('check_lists.building_id', $result->id)
                ->where('check_lists.work_id', $item->id)
                ->setEagerLoads([])
                ->get();

            $contractorsGrouped = [];

            foreach ($contractors as $contractor) {
                $contractorInGroup = array_filter($contractorsGrouped, function ($item) use ($contractor) {
                    return $item['id'] === $contractor['id'];
                }, null);

                if ($contractorInGroup) {
                    $key = array_search($contractorInGroup[0], $contractorsGrouped);
                    $contractorsGrouped[$key]['floors'][] = $contractor['floor'];
                } else {
                    $contractorsGrouped[] = [
                        'id' => $contractor['id'],
                        'title' => $contractor['title'],
                        'floors' => [$contractor['floor']]
                    ];
                }
            }

            $item->contractors = $contractorsGrouped;
        }

        // Маркеры со множественной привязкой к типам работ
        $markers = CheckListItem::select('check_list_items.*', 'check_lists.floor')
            ->distinct()
            ->join('check_lists', 'check_lists.id', 'check_list_items.check_list_id')
            ->where('check_lists.building_id', $result->id)
            ->where('check_lists.work_id', null)
            ->setEagerLoads([])
            ->get();
        if ($markers->isEmpty() == false) {
            $result->markers = $markers;
        }

        return $result;
    }

    public function delete(int $id)
    {
        $building = Building::setEagerLoads([])->findOrFail($id);
        Gate::authorize('delete', $building);

        $result = $building->delete();

        return response()->json([
            'status' => $result ? 'success' : 'error',
        ]);
    }

    /**
     * Данные для диаграммы Ганта
     *
     * @param int $buildingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function ganttChart(int $buildingId)
    {
        if ($buildingId) {
            $building = Building::setEagerLoads([])->findOrFail($buildingId);

            $contractors = Contractor::setEagerLoads([])
                ->select('contractors.*')
                ->join('check_lists', 'check_lists.contractor_id', 'contractors.id')
                ->where('check_lists.building_id', $buildingId)
                ->whereNull('contractors.deleted_at')
                ->orderBy('contractors.title')
                ->distinct()
                ->get();

            $checkListItems = CheckListItem::setEagerLoads([])
                ->with('statusHistory')
                ->select('check_list_items.*', 'contractors.id as contractor_id')
                ->join('check_lists', 'check_lists.id', 'check_list_items.check_list_id')
                ->join('contractors', 'contractors.id', 'check_lists.contractor_id')
                ->where('check_lists.building_id', $buildingId)
                ->whereNull('check_lists.deleted_at')
                ->whereNull('contractors.deleted_at')
                ->orderBy('check_list_items.id')
                ->get();

            $building->contractors = $contractors;
            $building->items = $checkListItems;
        }

        return response()->json($building ?? []);
    }

}
