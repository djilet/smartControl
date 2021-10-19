<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $id;
    private $password;

    /**
     * Create a new message instance.
     *
     * @param int $id
     * @param string $password
     */
    public function __construct(int $id, string $password)
    {
        $this->id = $id;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = User::find($this->id);
        $subject = 'Для вас создана учетная запись';

        return $this->view('email.user_created', [
                'user' => $user,
                'password' => $this->password,
            ])
            ->subject($subject);
    }
}
