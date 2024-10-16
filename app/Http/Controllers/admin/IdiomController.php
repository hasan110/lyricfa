<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\IdiomHelper;
use App\Models\Idiom;
use App\Models\IdiomDefinition;
use App\Models\IdiomDefinitionExample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class IdiomController extends Controller
{
    public function getIdiomsWord(Request $request)
    {
        $word = $request->word;
        $get_idioms = Idiom::where('base', $word)->get();

        if (!$get_idioms) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "هیچ کلمه ای پیدا نشد",
            ]);
        } else {
            return response()->json([
                'data' => $get_idioms,
                'errors' => [],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);
        }

    }


    public function searchIdiom(Request $request)
    {
        $idioms = Idiom::orderBy('id', 'DESC')->
        where('phrase', 'LIKE', "%{$request->search_text}%")->
        orWhere('definition', 'LIKE', "%{$request->search_text}%")->
        paginate(25);


        foreach ($idioms as $index => $item) {
            $rooms = json_decode($item->definition, true);

            $idioms[$index]->parse = $rooms;
        }

        return response()->json([
            'data' => $idioms,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);

    }

    public static function getIdiomById(int $id)
    {
        $idiom =  Idiom::where('id', $id)->first();

            $rooms = json_decode($idiom->definition, true);

            $idiom->parse = $rooms;
            return $idiom;
    }


    public function idiomsList(Request $request)
    {
        $list = Idiom::with('idiom_definitions');

        if ($request->search_key) {
            if (isset($request->equals) && $request->equals) {
                $list = $list->where('base', '=', $request->search_key)->
                orWhere('phrase', '=', $request->search_key);
            } else {
                $list = $list->where('base', 'LIKE', "%{$request->search_key}%")->
                orWhere('phrase', 'LIKE', "%{$request->search_key}%");
            }
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

    public function getIdiom(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه اصطلاح نمی تواند خالی باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت اطلاعات لغت شکست خورد",
            ], 400);
        }

        $get = Idiom::with('idiom_definitions')->find($request->id);
        if(!$get){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " اصطلاح یافت نشد.",
            ], 404);
        }

        return response()->json([
            'data' => $get,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ]);
    }

    public function createIdiom(Request $request)
    {
        $messages = array(
            'base.required' => 'لغت پایه اصطلاح نمی تواند خالی باشد',
            'phrase.required' => 'متن اصطلاح نمی تواند خالی باشد',
            'phrase.unique' => 'این اصطلاح قبلا ثبت شده و تکراری است',
            'idiom_definitions.*.definition.filled' => 'معنی اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.definition.filled' => 'معنی مثال اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.phrase.filled' => 'عبارت مثال اصطلاح نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'phrase' => 'required|unique:idioms',
            'base' => 'required',
            'idiom_definitions.*.definition' => 'filled',
            'idiom_definitions.*.idiom_definition_examples.*.definition' => 'filled',
            'idiom_definitions.*.idiom_definition_examples.*.phrase' => 'filled',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن اصطلاح با مشکل اعتبارسنجی مواجه شد",
            ], 422);
        }

        if (in_array(intval($request->type) , [0,1,2,3,4,5])) {
            $type = intval($request->type);
        } else {
            $type = 0;
        }

        $idiom = new Idiom();
        $idiom->phrase = $request->phrase;
        $idiom->phrase_base = (new IdiomHelper())->convertPhraseToBase($request->phrase);
        $idiom->base = $request->base;
        $idiom->type = $type;
        $idiom->save();

        foreach ($request->idiom_definitions as $definition)
        {
            $idiom_definition = new IdiomDefinition();
            $idiom_definition->idiom_id = $idiom->id;
            $idiom_definition->definition = $definition['definition'];
            $idiom_definition->save();

            foreach ($definition['idiom_definition_examples'] as $definition_example)
            {
                $idiom_definition_example = new IdiomDefinitionExample();
                $idiom_definition_example->idiom_definition_id = $idiom_definition->id;
                $idiom_definition_example->phrase = $definition_example['phrase'];
                $idiom_definition_example->definition = $definition_example['definition'];
                $idiom_definition_example->save();
            }
        }

        return response()->json([
            'data' => $idiom,
            'errors' => null,
            'message' => "اصطلاح با موفقیت اضافه شد"
        ]);
    }

    public function updateIdiom(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه اصطلاح نمی تواند خالی باشد',
            'base.required' => 'لغت پایه اصطلاح نمی تواند خالی باشد',
            'phrase.required' => 'متن اصطلاح نمی تواند خالی باشد',
            'idiom_definitions.*.definition.filled' => 'معنی اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.definition.filled' => 'معنی مثال اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.phrase.filled' => 'عبارت مثال اصطلاح نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'phrase' => 'required',
            'base' => 'required',
            'idiom_definitions.*.definition' => 'filled',
            'idiom_definitions.*.idiom_definition_examples.*.definition' => 'filled',
            'idiom_definitions.*.idiom_definition_examples.*.phrase' => 'filled',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش اصطلاح با مشکل اعتبارسنجی مواجه شد",
            ], 422);
        }

        if (in_array(intval($request->type) , [0,1,2,3,4,5])) {
            $type = intval($request->type);
        } else {
            $type = 0;
        }

        $idiom = Idiom::with('idiom_definitions')->find($request->id);
        if(!$idiom){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " اصطلاح یافت نشد.",
            ], 404);
        }
        $idiom->phrase = $request->phrase;
        $idiom->phrase_base = (new IdiomHelper())->convertPhraseToBase($request->phrase);
        $idiom->base = $request->base;
        $idiom->type = $type;
        $idiom->save();

        // delete all word relations and ...
        foreach ($idiom->idiom_definitions as $item){
            foreach ($item->idiom_definition_examples as $example_item){
                $example_item->delete();
            }
            $item->delete();
        }

        foreach ($request->idiom_definitions as $definition)
        {
            $idiom_definition = new IdiomDefinition();
            $idiom_definition->idiom_id = $idiom->id;
            $idiom_definition->definition = $definition['definition'];
            $idiom_definition->save();

            foreach ($definition['idiom_definition_examples'] as $definition_example)
            {
                $idiom_definition_example = new IdiomDefinitionExample();
                $idiom_definition_example->idiom_definition_id = $idiom_definition->id;
                $idiom_definition_example->phrase = $definition_example['phrase'];
                $idiom_definition_example->definition = $definition_example['definition'];
                $idiom_definition_example->save();
            }
        }

        return response()->json([
            'data' => $idiom,
            'errors' => null,
            'message' => "اصطلاح باموفقیت ویرایش شد"
        ]);
    }

    public function removeIdiom(Request $request)
    {
        $idiom = Idiom::with('idiom_definitions')->find($request->id);
        if(!$idiom){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " اصطلاح یافت نشد.",
            ], 404);
        }

        // delete all word relations and ...
        foreach ($idiom->idiom_definitions as $item){
            foreach ($item->idiom_definition_examples as $example_item){
                $example_item->delete();
            }
            $item->delete();
        }
        $idiom->delete();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "اصطلاح باموفقیت حذف شد"
        ]);
    }
}
