<?php

namespace App\Http\Controllers\admin;

use App\Models\Comment;
use App\Models\Music;
use App\Models\Report;
use App\Models\Singer;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class IndexController extends Controller
{
    public function index()
    {
        return view('template');
    }

    public function statistics()
    {
        $week_ago = Carbon::now()->subWeek();
        $total_users = User::count();
        $new_users = User::where('created_at' , '>' , $week_ago)->count();
        $total_musics = Music::count();
        $new_musics = Music::where('created_at' , '>' , $week_ago)->count();
        $total_singers = Singer::count();
        $new_singers = Singer::where('created_at' , '>' , $week_ago)->count();
        $total_comments = Comment::count();
        $pending_comments = Comment::where('status' , 0)->count();
        $week_total_pays = Report::where('type', 0)->where('created_at' , '>' , $week_ago)->sum('val_money');
        $yesterday_total_pays = Report::where('type', 0)->where('created_at' , '<' , Carbon::today()->subMinutes(210)->format('Y-m-d H:i:s'))->where('created_at' , '>' , Carbon::yesterday()->subMinutes(210)->format('Y-m-d H:i:s'))->sum('val_money');
        $today_total_pays = Report::where('type', 0)->where('created_at' , '>' , Carbon::today()->subMinutes(210)->format('Y-m-d H:i:s'))->sum('val_money');

        $statistics = [
            'total_users' => number_format($total_users),
            'new_users' => $new_users,
            'total_musics' => number_format($total_musics),
            'new_musics' => $new_musics,
            'total_singers' => number_format($total_singers),
            'new_singers' => $new_singers,
            'total_comments' => number_format($total_comments),
            'pending_comments' => $pending_comments,
            'today_total_pays' => number_format($today_total_pays),
            'yesterday_total_pays' => number_format($yesterday_total_pays),
            'week_total_pays' => number_format($week_total_pays)
        ];

        $arr = [
            'data' => $statistics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($arr);
    }
}
