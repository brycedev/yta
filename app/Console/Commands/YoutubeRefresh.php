<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Channel;
use Carbon\Carbon;
use App\Models\Sync;
use App\Jobs\SyncVideo;

class YoutubeRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh videos from youtube channels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $channels = Channel::all();

        foreach ($channels as $channel) {
            $channel->getFreshVideos();
        }

    }
}
