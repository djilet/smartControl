<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Building;

class BuildingChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $deletedFiles = [];
    private $addedFiles = [];
    private $user;
    private $building;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $deletedFiles, array $addedFiles, User $user, Building $building)
    {
        $this->deletedFiles = $deletedFiles;
        $this->addedFiles = $addedFiles;
        $this->user = $user;
        $this->building = $building;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.building_changed', [
            'user' => $this->user,
            'building' => $this->building,
            'deletedFiles' => $this->deletedFiles,
            'addedFiles' => $this->addedFiles,
        ])
        ->subject("Список измененных файлов у объекта ".$this->building->title);
    }
}
