<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\ReminderStatusEnum;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{

    private $verifyToken;
    private $pageAccessToken;
    public function __construct()
    {

        $this->verifyToken = config('services.facebook.fb_verify_token');
        $this->pageAccessToken = config('services.facebook.fb_page_access_token');
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function verifyWebhook(Request $request): Application|Response|ResponseFactory
    {
        if ($request->hub_verify_token === $this->verifyToken) {
            return response($request->hub_challenge, 200);
        }

        return response('Unauthorized', 403);
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function handleMessage(Request $request): Application|Response|ResponseFactory
    {
        $data = $request->all();

        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                foreach ($entry['messaging'] as $event) {
                    if (isset($event['message'])) {
                        $this->processMessage($event);
                    }
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processMessage($event): void
    {
        $senderId = $event['sender']['id'];
        $messageText = $event['message']['text'];

        $reminderData = $this->parseEvent($messageText);

        if ($reminderData) {
            Reminder::create([
                Reminder::FIELD_USER_ID => $senderId,
                Reminder::FIELD_MESSAGE => $reminderData['eventName'],
                Reminder::FIELD_REMINDER_TIME => Carbon::create($reminderData['time']),
                Reminder::FIELD_STATUS => ReminderStatusEnum::Wait,
            ]);

            $this->sendMessage($senderId, "Przypomnienie zostało zapisane!");
        } else {
            $this->sendMessage($senderId, "Nie rozumiem wiadomości. Spróbuj: '4.01.2025 12:35 Wizyta u lekarza'.");
        }
    }

    /**
     * @param $input
     * @return array
     */
    private function parseEvent($input): array
    {
        $pattern = '/^(\d{1,2}\.\d{1,2}\.\d{4})\s+(\d{1,2}:\d{2})\s+(.+)$/';

        if (preg_match($pattern, $input, $matches)) {
            return [
                'data' => $matches[1],
                'time' => $matches[2],
                'eventName' => $matches[3]
            ];
        } else {
            return [];
        }
    }

    /**
     * @param $recipientId
     * @param $message
     * @return void
     * @throws GuzzleException
     */
    private function sendMessage($recipientId, $message): void
    {

        $url = "https://graph.facebook.com/v13.0/me/messages?access_token={$this->pageAccessToken}";

        $client = new Client();
        $client->post($url, [
            'json' => [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $message],
            ],
        ]);
    }
}
