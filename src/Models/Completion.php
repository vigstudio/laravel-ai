<?php

namespace VigStudio\LaravelAI\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Completion extends EloquentModel
{
    protected $table = 'vig_ai_completions';

    protected $fillable = [
        'model_id',
        'external_id',
        'prompt',
        'completion',
    ];
}
