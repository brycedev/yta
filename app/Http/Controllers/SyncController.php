<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sync;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncVideo;

class SyncController extends Controller
{
    public function store(Request $request)
    {
        if($request->has('retry')) {
            $sync = Sync::where('guid', $request->guid)->where('user_id', Auth::user()->id)->first();
            $sync->status = 'queued';
            $sync->save();
        } else {
            $sync = Sync::create([
                'user_id' => Auth::user()->id,
                'channel_id' => $request->channel_id,
                'title' => $request->title,
                'image' => $request->image,
                'guid' => $request->guid,
                'status' => 'queued',
                'automated' => false
            ]);
        }

        dispatch(new SyncVideo($sync));

        $sync->channel->getFreshVideos();

        return to_route('dashboard');
    }

    public function all(Request $request)
    {
        $channel = Auth::user()->channel;
        $episodes = $channel->items;
        foreach($episodes as $episode) {
            $sync = Sync::where('guid', $episode['guid'])->where('user_id', Auth::user()->id)->first();
            if(!$sync) {
                $sync = Sync::create([
                    'user_id' => Auth::user()->id,
                    'channel_id' => $channel->id,
                    'title' => $episode['title'],
                    'image' => $episode['image'],
                    'guid' => $episode['guid'],
                    'status' => 'queued',
                    'automated' => true
                ]);
                dispatch(new SyncVideo($sync));
            }
        }
        return to_route('dashboard');
    }
}
