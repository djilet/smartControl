<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonApiCollection;
use App\Models\PushNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{

    public function all(Request $request)
    {
        $validData = $request->validate([
            'from' => 'date|date_format:Y-m-d',
            'to' => 'date|date_format:Y-m-d',
        ]);
        $validData['from'] =  $validData['from'] ?? Carbon::now()->addDays(-14)->format('Y-m-d');

        $builder = PushNotification::where('user_id', Auth::user()->id)
            ->where('created_at', '>=', $validData['from'].' 00:00:00')
            ->orderBy('is_read', 'asc')
            ->orderBy('id', 'desc');

        if (isset($validData['to'])) {
            $builder->where('created_at', '<=', $validData['to'].' 23:59:59');
        }

        return $builder->get();
    }

    /**
     * Отметить оповещение как прочитанное
     *
     * @param int $id
     * @return JsonResponse
     */
    public function reading(int $id)
    {
        PushNotification::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->update([
            'is_read' => true
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Отметить оповещение как прочитанное
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function readings(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'array|required',
            'id.*' => 'int',
        ]);

        PushNotification::where('user_id', Auth::user()->id)
            ->whereIn('id', $validatedData['id'])
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
        ]);
    }

}
