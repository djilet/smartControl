<?php


namespace App\Services;

use App\Services\Contracts\SmsServiceContract;
use App\Helpers\SmsResponseHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SigmaSmsService implements SmsServiceContract
{
    private $client;
    private $token;
    private $api = 'https://online.sigmasms.ru/api/';

    private $username;
    private $password;
    private $cache_time;

    public function __construct()
    {
        $this->username = config('sms.sigmasms.login');
        $this->password = config('sms.sigmasms.password');
        $this->cache_time = config('sms.sigmasms.time_cache');

        $this->client = new Client([
            'base_uri' => $this->api,
            'timeout'  => 10
        ]);

        if (!cache()->get('sigmasms')) {
            $this->token = $this->login();
            cache()->set('sigmasms_token', $this->token, $this->cache_time);
        } else {
            $this->token = cache()->get('sigmasms_token');
        }
    }

    /**
     * Send SMS message.
     *
     * @param $recipient
     * @param $text
     * @return SmsResponseHelper
     */
    public function sendSms($recipient, $text): SmsResponseHelper
    {
        try {
            $response = $this->client->post('sendings', [
                'headers' => [
                    'Authorization' => $this->token
                ],
                'json'    => [
                    'recipient' => $recipient,
                    'type'      => 'sms',
                    'payload'   => [
                        'sender' => config('sms.sigmasms.sender.sms'),
                        'text'   => $text
                    ]
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $messageResponse = (new SmsResponseHelper)->setStatus(true)->setResult($response->getBody()->getContents());
                $this->log($messageResponse, $text);
                return $messageResponse;
            } else {
                return (new SmsResponseHelper)->setStatus(false);
            }
        } catch (\Exception $e) {
            return (new SmsResponseHelper)->setStatus(false);
        }
    }

    /**
     * Send Viber message.
     *
     * @param $recipient
     * @param $text
     * @param $image
     * @param $button_text
     * @param $button_url
     * @return SmsResponseHelper
     */
    public function sendViber($recipient, $text, $image = null, $button_text = null, $button_url = null): SmsResponseHelper
    {
        try {
            $response = $this->client->post('sendings', [
                'headers' => [
                    'Authorization' => $this->token
                ],
                'json'    => [
                    'recipient' => $recipient,
                    'type'      => 'viber',
                    'payload'   => [
                        'sender' => config('sms.sigmasms.sender.viber'),
                        'text'   => $text,
                        'image'  => $image,
                        'button' => [
                            'text' => $button_text,
                            'url'  => $button_url
                        ]
                    ]
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $messageResponse = (new SmsResponseHelper)->setStatus(true)->setResult($response->getBody()->getContents());
                $this->log($messageResponse, $text);
                return $messageResponse;
            } else {
                return (new SmsResponseHelper)->setStatus(false);
            }
        } catch (\Exception $e) {
            return (new SmsResponseHelper)->setStatus(false);
        }
    }

    /**
     * Send WhatsApp message.
     *
     * @param $recipient
     * @param $text
     * @param null $image
     * @return mixed|null
     */
    public function sendWhatsApp($recipient, $text, $image = null): SmsResponseHelper
    {
        try {
            $response = $this->client->post('sendings', [
                'headers' => [
                    'Authorization' => $this->token
                ],
                'json'    => [
                    'recipient' => $recipient,
                    'type'      => 'whatsapp',
                    'payload'   => [
                        'sender' => config('sms.sigmasms.sender.whats_app'),
                        'text'   => $text,
                        'image'  => $image
                    ]
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $messageResponse = (new SmsResponseHelper)->setStatus(true)->setResult(json_decode($response->getBody()->getContents()));
                $this->log($messageResponse, $text);
                return $messageResponse;
            } else {
                return (new SmsResponseHelper)->setStatus(false);
            }
        } catch (\Exception $e) {
            return (new SmsResponseHelper)->setStatus(false);
        }
    }

    /**
     * Send VK message.
     *
     * @param $recipient
     * @param $text
     * @return SmsResponseHelper
     */
    public function sendVk($recipient, $text): SmsResponseHelper
    {
        try {
            $response = $this->client->post('sendings', [
                'headers' => [
                    'Authorization' => $this->token
                ],
                'json'    => [
                    'recipient' => $recipient,
                    'type'      => 'vk',
                    'payload'   => [
                        'sender' => config('sms.sigmasms.sender.vk'),
                        'text'   => $text
                    ]
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $messageResponse = (new SmsResponseHelper)->setStatus(true)->setResult(json_decode($response->getBody()->getContents()));
                $this->log($messageResponse, $text);
                return $messageResponse;
            } else {
                return (new SmsResponseHelper)->setStatus(false);
            }
        } catch (\Exception $e) {
            return (new SmsResponseHelper)->setStatus(false);
        }
    }

    public function status($message_id): SmsResponseHelper
    {
        try {
            $response = $this->client->get('sendings/' . $message_id, [
                'headers' => [
                    'Authorization' => $this->token
                ],
                'json'    => [],
            ]);

            if ($response->getStatusCode() === 200) {
                $messageResponse = (new SmsResponseHelper)->setStatus(true)->setResult(json_decode($response->getBody()->getContents()));
                $this->log($messageResponse);
                return $messageResponse;
            } else {
                return (new SmsResponseHelper)->setStatus(false);
            }
        } catch (\Exception $e) {
            return (new SmsResponseHelper)->setStatus(false);
        }
    }

    private function login()
    {
        try {
            $response = $this->client->post('login', [
                'json' => [
                    'username' => $this->username,
                    'password' => $this->password
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents());
                return $data->token;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    private function log(SmsResponseHelper $response, string $text = null)
    {
        Log::channel('smslog')->info('response_body: ' . $response->result);
        Log::channel('smslog')->info('response_message: ' . $response->message);
        Log::channel('smslog')->info('response_status: ' . $response->status);

        if ($text) {
            Log::channel('smslog')->info('sms_text: ' . $text);
        }
    }
}
