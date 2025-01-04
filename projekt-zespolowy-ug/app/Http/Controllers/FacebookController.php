<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\ReminderStatusEnum;
use App\Services\FacebookService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{

    private FacebookService $facebookService;
    public function __construct(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function verifyWebhook(Request $request): Application|Response|ResponseFactory
    {
        return $this->facebookService->verifyWebhook($request);
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
            $reminder = Reminder::create([
                Reminder::FIELD_USER_ID => $senderId,
                Reminder::FIELD_MESSAGE => $reminderData['eventName'],
                Reminder::FIELD_REMINDER_TIME => Carbon::create($reminderData['time']),
                Reminder::FIELD_STATUS => ReminderStatusEnum::Wait,
            ]);
            try {
                $this->facebookService->sendMessage($senderId, "Przypomnienie zostało zapisane!");
            } catch (GuzzleException $e) {
                $reminder->update([
                    Reminder::FIELD_STATUS => ReminderStatusEnum::Error
                ]);
                Log::error('Facebook error: '. $e->getMessage());
            }
        } else {
            try {
                $this->facebookService->sendMessage($senderId, "Nie rozumiem wiadomości. Spróbuj: '4.01.2025 12:35 Wizyta u lekarza'.");
            } catch (GuzzleException $e) {
                Log::error('Facebook error: '. $e->getMessage());
            }
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
}
