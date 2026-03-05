<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLog extends Model
{
    protected $fillable = [
        'chat_id',
        'user_name',
        'message',
        'bot_response',
        'action',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
