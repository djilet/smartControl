<?php

namespace App\Mail;

use App\Models\CheckList;
use App\Models\CheckListRenouncement;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckListMail extends Mailable
{
    use Queueable, SerializesModels;

    private $checkListId;

    /**
     * Create a new message instance.
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->checkListId = $id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $checkList = CheckList::findOrFail($this->checkListId);

        if ($checkList->status == 'canceled') {
            $renouncement = CheckListRenouncement::where(['check_list_id' => $this->checkListId])->firstOrFail();
            $domPdf = PDF::loadView('pdf.renouncement', ['renouncement' => $renouncement]);
            $subject = 'Отказ от предписания №'.$renouncement->id;
        } else {
            $domPdf = PDF::loadView('pdf.prescription', ['prescription' => $checkList]);
            $subject = 'Предписание №П/'.$checkList->id;
        }

        return $this->view('email.prescription', ['prescription' => $checkList])
            ->subject($subject)
            ->attachData($domPdf->output(), $subject.'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
