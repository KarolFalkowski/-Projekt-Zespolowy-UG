<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property string $user_id
 * @property ReminderStatusEnum $status
 * @property string $message
 * @property string $reminder_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Reminder newModelQuery()
 * @method static Builder<static>|Reminder newQuery()
 * @method static Builder<static>|Reminder query()
 * @method static Builder<static>|Reminder whereCreatedAt($value)
 * @method static Builder<static>|Reminder whereId($value)
 * @method static Builder<static>|Reminder whereMessage($value)
 * @method static Builder<static>|Reminder whereReminderTime($value)
 * @method static Builder<static>|Reminder whereStatus($value)
 * @method static Builder<static>|Reminder whereUpdatedAt($value)
 * @method static Builder<static>|Reminder whereUserId($value)
 * @mixin Eloquent
 */
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
