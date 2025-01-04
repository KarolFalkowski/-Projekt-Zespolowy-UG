<?php

namespace App\Models;

enum ReminderStatusEnum: string
{
    case Sent = 'sent';
    case Wait = 'wait';
    case Error = 'error';
}
