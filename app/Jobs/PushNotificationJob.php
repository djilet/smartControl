<?php

namespace App\Jobs;

use App\Facades\SmsGateway as SMS;
use App\Models\CheckListItem;
use App\Models\Contractor;
use App\Models\PushNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $items = CheckListItem::select('*')
            ->whereIn('status', ['red', 'yellow'])
            ->where('date_elimination', '>', Carbon::now())
            ->whereRaw('`date_elimination` <= DATE_ADD(NOW(), INTERVAL 1 DAY)')
            ->get();

        foreach ($items as $item) {
            $users = User::setEagerLoads([])
                ->where(function($builder) use($item) {
                    $builder->where('contractor_id', $item->contractor->id);
                    $builder->orWhere('role_id', 4); // Инженер строительного контроля
                })
                ->get();
            $contractor = Contractor::findOrFail($item->contractor->id);

            foreach ($users as $user) {
                $text = 'Истекает срок замечания '.$item->id.', но предписание не исправлено';
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
    }
}
