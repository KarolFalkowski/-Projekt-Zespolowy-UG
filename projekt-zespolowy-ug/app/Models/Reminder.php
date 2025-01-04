<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    const FIELD_USER_ID = 'user_id';
    const FIELD_MESSAGE = 'message';
    const FIELD_REMINDER_TIME = 'reminder_time';
    const FIELD_STATUS = 'status';

    protected $fillable = [
        self::FIELD_USER_ID,
        self::FIELD_MESSAGE,
        self::FIELD_REMINDER_TIME,
        self::FIELD_STATUS
    ];

    protected function casts(): array
    {
        return [
            'status' => ReminderStatusEnum::class,
        ];
    }
}
