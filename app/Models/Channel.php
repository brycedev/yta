<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Channel extends Model
{
    use HasFactory;

    protected $appends = ['items'];
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function syncs()
    {
        return $this->hasMany(Sync::class);
    }

    public function getFreshVideos()
    {
        Cache::forget('channel-videos:' . $this->id);
        return $this->getVideos();
    }

    public function getVideos()
    {
        $items = Cache::remember('channel-videos:' . $this->id, 60 * 60 * 24, function () {
            $entries = [];

            return $entries;
        });
        return $items;
    }

    public function getItemsAttribute()
    {
        return $this->getVideos();
    }
}
