<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use GuzzleHttp\Client;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    public function verifyWebhook(Request $request): Application|Response|ResponseFactory
    {
        $verifyToken = env('FB_VERIFY_TOKEN');

        if ($request->hub_verify_token === $verifyToken) {
            return response($request->hub_challenge, 200);
        }

        return response('Unauthorized', 403);
    }

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

        // Analiza wiadomości
        $reminderData = $this->extractReminderData($messageText);

        if ($reminderData) {
            // Zapisz przypomnienie
            Reminder::create([
                'user_id' => $senderId,
                'message' => $reminderData['message'],
                'reminder_time' => $reminderData['time'],
            ]);

            // Wyślij potwierdzenie
            $this->sendMessage($senderId, "Przypomnienie zostało zapisane!");
        } else {
            $this->sendMessage($senderId, "Nie rozumiem wiadomości. Spróbuj: 'Jutro wizyta u lekarza 12:00'.");
        }
    }

    private function extractReminderData($message): ?array
    {
        // Prosty parser do analizy wiadomości
        if (preg_match('/Jutro (.+) (\d{2}:\d{2})/', $message, $matches)) {
            return [
                'message' => $matches[1],
                'time' => now()->addDay()->format('Y-m-d') . ' ' . $matches[2] . ':00',
            ];
        }

        return null;
    }

    private function sendMessage($recipientId, $message): void
    {
        $pageAccessToken = env('FB_PAGE_ACCESS_TOKEN');
        $url = "https://graph.facebook.com/v13.0/me/messages?access_token={$pageAccessToken}";

        $client = new Client();
        $client->post($url, [
            'json' => [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $message],
            ],
        ]);
    }
}
