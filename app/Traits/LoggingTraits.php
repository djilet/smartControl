<?php
namespace App\Traits;

use App\Facades\SmsGateway as SMS;
use App\Jobs\FirebaseMessagingJob;
use App\Models\Building;
use App\Models\BuildingFile;
use App\Models\BuildingWork;
use App\Models\CheckList;
use App\Models\CheckListDemand;
use App\Models\CheckListItem;
use App\Models\CheckListItemStatusHistory;
use App\Models\CheckListRenouncement;
use App\Models\Contractor;
use App\Models\ContractorItem;
use App\Models\ContractorItemEliminationDate;
use App\Models\History;
use App\Models\PushNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait LoggingTraits
{
    private static $hiddenFields = ['password'];

    protected static function boot()
    {
        parent::boot();

        self::created(function ($item) {
            $newValue = self::prepareAttributes($item->getAttributes());
            $history = new History([
                'model' => self::class,
                'model_id' => $item->id,
                'building_id' => self::getBuildingId(self::class, $item->id, $newValue, []),
                'user_id' => Auth::user()->id,
                'user' => self::prepareAttributes(Auth::user()->getAttributes()),
                'action' => 'create',
                'old_value' => '',
                'new_value' => $newValue,
                'created_at' => Carbon::now(),
            ]);
            $history->save();

            if (self::class == CheckListItem::class) {
                $status = $item->status;
                $historyStatus = new CheckListItemStatusHistory([
                    'user_id' => Auth::user()->id,
                    'status' => $status,
                    'created_at' => Carbon::now(),
                ]);
                $item->statusHistory()->save($historyStatus);

                // Оповещения
                self::checkListItemAdded($item);
            }
        });

        self::updated(function ($item) {
            $newValue = self::prepareAttributes($item->getAttributes());
            $oldValue = self::prepareAttributes($item->getOriginal());
            $history = new History([
                'model' => self::class,
                'model_id' => $item->id,
                'building_id' => self::getBuildingId(self::class, $item->id, $newValue, []),
                'user_id' => Auth::user()->id,
                'user' => self::prepareAttributes(Auth::user()->getAttributes()),
                'action' => 'update',
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'created_at' => Carbon::now(),
            ]);
            $history->save();

            if (self::class == CheckListItem::class) {
                $status = $item->changes['status'] ?? null;
                if ($status) {
                    $historyStatus = new CheckListItemStatusHistory([
                        'user_id' => Auth::user()->id,
                        'status' => $status,
                        'created_at' => Carbon::now(),
                    ]);
                    $item->statusHistory()->save($historyStatus);
                }
            }

            if (self::class == CheckList::class) {
                $status = $item->changes['status'] ?? null;
                if ($status == 'accepted' OR $status == 'accepted_part' OR $status == 'without_comments') {
                    if ($item->type == 'operating') {
                        self::checkListAccepted($item);
                    } else {
                        self::checkListKSAccepted($item);
                    }
                }
                if ($status == 'canceled') {
                    self::checkListCancelled($item);
                }
            }

        });

        self::deleted(function ($item) {
            $oldValue = self::prepareAttributes($item->getOriginal());
            $history = new History([
                'model' => self::class,
                'model_id' => $item->id,
                'building_id' => self::getBuildingId(self::class, $item->id, [], $oldValue),
                'user_id' => Auth::user()->id,
                'user' => self::prepareAttributes(Auth::user()->getAttributes()),
                'action' => 'delete',
                'old_value' => $oldValue,
                'new_value' => '',
                'created_at' => Carbon::now(),
            ]);
            $history->save();

            if (self::class == BuildingFile::class) {
                Storage::disk('local')->delete('files/building/'.$item->filename);
            }
        });
    }

    private static function prepareAttributes(array $attributes): array
    {
        foreach (self::$hiddenFields as $field) {
            if (isset($attributes[$field])) {
                unset($attributes[$field]);
            }
        }

        return $attributes;
    }


    /**
     * Добавлено новое замечание
     * @param CheckListItem $item
     */
    private static function checkListItemAdded(CheckListItem $item)
    {
        $contractorId = $item->contractor->id;
        $contractor = Contractor::findOrFail($contractorId);
        $users = User::setEagerLoads([])->where('contractor_id', $contractorId)->get();
        foreach ($users as $user) {
            $text = 'По объекту '.$item->building->title.' выставлено замечание №'.$item->id;
            $push = new PushNotification([
                'user_id' => $user->id,
                'model' => CheckListItem::class,
                'model_id' => $item->id,
                'title' => $text,
                'description' => '',
                'is_read' => false,
            ]);
            $push->save();

            FirebaseMessagingJob::dispatchByNotification($push);

            SMS::sendSms($contractor->phone, $text);
        }
    }

    /**
     * Проверка принята
     * @param CheckList $item
     */
    private static function checkListAccepted(CheckList $item)
    {
        $users = User::where('contractor_id', $item->contractor_id)->get();
        $contractor = Contractor::findOrFail($item->contractor_id);
        foreach ($users as $user) {
            $text = 'Работы по предписанию '.$item->id.' приняты';
            $push = new PushNotification([
                'user_id' => $user->id,
                'model' => CheckListItem::class,
                'model_id' => $item->id,
                'title' => $text,
                'description' => '',
                'is_read' => false,
            ]);
            $push->save();

            FirebaseMessagingJob::dispatchByNotification($push);

            SMS::sendSms($contractor->phone, $text);
        }
    }

    /**
     * Отказ от подписания КС
     * @param CheckList $item
     */
    private static function checkListCancelled(CheckList $item)
    {
        $users = User::where(function ($builder) use($item) {
            $builder->where('contractor_id', $item->contractor_id);
            $builder->orWhereIn('role_id', [2, 3]);
        })->get();
        foreach ($users as $user) {

            if ($user->contractor_id) {
                $contractor = Contractor::findOrFail($item->contractor_id);
                $phone = $contractor->phone;
            } else {
                $phone = $user->phone;
            }

            $text = 'Оформлен отказ от КС, потенциальный срыв графика финансирования';
            $push = new PushNotification([
                'user_id' => $user->id,
                'model' => CheckList::class,
                'model_id' => $item->id,
                'title' => $text,
                'description' => '',
                'is_read' => false,
            ]);
            $push->save();

            FirebaseMessagingJob::dispatchByNotification($push);

            SMS::sendSms($phone, $text);
        }
    }



    /**
     * Проверка КС принята
     * @param CheckList $item
     */
    private static function checkListKSAccepted(CheckList $item)
    {
        $users = User::where('role_id', 3)->get(); // role_id = 3 -> Инженер ПТО
        foreach ($users as $user) {
            $push = new PushNotification([
                'user_id' => $user->id,
                'model' => CheckList::class,
                'model_id' => $item->id,
                'title' => 'КС принято',
                'description' => '',
                'is_read' => false,
            ]);
            $push->save();
        }
    }

    private static function getBuildingId(string $model, int $modelId, array $newValues, array $oldValues)
    {
        switch ($model) {
            case Building::class:
                return $modelId;
            case BuildingWork::class:
                return $newValues['building_id'] ?? $oldValues['building_id'];
            case BuildingFile::class:
                $buildingWork = BuildingWork::find($newValues['building_work_id'] ?? $oldValues['building_work_id']);
                return $buildingWork->building_id ?? null;
            case ContractorItem::class:
                return $newValues['building_id'] ?? $oldValues['building_id'];
            case ContractorItemEliminationDate::class:
                $checkList = ContractorItem::find($newValues['contractor_item_id'] ?? $oldValues['contractor_item_id']);
                return $checkList->building_id ?? null;
            case CheckList::class:
                return $newValues['building_id'] ?? $oldValues['building_id'];
            case CheckListItem::class:
                $checkList = CheckList::find($newValues['check_list_id'] ?? $oldValues['check_list_id']);
                return $checkList->building_id ?? null;
            case CheckListRenouncement::class:
                $checkList = CheckList::find($newValues['check_list_id'] ?? $oldValues['check_list_id']);
                return $checkList->building_id ?? null;
            case CheckListDemand::class:
                $checkList = CheckList::select('check_lists.id')
                    ->join('check_list_items', 'check_list_items.check_list_id', 'check_lists.id')
                    ->where('check_list_items.id', $newValues['check_list_item_id'] ?? $oldValues['check_list_item_id'])
                    ->first();
                return $checkList->building_id ?? null;
            default:
                return null;
        }
    }
}
