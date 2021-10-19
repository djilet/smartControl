<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    private $id;
    private $password;

    /**
     * Create a new message instance.
     *
     * @return void
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
        $subject = 'Ваш пароль изменен';

        return $this->view('email.user_password_changed', [
                'user' => $user,
                'password' => $this->password,
            ])
            ->subject($subject);
    }
}
