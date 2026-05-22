<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'title',
        'created_by',
        'participants',
        'last_message_at'
    ];
}
