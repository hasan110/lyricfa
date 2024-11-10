<?php

namespace App\Http\Controllers\admin;

use App\Http\Helpers\CommentHelper;
use App\Http\Helpers\LikeHelper;
use App\Http\Helpers\SingerHelper;
use App\Models\Admin;
use App\Models\File;
use App\Models\Music;
use App\Models\Singer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

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
            $music->singers = (new SingerHelper())->getMusicSingers($music->id);
            $music->num_like = (new LikeHelper())->getMusicLikesCount($music->id);
            $music->num_comment = (new CommentHelper())->getMusicCommentsCount($music->id);
        }

        return response()->json([
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function musicsCreate(Request $request)
    {
        $message = array(
            'english_title.required' => 'عنوان انگلیسی آهنگ نمی تواند خالی باشد',
            'persian_title.required' => 'عنوان فارسی آهنگ نمی تواند خالی باشد',
            'singers.required' => 'حتما یک خواننده باید انتخاب شود.',
            'level.required' => 'سطح موزیک باید انتخاب شود',
            'level.in' => 'سطح باید یکی از موارد: A1, A2, B1, B2, C1, C2 باشد',
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
            'image.required' => 'فایل پوستر موزیک باید آپلود شود',
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
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
            'start_demo' => 'required|numeric',
            'end_demo' => 'required|numeric',
            'hardest_degree' => 'required|numeric',
            'date_publication' => 'required|date',
            'image' => 'file|required|mimes:jpg|dimensions:min_width=300,min_height=300,max_width=300,max_height=300',
            'music' => 'file|required',
            'is_user_request' => 'required|numeric'
        ], $message);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن آهنگ شکست خورد",
            ], 400);
        }

        $music = new Music();
        $music->name = $request->english_title;
        $music->persian_name = $request->persian_title;
        $music->start_demo = $request->start_demo;
        $music->end_demo = $request->end_demo;
        $music->degree = $request->hardest_degree;
        $music->level = $request->level;
        $music->published_at = $request->date_publication;
        $music->is_user_request = $request->is_user_request;
        $music->status = $request->has('status') ? intval($request->status) : 0;

        if ($request->album_id) {
            $music->album_id = $request->album;
        }

        $music->save();

        if ($request->hasFile('music')) {
            File::createFile($request->music , $music, Music::SOURCE_FILE_TYPE);
        }

        if ($request->hasFile('image')) {
            File::createFile($request->image , $music, Music::POSTER_FILE_TYPE);
        }

        if ($request->singers) {
            $music->singers()->attach(explode(',', $request->singers));
        }

        return response()->json([
            'data' => $music,
            'errors' => null,
            'message' => "موزیک با موفقیت اضافه شد"
        ]);
    }

    public function musicsUpdate(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
            'singers.required' => 'لیست خواننده ها نمی تواند خالی باشد',
            'english_title.required' => 'عنوان انگلیسی آهنگ نمی تواند خالی باشد',
            'persian_title.required' => 'عنوان فارسی آهنگ نمی تواند خالی باشد',
            'level.required' => 'سطح موزیک باید انتخاب شود',
            'level.in' => 'سطح باید یکی از موارد: A1, A2, B1, B2, C1, C2 باشد',
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
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
            'start_demo' => 'required|numeric',
            'end_demo' => 'required|numeric',
            'hardest_degree' => 'required|numeric',
            'date_publication' => 'required|date',
            'music' => 'file',
            'image' => 'file|mimes:jpg|dimensions:min_width=300,min_height=300,max_width=300,max_height=300'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن آهنگ شکست خورد",
            ], 400);
        }

        $music = Music::where('id', $request->id)->first();
        if(!$music){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این موزیک وجود ندارد برای به روز رسانی"
            ], 400);
        }

        $music->name = $request->english_title;
        $music->persian_name = $request->persian_title;
        $music->is_user_request = $request->has('is_user_request') ? intval($request->is_user_request) : 0;
        $music->status = $request->has('status') ? intval($request->status) : 0;
        $music->start_demo = $request->start_demo;
        $music->end_demo = $request->end_demo;
        $music->degree = $request->hardest_degree;
        $music->level = $request->level;
        $music->published_at = $request->date_publication;
        if ($request->album) {
            $music->album_id = $request->album;
        }
        $music->save();

        if ($request->hasFile('music')) {
            File::createFile($request->music, $music, Music::SOURCE_FILE_TYPE);
        }

        if ($request->hasFile('image')) {
            File::createFile($request->image, $music, Music::POSTER_FILE_TYPE);
        }

        if ($request->singers) {
            $music->singers()->sync(explode(',', $request->singers));
        }

        return response()->json([
            'data' => $music,
            'errors' => null,
            'message' => "موزیک با موفقیت اضافه شد"
        ]);
    }

    public function getMusicCompleteInfo(Request $request)
    {
        $id = $request->id;
        $music = Music::where('id', $id)->first();
        $singer = (new SingerHelper())->getMusicSingers($id);

        $singers = [];
        foreach ($singer as $item) {
            $singers[] = $item->id;
        }

        return response()->json([
            'data' => [
                'music' => $music,
                'singers' => $singers
            ],
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}
