<?php

namespace VigStudio\LaravelAI\Models;

use Illegal\LaravelAI\Contracts\BelongsToModel;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Chat extends EloquentModel
{
    use BelongsToModel;

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
