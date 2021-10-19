<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonApiCollection;
use App\Models\CheckListDemand;
use App\Models\History;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class HistoryController extends Controller
{

    public function all(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $filter = $request->validate([
            'building_id' => 'int',
            'user_id' => 'int',
        ]);

        $builder = History::select('*')
            ->where('model', '<>', CheckListDemand::class)
            ->whereColumn('old_value', '<>', 'new_value');

        if (isset($filter['user_id'])) {
            $builder->where('user_id', $filter['user_id']);
        }
        if (isset($filter['building_id'])) {
            $builder->where('building_id', $filter['building_id']);
        }

        $items = $builder->orderBy('histories.id', 'desc')->paginate($perPage);

        $roles = Role::select('id', 'name')->get()->keyBy('id')->toArray();

        foreach ($items as $item) {
            if ($item->model == User::class) {
                if (empty($item->old_value) == false) {
                    $role = ['role' => $roles[$item->old_value['role_id']]] ?? [];
                    $item->old_value = array_merge($item->old_value, $role);
                }
                if (empty($item->new_value) == false) {
                    $role = ['role' => $roles[$item->new_value['role_id']]] ?? [];
                    $item->new_value = array_merge($item->new_value, $role);
                }
            }

            if (empty($item->user) == false) {
                $role = ['role' => $roles[$item->user['role_id']]] ?? [];
                $item->user = array_merge($item->user, $role);
            }
        }

        return new JsonApiCollection($items);
    }

    public function byUser(Request $request, int $id)
    {
        $perPage = $request->get('per_page', 25);
        $items = History::where('user_id', $id)->paginate($perPage);
        return new JsonApiCollection($items);
    }

    public function show(int $id)
    {
        return History::findOrFail($id);
    }

}
