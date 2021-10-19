<?php


namespace App\Services\Contracts;

interface SmsServiceContract
{
    public function sendSms($phone, $message);
    public function status($message_id);
}