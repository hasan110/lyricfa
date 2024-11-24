<?php

namespace App\Http\Controllers\admin;

use App\Models\MapReason;
use App\Models\Word;
use App\Models\Map;
use App\Models\WordDefinition;
use App\Models\WordDefinitionExample;
use App\Models\WordEnEn;
use App\Models\WordEnEnDefinition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class MapController extends Controller
{
    public function MapsList(Request $request)
    {
        $list = Map::query();

        if ($request->word) {
            $list = $list->where('word', 'LIKE', $this->getSearchModel($request->word_search_model , $request->word));
        }
        if ($request->base_word) {
            $list = $list->where('ci_base', 'LIKE', $this->getSearchModel($request->base_word_search_model , $request->base_word));
        }
        if ($request->word_types) {
            $list = $list->where('word_types', 'LIKE', $this->getSearchModel($request->word_types_search_model , $request->word_types));
        }
        if ($request->base_word_types) {
            $list = $list->where('base_word_types', 'LIKE', $this->getSearchModel($request->base_word_types_search_model , $request->base_word_types));
        }

        if ($request->sort_by) {
            switch ($request->sort_by) {
                case 'update':
                default:
                    $list = $list->orderBy('updated_at');
                    break;
                case 'asc':
                    $list = $list->orderBy('id');
                    break;
                case 'desc':
                    $list = $list->orderBy('id', 'desc');
                    break;
            }
        }

        $list = $list->with('map_reasons')->paginate(50);

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    private function getSearchModel($searching_model , $phrase)
    {
        $search_model = "_";
        if ($searching_model) {
            switch ($searching_model) {
                case 'first_like':
                    $search_model = "_%";
                    break;
                case 'last_like':
                    $search_model = "%_";
                    break;
                case 'like':
                    $search_model = "%_%";
                    break;
            }
        }

        return str_replace("_" , $phrase , $search_model);
    }

    public function MapReasonsList(Request $request)
    {
        $list = MapReason::query();

        if ($request->search_key) {
            $list = $list->where('english_title', 'LIKE', "%{$request->search_key}%")
                ->orWhere('persian_title', 'LIKE', "%{$request->search_key}%");
        }
        if ($request->sort_by) {
            switch ($request->sort_by) {
                case 'asc':
                default:
                    $list = $list->orderBy('id');
                    break;
                case 'desc':
                    $list = $list->orderBy('id', 'desc');
                    break;
            }
        }
        $list = $list->paginate(50);

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getMap(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه مپ نمی تواند خالی باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت اطلاعات مپ شکست خورد",
            ], 400);
        }

        $get = Map::find($request->id);
        if(!$get){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " مپ یافت نشد.",
            ], 404);
        }
        $get['map_reasons'] = $get->map_reasons()->pluck('id')->toArray();
        return response()->json([
            'data' => $get,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ]);
    }

    public function createMap(Request $request)
    {
        $messages = array(
            'word.required' => 'لغت نمی تواند خالی باشد',
            'base_word.required' => 'لغت پایه نمی تواند خالی باشد',
            'map_reasons.array' => 'علت مپ شدن باید آرایه باشد.',
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required',
            'base_word' => 'required',
            'map_reasons' => 'array',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن مپ لغت با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $map = new Map();
        $map->word = $request->word;
        $map->ci_base = $request->base_word;
        $map->word_types = $request->word_types ?? null;
        $map->base_word_types = $request->base_word_types ?? null;
        $map->save();

        if($request->has('map_reasons')) {
            $map->map_reasons()->sync($request->map_reasons);
        }

        return response()->json([
            'data' => $map,
            'errors' => null,
            'message' => "مپ لغت با موفقیت اضافه شد"
        ]);
    }

    public function updateMap(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'word.required' => 'لغت نمی تواند خالی باشد',
            'ci_base.required' => 'لغت پایه نمی تواند خالی باشد',
            'map_reasons.array' => 'علت مپ شدن باید آرایه باشد.',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'word' => 'required',
            'ci_base' => 'required',
            'map_reasons' => 'array',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش مپ لغت با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $map = Map::find($request->id);
        if (!$map) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "مپ یافت نشد",
            ] , 400);
        }
        $map->word = $request->word;
        $map->ci_base = $request->ci_base;
        $map->word_types = $request->word_types ?? null;
        $map->base_word_types = $request->base_word_types ?? null;
        $map->save();

        if($request->has('map_reasons')) {
            $map->map_reasons()->sync($request->map_reasons);
        }

        return response()->json([
            'data' => $map,
            'errors' => null,
            'message' => "مپ لغت با موفقیت ویرایش شد"
        ]);
    }

    public function createMapReason(Request $request)
    {
        $messages = array(
            'english_title.required' => 'عنوان انگلیسی نمی تواند خالی باشد',
            'persian_title.required' => 'عنوان فارسی نمی تواند خالی باشد',
            'type.required' => 'نوع علت مپ شدن نمی تواند خالی باشد',
            'type.unique' => 'نوع علت مپ شدن وارد شده تکراری است.',
            'description.required' => 'فیلد توضیحات نمی تواند خالی باشد',
        );

        $validator = Validator::make($request->all(), [
            'english_title' => 'required',
            'persian_title' => 'required',
            'type' => 'required|unique:map_reasons',
            'description' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن علت مپ شدن با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $map_reason = new MapReason();
        $map_reason->english_title = $request->english_title;
        $map_reason->persian_title = $request->persian_title;
        $map_reason->type = $request->type;
        $map_reason->description = $request->description;
        $map_reason->save();

        return response()->json([
            'data' => $map_reason,
            'errors' => null,
            'message' => "علت مپ شدن با موفقیت اضافه شد"
        ]);
    }

    public function updateMapReason(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'english_title.required' => 'عنوان انگلیسی نمی تواند خالی باشد',
            'persian_title.required' => 'عنوان فارسی نمی تواند خالی باشد',
            'description.required' => 'فیلد توضیحات نمی تواند خالی باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'english_title' => 'required',
            'persian_title' => 'required',
            'description' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش علت مپ شدن با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $map_reason = MapReason::find($request->id);
        $map_reason->english_title = $request->english_title;
        $map_reason->persian_title = $request->persian_title;
        $map_reason->description = $request->description;
        $map_reason->save();

        return response()->json([
            'data' => $map_reason,
            'errors' => null,
            'message' => "علت مپ شدن با موفقیت ویرایش شد"
        ]);
    }

    public function removeMapReason(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "حذف علت مپ با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $map_reason = MapReason::find($request->id);
        if (!$map_reason) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "حذف علت مپ با مشکل مواجه شد",
            ], 400);
        }
        $map_reason->delete();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "حذف علت مپ با موفقیت انجام شد"
        ]);
    }

    public function groupEditWordMapReason(Request $request)
    {
        $messages = array(
            'maps.required' => 'انتخاب لغات مپ شده اجباری است.',
            'maps.array' => 'لغات مپ شده باید آرایه باشد.',
            'map_reasons.required' => 'انتخاب علت مپ شدن اجباری است.',
            'map_reasons.array' => 'علت مپ شدن باید آرایه باشد.',
        );

        $validator = Validator::make($request->all(), [
            'maps' => 'required|array',
            'map_reasons' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش علت مپ شدن با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        foreach ($request->maps as $map_id)
        {
            $map = Map::find($map_id);
            if ($map) {
                $map->map_reasons()->sync($request->map_reasons);
            }
            $map->update([
                'updated_at' => Carbon::now()
            ]);
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "ویرایش علت مپ لغت با موفقیت انجام شد"
        ]);
    }
}
