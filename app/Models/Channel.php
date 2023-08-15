<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class Channel extends Model
{
    use HasFactory;

    protected $appends = ['videos'];
    protected $casts = ['auto_sync' => 'boolean', 'has_initial_fetch' => 'boolean'];
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
        $items = Cache::rememberForever('channel-videos:' . $this->id, function () {
            $entries = [];
            $path = storage_path('app/youtubes/' . $this->youtube_id . '/%(upload_date)s');
            $command = config('app.ytdl_path') . " --verbose --write-info-json --skip-download -o '" . $path . "' https://www.youtube.com/channel/" . $this->youtube_id;
            Process::timeout(120)->run($command);
            File::delete(storage_path('app/youtubes/' . $this->youtube_id . '/NA.info.json'));
            $files = Storage::disk('local')->files('youtubes/' . $this->youtube_id);
            foreach (array_reverse($files) as $file) {
                $json = Storage::disk('local')->get($file);
                $data = json_decode($json, true);
                $id = $data['id'];
                $description = $data['description'];
                $thumbnail = $data['thumbnail'];
                $title = $data['title'];
                $published_at = Carbon::parse($data['upload_date'])->toFormattedDateString();
                $sync = Sync::where('guid', $id)->where('user_id', $this->user->id)->first();
                array_push($entries, [
                    'date' => $published_at,
                    'guid' => $id,
                    'description' => $description,
                    'title' => $title,
                    'image' => $thumbnail,
                    'tags' => [],
                    'published_at' => $published_at,
                    'status' => $sync !== null ? $sync->status : 'unlisted',
                    'audius_url' => $sync !== null ? $sync->audius_url : '#',
                ]);
            }
            return $entries;
        });
        return $items;
    }

    public function getVideosAttribute()
    {
        return $this->getVideos();
    }

    public function updateVideo($id, $attrs)
    {
        $videos = $this->getVideos();
        foreach($videos as $video) {
            if($video['guid'] === $id) {
                if(isset($attrs['status'])) {
                    $video['status'] = $attrs['status'];
                }
                if(isset($attrs['audius_url'])) {
                    $video['audius_url'] = $attrs['audius_url'];
                }
            }
        }
        if(count(array_keys($attrs))) {
            Cache::forget('channel-videos:' . $this->id);
            Cache::forever('channel-videos:' . $this->id, $videos);
        }
    }
}
