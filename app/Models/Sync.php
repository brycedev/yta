<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sync extends Model
{
    use HasFactory;

    protected $appends = ['synced_at'];
    protected $casts = [
        'automated' => 'boolean',
    ];
    protected $guarded = [];

    public function getSyncedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->diffForHumans();
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
