<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
     protected $fillable =['event_id','user_id','rating','comment'];

     public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

  
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
