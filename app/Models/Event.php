<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'latitude', 'longitude', 'date', 'image', 'approved', 'max_attendees', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    /**
     * Users who attended this event.
     * Includes pivot column 'attended_at'.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('attended_at');
    }

    public function getLocationAttribute()
    {
        return ['latitude' => $this->latitude, 'longitude' => $this->longitude];
    }
}
