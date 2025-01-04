<?php

namespace App\Console\Commands;

use App\Models\ReminderStatusEnum;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use App\Models\Reminder;
use Carbon\Carbon;
use GuzzleHttp\Client;

class CheckReminders extends Command
{
    protected $signature = 'reminders:check';
    protected $description = 'Check reminders and send messages if time matches';


    /**
     * @return void
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $now = Carbon::now();

        $reminders = Reminder::where(Reminder::FIELD_REMINDER_TIME, '<=', $now)
            ->where(Reminder::FIELD_STATUS, '=', ReminderStatusEnum::Wait)
            ->get();

        foreach ($reminders as $reminder) {
            $this->sendMessage($reminder->user_id, $reminder->message);

            $reminder->update([
                Reminder::FIELD_STATUS => ReminderStatusEnum::Sent
            ]);
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
        $pageAccessToken = config('services.facebook.fb_page_access_token');
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
