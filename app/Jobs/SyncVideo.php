<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Sync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SyncVideo implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Sync $sync;

    public function __construct(Sync $sync)
    {
        $this->sync = $sync;
    }

    public function handle(): void
    {
        try {
            $video = collect($this->sync->channel->videos)->firstWhere('guid', $this->sync->guid);
            $data = json_encode(json_encode([
                "description" =>  $video['description'],
                "title" => $video['title'],
                "tags" => implode(',', $video['tags']),
                "date" => $video['date'],
                "image" => $video['image'],
            ]));
            $directory = "syncs/{$this->sync->id}";
            $video_audio_path = "{$directory}/video.wav";
            $image_path = "{$directory}/image.jpg";
            $absolute_image_path = storage_path("app/{$image_path}");
            Storage::disk('local')->makeDirectory($directory);
            $this->sync->update(['status' => 'syncing']);
            $this->sync->channel->updateVideo($this->sync->guid, ['status' => 'syncing']);
            $youtube_dl_video_path = storage_path("app/{$directory}/video");
            $youtube_dl_command = "yt-dlp -x --audio-format wav --output {$youtube_dl_video_path} https://youtube.com/watch?v={$this->sync->guid}";
            Process::timeout(600)->run($youtube_dl_command);
            Log::info($youtube_dl_command);
            if($this->sync->image == "") {

            } else {
                Storage::disk('local')->put($image_path, Http::get($this->sync->image)->body());
            }
            $audius_sync_path = base_path('audius.ts');
            $command = "npx tsx {$audius_sync_path} ";
            $command.= "--user {$this->sync->channel->user->audius_id} ";
            $command.= "--data {$data} ";
            $path = storage_path("app/{$video_audio_path}");
            $command.= "--audio {$path} ";
            if(File::exists($absolute_image_path)) {
                $command.= "--image {$absolute_image_path} ";
            }
            Log::info($command);
            $process = Process::timeout(600)->run($command);
            Log::info($process->output());
            if($process->successful()) {
                $this->sync->update(['status' => 'synced', 'audius_url' => $process->output()]);
                $this->sync->channel->updateVideo($this->sync->guid, ['status' => 'synced', 'audius_url' => $process->output()]);

                if(!$this->sync->channel->initial_sync_date) {
                    $this->sync->channel->initial_sync_date = Carbon::parse($video['date']);
                }
            }
            if($process->failed()) {
                Log::error($process->output());
                Log::error($process->errorOutput());
                $this->sync->update(['status' => 'failed']);
                $this->sync->channel->updateVideo($this->sync->guid, ['status' => 'failed']);
                $this->fail();
            }
            Storage::disk('local')->deleteDirectory($directory);
        } catch (\Throwable $th) {
            $this->sync->update(['status' => 'failed']);
            $this->sync->channel->updateVideo($this->sync->guid, ['status' => 'failed']);
            $this->fail($th);
            Log::error($th);
        }
    }
}
