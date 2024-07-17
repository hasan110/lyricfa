<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\CommentMusicController;
use App\Http\Controllers\LikeMusicController;
use App\Http\Controllers\ScoreMusicController;
use App\Http\Controllers\SingerController;
use App\Http\Controllers\UserController;
use App\Models\Admin;
use App\Models\Music;
use App\Models\Singer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class MusicController extends Controller
{
    public function MusicsList(Request $request)
    {
        $musics = Music::whereNotNull('id');

        if ($request->search_key) {
            $musics = $musics->where('name', 'LIKE', "%{$request->search_key}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_key}%")->
            orWhere('id', '=', $request->search_key);
        }
        if ($request->sort_by) {
            switch ($request->sort_by) {
                case 'newest':
                default:
                    $musics = $musics->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $musics = $musics->orderBy('created_at', 'asc');
                    break;
                case 'publish':
                    $musics = $musics->whereNotNull('published_at')->orderBy('published_at', 'desc');
                    break;
                case 'easy':
                    $musics = $musics->where('degree', 1);
                    break;
                case 'normal':
                    $musics = $musics->where('degree', 2);
                    break;
                case 'hard':
                    $musics = $musics->where('degree', 3);
                    break;
                case 'expert':
                    $musics = $musics->where('degree', 4);
                    break;
                case 'most_seen':
                    $musics = $musics->orderByRaw('CAST(views as SIGNED INTEGER) DESC');
                    break;
                case 'has_album':
                    $musics = $musics->whereNotNull('album_id');
                    break;
            }
        }
        $musics = $musics->paginate(50);

        foreach ($musics as $music) {
            $music->singers = \App\Http\Controllers\SingerController::getSingerById($music->id);
            $music->num_like = LikeMusicController::getNumberMusicLike($music->id);
            $music->num_comment = CommentMusicController::getNumberMusicComment($music->id);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public function musicsCreate(Request $request)
    {
        $message = array(
            'english_title.required' => 'عنوان انگلیسی آهنگ نمی تواند خالی باشد',
            'persian_title.required' => 'عنوان فارسی آهنگ نمی تواند خالی باشد',
            'singers.required' => 'حتما یک خواننده باید انتخاب شود.',
            'start_demo.required' => 'زمان شروع دمو آهنگ نمی تواند خالی باشد',
            'start_demo.numeric' => 'زمان شروع دمو آهنگ باید عدد باشد',
            'end_demo.required' => 'زمان پایان دمو آهنگ نمی تواند خالی باشد',
            'end_demo.numeric' => 'زمان پایان دمو آهنگ باید عدد باشد',
            'hardest_degree.required' => 'درجه سختی آهنگ نمی تواند خالی باشد',
            'hardest_degree.numeric' => 'درجه سختی آهنگ باید عدد باشد',
            'date_publication.required' => 'تاریخ انتشار آهنگ نمی تواند خالی باشد',
            'date_publication.date' => 'تاریخ انتشار آهنگ باید از جنس تاریخ باشد',
            'music.required' => 'فایل موزیک باید آپلود شود',
            'music.file' => 'نوع موزیک باید فایل باشد',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
            'image.dimensions' => 'عکس باید 300 در 300 باشد',
            'is_user_request.required' => 'سفارش شده توسط کاربر نمی تواند خالی باشد',
            'is_user_request.numeric' => 'سفارش شده توسط کاربر باید عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'english_title' => 'required',
            'persian_title' => 'required',
            'singers' => 'required',
            'start_demo' => 'required|numeric',
            'end_demo' => 'required|numeric',
            'hardest_degree' => 'required|numeric',
            'date_publication' => 'required|date',
            'image' => 'file|mimes:jpg|dimensions:min_width=300,min_height=300,max_width=300,max_height=300',
            'is_user_request' => 'required|numeric'
        ], $message);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن آهنگ شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $music = new Music();
        $music->name = $request->english_title;
        $music->persian_name = $request->persian_title;
        $music->start_demo = $request->start_demo;
        $music->end_demo = $request->end_demo;
        $music->degree = $request->hardest_degree;
        $music->published_at = $request->date_publication;
        $music->is_user_request = $request->is_user_request;

        if ($request->album_id) {
            $music->album_id = $request->album;
        }

        $music->save();

        if ($request->hasFile('music')) {
            $this->uploadFileById($request->music,"musics/128", $music->id);
        }

        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image,"musics_banner", $music->id);
        }

        if ($request->singers) {
            $music->singers()->attach(explode(',', $request->singers));
        }

        $arr = [
            'data' => $music,
            'errors' => null,
            'message' => "موزیک با موفقیت اضافه شد"
        ];

        return response()->json($arr);
    }



    //TODO this before added
    public function musicsUpdate(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
            'singers.required' => 'لیست خواننده ها نمی تواند خالی باشد',
            'english_title.required' => 'عنوان انگلیسی آهنگ نمی تواند خالی باشد',
            'persian_title.required' => 'عنوان فارسی آهنگ نمی تواند خالی باشد',
            'start_demo.required' => 'زمان شروع دمو آهنگ نمی تواند خالی باشد',
            'start_demo.numeric' => 'زمان شروع دمو آهنگ باید عدد باشد',
            'end_demo.required' => 'زمان پایان دمو آهنگ نمی تواند خالی باشد',
            'end_demo.numeric' => 'زمان پایان دمو آهنگ باید عدد باشد',
            'hardest_degree.required' => 'درجه سختی آهنگ نمی تواند خالی باشد',
            'hardest_degree.numeric' => 'درجه سختی آهنگ باید عدد باشد',
            'date_publication.required' => 'تاریخ انتشار آهنگ نمی تواند خالی باشد',
            'date_publication.date' => 'تاریخ انتشار آهنگ باید از جنس تاریخ باشد',
            'music.file' => 'نوع موزیک باید فایل باشد',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
            'image.dimensions' => 'عکس باید 300 در 300 باشد',
        );

        $validator = Validator::make($request->all(), [
            'id'=> 'required|numeric',
            'singers' => 'required',
            'english_title' => 'required',
            'persian_title' => 'required',
            'start_demo' => 'required|numeric',
            'end_demo' => 'required|numeric',
            'hardest_degree' => 'required|numeric',
            'date_publication' => 'required|date',
            'image' => 'file|mimes:jpg|dimensions:min_width=300,min_height=300,max_width=300,max_height=300'
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن آهنگ شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $music = $this->getMusicById($request->id);
        if(!$music){
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "این موزیک وجود ندارد برای به روز رسانی"
            ];
            return response()->json($arr, 400);
        }

        $music->name = $request->english_title;
        $music->persian_name = $request->persian_title;
        $music->is_user_request = $request->has('is_user_request') ? intval($request->is_user_request) : 0;
        $music->start_demo = $request->start_demo;
        $music->end_demo = $request->end_demo;
        $music->degree = $request->hardest_degree;
        $music->published_at = $request->date_publication;
        if ($request->album) {
            $music->album_id = $request->album;
        }
        $music->save();

        if ($request->hasFile('music')) {
            $this->uploadFileById($request->music,"musics/128", $music->id);
        }

        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image,"musics_banner", $music->id);
        }

        if ($request->singers) {
            $music->singers()->sync(explode(',', $request->singers));
        }

        $arr = [
            'data' => $music,
            'errors' => null,
            'message' => "موزیک با موفقیت اضافه شد"
        ];

        return response()->json($arr);


    }

    public static function getMusicById($id)
    {
        return Music::where('id', $id)->first();
    }

    public function getMusicCompleteInfo(Request $request)
    {
        $id = $request->id;
        $music = $this->getMusicById($id);
        $singer = SingerController::getSingerById($music->id);
        $api_token = $request->header("ApiToken");
        $user_id = AdminController::getAdminByToken($api_token)->id;
        $num_like = LikeMusicController::getNumberMusicLike($id);
        $num_comment = CommentMusicController::getNumberMusicComment($id);
        $user_like_it = LikeMusicController::isUserLike($id, $user_id);
        $average_score = ScoreMusicController::getAverageMusicScore($id);

        $singers = [];
        foreach ($singer as $item){
            $singers[] = $item->id;
        }

        $data = [
            'music' => $music,
            'singers' => $singers,
            'num_like' => $num_like,
            'num_comment' => $num_comment,
            'user_like_it' => $user_like_it,
            'average_score' => $average_score,
            'music_url' => "musics/" . $music->id . ".mp3"
        ];

        $arr = [
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($arr);
    }
}
