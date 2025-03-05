<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Film;
use App\Models\Music;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ViewController extends Controller
{
    public function latestViews(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $views = $user->views()->where('percentage' , '<' , 100)->with('viewable')->orderBy('updated_at', 'desc')->take(20)->get();
        $final_list = [];

        foreach ($views as $view) {
            if ($view->viewable_type === Film::class) {
                $film = $view->viewable;
                $final_list[] = [
                    'id' => $film->id,
                    'type' => Film::class,
                    'persian_title' => $film->persian_name,
                    'english_title' => $film->english_name,
                    'poster' => $film->film_poster,
                    'percentage' => $view->percentage,
                ];
            }
            if ($view->viewable_type === Music::class) {
                $music = $view->viewable;
                $final_list[] = [
                    'id' => $music->id,
                    'type' => Music::class,
                    'persian_title' => $music->persian_name,
                    'english_title' => $music->name,
                    'poster' => $music->music_poster,
                    'percentage' => $view->percentage,
                ];
            }
        }

        return response()->json([
            'data' => $final_list,
            'errors' => null,
            'message' => "اطلاعات با موفقیت دریافت شد"
        ]);
    }

    public function addView(Request $request)
    {
        $messages = array(
            'viewable_id.required' => 'شناسه نمی تواند خالی باشد',
            'viewable_type.required' => 'نوع نمی تواند خالی باشد',
            'total.required' => 'مقدار کل نمی تواند خالی باشد',
            'total.numeric' => 'مقدار کل باید عدد باشد',
            'progress.required' => 'پیشرفت نمی تواند خالی باشد',
            'progress.numeric' => 'پیشرفت باید عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'viewable_id' => 'required|numeric',
            'viewable_type' => 'required',
            'total' => 'required|numeric',
            'progress' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن بازدید شکست خورد",
            ]);
        }

        if ($request->input('total') < $request->input('progress')) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "افزودن بازدید شکست خورد",
            ]);
        }

        $user = (new UserHelper())->getUserDetailByToken($request->header("ApiToken"));
        if (!$user) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "کاربر یافت نشد",
            ]);
        }

        $id = $request->input('viewable_id');
        if ($request->input('viewable_type') === 'music') {
            $model = Music::find($id);
            $model_type = Music::class;
        }
        else if ($request->input('viewable_type') === 'film') {
            $model = Film::find($id);
            $model_type = Film::class;
        }
        else {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "مدل یافت نشد",
            ]);
        }

        if (!$model) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "مدل یافت نشد",
            ]);
        }
        $is_complete = boolval($request->input('is_complete'));

        $view = View::where('user_id' , $user->id)->where('viewable_id' , $id)->where('viewable_type' , $model_type)->first();
        if ($view) {
            $view->total = $request->input('total');
            $view->progress = $view->total > $request->input('progress') ? $request->input('progress') : $view->total;
            if ($is_complete || $view->total === $view->progress) {
                $view->percentage = 100;
            } else {
                $view->percentage = round(($view->progress * 100) / $view->total);
            }
            $view->updated_at = now();
            $view->save();
        } else {
            View::create([
                'user_id' => $user->id,
                'viewable_id' => $id,
                'viewable_type' => $model_type,
                'total' => $request->input('total'),
                'percentage' => round(($request->input('progress') * 100) / $request->input('total')),
                'progress' => $request->input('progress'),
            ]);
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "اطلاعات با موفقیت ثبت شد"
        ]);
    }
}
