<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Sync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SyncVideo;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ChannelController extends Controller
{
    public function verify(Request $request)
    {

        return to_route('dashboard');
    }

    public function store(Request $request)
    {

        try {
            $verify_script_path = base_path('verify.js');
            $command = "node {$verify_script_path} ";
            $command.= $request->user()->audius_handle;
            $command.= " {$request->url} ";
            Log::info($command);
            $process = Process::timeout(120)->run($command);
            $output = $process->output();
            Log::info($output);
            if($process->successful()) {
                $payload = [
                    'user_id' => Auth::user()->id,
                    'name' => 'YouTube Channel',
                    'url' => $request->url,
                    'youtube_id' => json_decode($output)->id
                ];
                $channel = Channel::create($payload);
                $videos = $channel->getVideos();

                if(count($videos) !== 0) {
                    $video = $videos[0];

                    $channel->initial_sync_date = Carbon::parse($video['date']);
                    $channel->save();

                    $sync = Sync::create([
                        'user_id' => Auth::user()->id,
                        'channel_id' => $channel->id,
                        'title' => $video['title'],
                        'image' => $video['image'],
                        'source' => $video['source'],
                        'guid' => $video['guid'],
                        'status' => 'queued'
                    ]);
                    $sync->channel->getFreshVideos();

                    dispatch(new SyncVideo($sync));
                }

                return to_route('dashboard');
            }
            return back()->withErrors(['url' => "We couldn't verify that you own this channel. Please try again."]);
        } catch (\Throwable $th) {
            Log::error($th);
            return to_route('dashboard')->withErrors(['url' => "We couldn't verify that you own this channel. Please try again."]);
        }




        return to_route('dashboard');
    }

    public function refresh()
    {
        $channel = Auth::user()->channel;
        $channel->getFreshVideos();
        return to_route('dashboard');
    }

    public function update(Request $request)
    {
        $channel = Auth::user()->channel;
        $channel->update($request->all());
        return to_route('dashboard');
    }

    public function destroy()
    {
        $channel = Auth::user()->channel;
        $channel->delete();
        return to_route('dashboard');
    }
}
