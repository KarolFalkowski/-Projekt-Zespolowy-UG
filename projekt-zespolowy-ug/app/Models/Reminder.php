<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    const FIELD_USER_ID = 'user_id';
    const FIELD_MESSAGE = 'message';
    const FIELD_REMINDER_TIME = 'reminder_time';

    protected $fillable = [
      self::FIELD_MESSAGE,
      self::FIELD_REMINDER_TIME
    ];
}
