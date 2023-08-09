<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Sync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SyncVideo;
use Carbon\Carbon;

class ChannelController extends Controller
{
    public function store(Request $request)
    {

        $payload = [
            'user_id' => Auth::user()->id,
            'name' => 'YouTube Channel',
            'url' => $request->url
        ];
        $channel = Channel::create($payload);
        $videos = $channel->getVideos();

        // if(count($videos) == 0) {
        //     $channel->delete();
        //     return back()->withErrors(['url' => 'This podcast feed has no audio episodes.']);
        // }

        // $video = $videos[0];

        // $channel->initial_sync_date = Carbon::parse($video['date']);
        // $channel->save();

        // $sync = Sync::create([
        //     'user_id' => Auth::user()->id,
        //     'podcast_id' => $channel->id,
        //     'title' => $video['title'],
        //     'image' => $video['image'],
        //     'source' => $video['source'],
        //     'guid' => $video['guid'],
        //     'status' => 'queued'
        // ]);
        // $sync->channel->getFreshVideos();

        // dispatch(new SyncVideo($sync));

        return to_route('dashboard');
    }

    public function refresh()
    {
        $channel = Auth::user()->podcast;
        $channel->getFreshVideos();
        return to_route('dashboard');
    }

    public function update(Request $request)
    {
        $channel = Auth::user()->podcast;
        $channel->update($request->all());
        return to_route('dashboard');
    }

    public function destroy()
    {
        $channel = Auth::user()->podcast;
        $channel->delete();
        return to_route('dashboard');
    }
}
