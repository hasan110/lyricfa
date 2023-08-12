<?php

namespace App\Http\Controllers\admin;

use App\Models\CommentMusic;
use App\Models\Music;
use App\Models\Singer;
use App\Models\User;
use Illuminate\Http\Request;
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
        $total_users = User::all()->count();
        $new_users = User::where('created_at' , '>' , $week_ago)->count();
        $total_musics = Music::all()->count();
        $new_musics = Music::where('created_at' , '>' , $week_ago)->count();
        $total_singers = Singer::all()->count();
        $new_singers = Singer::where('created_at' , '>' , $week_ago)->count();
        $total_comments = CommentMusic::all()->count();
        $pending_comments = CommentMusic::where('id_admin_confirmed' , 0)->count();

        $statistics = [
            'total_users' => $total_users,
            'new_users' => $new_users,
            'total_musics' => $total_musics,
            'new_musics' => $new_musics,
            'total_singers' => $total_singers,
            'new_singers' => $new_singers,
            'total_comments' => $total_comments,
            'pending_comments' => $pending_comments
        ];

        $arr = [
            'data' => $statistics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($arr, 200);
    }
}
