<?php

namespace VigStudio\LaravelAI\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Chat extends EloquentModel
{
    protected $table = 'vig_ai_chats';

    protected $fillable = [
        'model_id',
        'external_id',
        'messages',
    ];

    protected $casts = [
        'messages' => 'array',
    ];
}
