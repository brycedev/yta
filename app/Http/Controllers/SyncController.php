<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sync;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncEpisode;

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
                'podcast_id' => $request->podcast_id,
                'title' => $request->title,
                'image' => $request->image,
                'source' => $request->source,
                'guid' => $request->guid,
                'status' => 'queued',
                'automated' => false
            ]);
        }

        dispatch(new SyncEpisode($sync));

        $sync->podcast->getFreshVideos();

        return to_route('dashboard');
    }

    public function all(Request $request)
    {
        $podcast = Auth::user()->podcast;
        $episodes = $podcast->items;
        foreach($episodes as $episode) {
            $sync = Sync::where('guid', $episode['guid'])->where('user_id', Auth::user()->id)->first();
            if(!$sync) {
                $sync = Sync::create([
                    'user_id' => Auth::user()->id,
                    'podcast_id' => $podcast->id,
                    'title' => $episode['title'],
                    'image' => $episode['image'],
                    'source' => $episode['source'],
                    'guid' => $episode['guid'],
                    'status' => 'queued',
                    'automated' => true
                ]);
                dispatch(new SyncEpisode($sync));
            }
        }
        return to_route('dashboard');
    }
}
