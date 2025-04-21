<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Category;
use App\Models\Film;
use App\Models\View;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategoryData(Request $request)
    {
        if (!$request->input('category_id')) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "شناسه دسته بندی را ارسال نمایید"
            ], 422);
        }

        $category = Category::where('id' , $request->input('category_id'))->where('mode' , 'category')->with(['children' => function ($q) {
            $q->with(['children','grammers','musics','films','word_definitions']);
        }])->first();
        if (!$category) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "دسته بندی یافت نشد"
            ], 404);
        }

        if ($category->belongs_to === 'word_definitions') {
            $word_definitions = $category->word_definitions()->with(['word' , 'categories'])->get()->map(function ($item) {
                return [
                    'type' => 'word_definition',
                    'word' => $item->word,
                    'idiom' => null,
                    'definition' => $item,
                ];
            });
            $idiom_definitions = $category->idiom_definitions()->with(['idiom' , 'categories'])->get()->map(function ($item) {
                return [
                    'type' => 'idiom_definition',
                    'word' => null,
                    'idiom' => $item->idiom,
                    'definition' => $item,
                ];
            });
            $category->items = collect()->merge($word_definitions)->merge($idiom_definitions);
        } else if ($category->belongs_to === 'grammers') {
            $category->items = $category->grammers()->orderBy('level')->get();
        } else if ($category->belongs_to === 'musics') {
            $category->items = (new MusicHelper())->prepareMusicsTemplate($category->musics()->get());
        } else if ($category->belongs_to === 'films') {
            $category->items = $category->films()->get();
        } else {
            $category->items = [];
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $category->readable_like = (new Helper())->readableNumber($category->likes()->count());
        $category->readable_comment = (new Helper())->readableNumber($category->comments()->count());
        $category->user_like_it = $category->likes()->where('user_id', $user->id)->count();
        $category['view'] = View::where('user_id' , $user->id)->where('viewable_type', Category::class)->where('viewable_id' , $category->id)->first();

        foreach ($category->children as $item) {
            $item->view = $item->views()->where('user_id' , $user->id)->first();
        }

        return response()->json([
            'data' => $category,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}
