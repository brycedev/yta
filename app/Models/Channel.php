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
        $entries = [];
        $path = storage_path('app/youtubes/' . $this->youtube_id . '/%(upload_date)s%(id)s');
        $command = config('app.ytdl_path') . " --verbose --write-info-json --skip-download -o '" . $path . "' https://www.youtube.com/channel/" . $this->youtube_id;
        Process::timeout(120)->run($command);
        File::delete(storage_path('app/youtubes/' . $this->youtube_id . '/NA' . $this->youtube_id . '.info.json'));
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
        Cache::put('channel-videos:' . $this->id, $entries);
        return $entries;
    }

    public function getVideos()
    {
        return Cache::get('channel-videos:' . $this->id);
    }

    public function getVideosAttribute()
    {
        return $this->getVideos();
    }

    public function updateVideo($id, $attrs)
    {
        $entries = [];
        $videos = $this->getVideos();
        foreach($videos as $video) {
            if($video['guid'] === $id) {
                $copy = $video;
                if(isset($attrs['status'])) {
                    $copy['status'] = $attrs['status'];
                }
                if(isset($attrs['audius_url'])) {
                    $copy['audius_url'] = $attrs['audius_url'];
                }
                array_push($entries, $copy);
            } else {
                array_push($entries, $video);
            }
        }
        if(count(array_keys($attrs))) {
            Cache::forget('channel-videos:' . $this->id);
            Cache::put('channel-videos:' . $this->id, $entries);
        }
    }
}
