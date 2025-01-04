<?php

namespace App\Console\Commands;

use App\Models\ReminderStatusEnum;
use App\Services\FacebookService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckReminders extends Command
{
    protected $signature = 'reminders:check';
    protected $description = 'Check reminders and send messages if time matches';


    /**
     * @param FacebookService $facebookService
     * @return void
     */
    public function handle(FacebookService $facebookService): void
    {
        $now = Carbon::now();

        $reminders = Reminder::where(Reminder::FIELD_REMINDER_TIME, '<=', $now)
            ->where(Reminder::FIELD_STATUS, '=', ReminderStatusEnum::Wait)
            ->get();

        foreach ($reminders as $reminder) {
            try {
                $facebookService->sendMessage($reminder->{Reminder::FIELD_USER_ID}, $reminder->{Reminder::FIELD_MESSAGE});
            } catch (GuzzleException $e) {
                $reminder->update([
                    Reminder::FIELD_STATUS => ReminderStatusEnum::Error
                ]);
                Log::error('Facebook error: '. $e->getMessage());
            }

            $reminder->update([
                Reminder::FIELD_STATUS => ReminderStatusEnum::Sent
            ]);
        }
    }
}
