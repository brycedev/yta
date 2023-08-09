<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Channel;
use Carbon\Carbon;
use App\Models\Sync;
use App\Jobs\SyncVideo;

class AudiusSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audius:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync auto sync channels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $channels = Channel::where('auto_sync', true)
            ->where('synced_at', '<=', Carbon::now()->subHours(4)->toDateTimeString())->get();

        foreach ($channels as $channel) {
            $video = $channel->getFreshVideos();
            foreach ($video as $video) {
                $sync = Sync::where('guid', $video['guid'])->where('user_id', $channel->user->id)->first();
                if(!$sync && Carbon::parse($video['date']) > Carbon::parse($channel->initial_sync_date)) {
                    $sync = Sync::create([
                        'user_id' => $channel->user->id,
                        'channel_id' => $channel->id,
                        'title' => $video['title'],
                        'image' => $video['image'],
                        'source' => $video['source'],
                        'guid' => $video['guid'],
                        'status' => 'queued',
                        'automated' => true
                    ]);
                    dispatch(new SyncVideo($sync));
                }
            }

        }

    }
}
