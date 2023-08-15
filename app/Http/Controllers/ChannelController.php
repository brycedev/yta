<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Sync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SyncVideo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ChannelController extends Controller
{
    public function fetch(Request $request)
    {
        $channel = Auth::user()->channel;
        if($channel) {
            $channel->getFreshVideos();
            $channel->has_initial_fetch = true;
            $channel->save();
        }
        return to_route('dashboard');
    }

    public function verify(Request $request)
    {
        $link_check = Http::get($request->url);
        if($link_check->status() === 404 || !str_contains($request->url, 'youtube.com/')) {
            return back()->withErrors(['url' => "This doesn't appear to be a valid channel. Please try again."]);
        }
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
            $data = json_decode($output);
            $is_staging = (config('app.audius_env') === 'staging' && config('app.env') === 'production');
            if($process->successful() || $is_staging) {
                $payload = [
                    'user_id' => Auth::user()->id,
                    'name' => $data->name,
                    'url' => $request->url,
                    'youtube_id' => $data->id
                ];
                Channel::create($payload);
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
