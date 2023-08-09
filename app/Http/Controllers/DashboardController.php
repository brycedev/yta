<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Channel;

class DashboardController extends Controller
{
    public function index()
    {
        $channel = Channel::where('user_id', auth()->user()->id)->first();
        $syncs = [];
        if($channel) {
            $syncs = $channel->syncs()->orderBy('created_at', 'desc')->get();
        }
        return Inertia::render('Dashboard')->with([
            'auth' => [
                'user' => auth()->user()
            ],
            'channel' => $channel,
            'syncs' => $syncs
        ]);
    }
}
