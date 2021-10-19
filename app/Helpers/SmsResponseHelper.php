<?php


namespace App\Helpers;

class SmsResponseHelper
{
    public $status = false;
    public $message;
    public $result;

    /**
     * @param mixed $result
     * @return SmsResponseHelper
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param mixed $message
     * @return SmsResponseHelper
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param mixed $status
     * @return SmsResponseHelper
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}