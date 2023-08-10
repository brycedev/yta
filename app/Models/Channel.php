<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Channel extends Model
{
    use HasFactory;

    protected $appends = ['videos'];
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
            $feed = 'https://youtube.com/feeds/videos.xml?channel_id=' . $this->youtube_id;
            $rss = new \SimpleXMLElement(file_get_contents($feed));
            foreach ($rss->entry as $entry) {
                $id = str_replace('yt:video:', '', $entry->id->__toString());
                $namespaces = $entry->getNameSpaces(true);
                $media = $entry->children($namespaces['media']);
                $description = $media->group->description->__toString();
                $thumbnail = $media->group->thumbnail->attributes()->url->__toString();
                $title = $entry->title->__toString();
                $published_at = Carbon::createFromFormat('Y-m-d', explode('T', $entry->published->__toString(), 2)[0])->toFormattedDateString();
                $sync = Sync::where('guid', $id)->where('user_id', $this->user->id)->first();
                $vid = [
                    'date' => $published_at,
                    'guid' => $id,
                    'description' => $description,
                    'title' => $title,
                    'image' => $thumbnail,
                    'tags' => [],
                    'published_at' => $published_at,
                    'status' => $sync !== null ? $sync->status : 'unlisted',
                    'audius_url' => $sync !== null ? $sync->audius_url : '#',
                ];
                array_push($entries, $vid);
            }
            return $entries;
        });
        return $items;
    }

    public function getVideosAttribute()
    {
        return $this->getVideos();
    }
}
