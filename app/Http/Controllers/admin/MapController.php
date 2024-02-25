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
        $messsages = array(
            'id.required' => 'شناسه مپ نمی تواند خالی باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت اطلاعات مپ شکست خورد",
            ];
            return response()->json($arr, 400);
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
            'map_reasons.required' => 'انتخاب علت مپ شدن اجباری است.',
            'map_reasons.array' => 'علت مپ شدن باید آرایه باشد.',
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required',
            'base_word' => 'required',
            'map_reasons' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن مپ لغت با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
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

        $arr = [
            'data' => $map,
            'errors' => null,
            'message' => "مپ لغت با موفقیت اضافه شد"
        ];

        return response()->json($arr);
    }

    public function updateMap(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'word.required' => 'لغت نمی تواند خالی باشد',
            'ci_base.required' => 'لغت پایه نمی تواند خالی باشد',
            'map_reasons.required' => 'انتخاب علت مپ شدن اجباری است.',
            'map_reasons.array' => 'علت مپ شدن باید آرایه باشد.',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'word' => 'required',
            'ci_base' => 'required',
            'map_reasons' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش مپ لغت با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
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

        $arr = [
            'data' => $map,
            'errors' => null,
            'message' => "مپ لغت با موفقیت ویرایش شد"
        ];

        return response()->json($arr);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن علت مپ شدن با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
        }

        $map_reason = new MapReason();
        $map_reason->english_title = $request->english_title;
        $map_reason->persian_title = $request->persian_title;
        $map_reason->type = $request->type;
        $map_reason->description = $request->description;
        $map_reason->save();

        $arr = [
            'data' => $map_reason,
            'errors' => null,
            'message' => "علت مپ شدن با موفقیت اضافه شد"
        ];

        return response()->json($arr);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش علت مپ شدن با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
        }

        $map_reason = MapReason::find($request->id);
        $map_reason->english_title = $request->english_title;
        $map_reason->persian_title = $request->persian_title;
        $map_reason->description = $request->description;
        $map_reason->save();

        $arr = [
            'data' => $map_reason,
            'errors' => null,
            'message' => "علت مپ شدن با موفقیت ویرایش شد"
        ];

        return response()->json($arr);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "حذف علت مپ با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
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

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "حذف علت مپ با موفقیت انجام شد"
        ];

        return response()->json($arr);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش علت مپ شدن با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
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

        $arr = [
            'data' => $map,
            'errors' => null,
            'message' => "ویرایش علت مپ لغت با موفقیت انجام شد"
        ];

        return response()->json($arr);
    }


    public function updateGrammer(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'english_word.required' => 'لغت نمی تواند خالی باشد',
            'word_definitions.*.definition.filled' => 'معنی لغت نمی تواند خالی باشد.',
            'english_definitions.*.definition.filled' => 'معنی انگلیسی لغت نمی تواند خالی باشد.',
            'word_definitions.*.word_definition_examples.*.definition.filled' => 'معنی مثال لغت نمی تواند خالی باشد.',
            'word_definitions.*.word_definition_examples.*.phrase.filled' => 'عبارت مثال لغت نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'english_word' => 'required',
            'word_definitions.*.definition' => 'filled',
            'english_definitions.*.definition' => 'filled',
            'word_definitions.*.word_definition_examples.*.definition' => 'filled',
            'word_definitions.*.word_definition_examples.*.phrase' => 'filled',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن لغت با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
        }

        $word = Word::with('word_definitions')->find($request->id);
        if(!$word){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " لغت یافت نشد.",
            ], 404);
        }
        // delete all word relations and ...
        foreach ($word->word_definitions as $item){
            foreach ($item->word_definition_examples as $example_item){
                $example_item->delete();
            }
            $item->delete();
        }

        $word->english_word = $request->english_word;
        $word->pronunciation = $request->pronunciation;
        $word->word_types = $request->word_types;
        $word->save();

        foreach ($request->word_definitions as $definition)
        {
            $word_definition = new WordDefinition();
            $word_definition->word_id = $word->id;
            $word_definition->definition = $definition['definition'];
            $word_definition->save();

            foreach ($definition['word_definition_examples'] as $definition_example)
            {
                $word_definition_example = new WordDefinitionExample();
                $word_definition_example->word_definition_id = $word_definition->id;
                $word_definition_example->phrase = $definition_example['phrase'];
                $word_definition_example->definition = $definition_example['definition'];
                $word_definition_example->save();
            }
        }

        if($request->english_definitions)
        {
            $check = WordEnEn::with('english_word_definitions')->where('ci_word' , $request->english_word)->first();
            if ($check){
                $english_word = $check;
            }else{
                $english_word = WordEnEn::create([
                    'ci_word' => $request->english_word
                ]);
            }
            foreach ($english_word->english_word_definitions as $english_item){
                $english_item->delete();
            }

            foreach ($request->english_definitions as $english_definition)
            {
                $english_word_definition = new WordEnEnDefinition();
                $english_word_definition->english_word_id = $english_word->id;
                $english_word_definition->pronounciation = $english_definition['pronounciation'] ?? null;
                $english_word_definition->word_type = $english_definition['word_type'] ?? null;
                $english_word_definition->definition = $english_definition['definition'];
                $english_word_definition->save();

            }
        }

        $arr = [
            'data' => $word,
            'errors' => null,
            'message' => "لغت با موفقیت اضافه شد"
        ];

        return response()->json($arr, 200);
    }

    public function getGrammer(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه لغت نمی تواند خالی باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت اطلاعات لغت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $get = Word::with('word_definitions')->find($request->id);
        if(!$get){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " لغت یافت نشد.",
            ], 404);
        }
        $english_word = WordEnEn::where('ci_word' , $get->english_word)->first();
        if($english_word){
            $get['english_definitions'] = $english_word->english_word_definitions;
        }

        $arr = [
            'data' => $get,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ];
        return response()->json($arr, 200);
    }

    public function removeGrammer(Request $request)
    {
        $word = Word::with('word_definitions')->find($request->id);
        if(!$word){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " لغت یافت نشد.",
            ], 404);
        }
        $english_word = WordEnEn::where('ci_word' , $word->english_word)->with('english_word_definitions')->first();

        foreach ($word->word_definitions as $word_definition){
            foreach ($word_definition->word_definition_examples as $word_definition_example) {
                $word_definition_example->delete();
            }
            $word_definition->delete();
        }
        $word->delete();

        if ($english_word){
            foreach ($english_word->english_word_definitions as $english_word_definition){
                $english_word_definition->delete();
            }
            $english_word->delete();
        }

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => " تمامی اطلاعات این لغت با موفقیت حذف شد.",
        ];
        return response()->json($arr);
    }
}
