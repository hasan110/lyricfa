<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\IdiomHelper;
use App\Models\File;
use App\Models\Idiom;
use App\Models\IdiomDefinition;
use App\Models\IdiomDefinitionExample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IdiomController extends Controller
{
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
            'level.required' => 'سطح باید انتخاب شود',
            'level.in' => 'سطح باید یکی از موارد: A1, A2, B1, B2, C1, C2 باشد',
            'phrase.required' => 'متن اصطلاح نمی تواند خالی باشد',
            'phrase.unique' => 'این اصطلاح قبلا ثبت شده و تکراری است',
            'idiom_definitions.*.definition.filled' => 'معنی اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.level.filled' => 'سطح معنی لغت نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.definition.filled' => 'معنی مثال اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.phrase.filled' => 'عبارت مثال اصطلاح نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'phrase' => 'required|unique:idioms',
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
            'base' => 'required',
            'idiom_definitions.*.definition' => 'filled',
            'idiom_definitions.*.level' => 'filled',
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
        $idiom->level = $request->level;
        $idiom->phrase_base = (new IdiomHelper())->convertPhraseToBase($request->phrase);
        $idiom->base = $request->base;
        $idiom->type = $type;
        $idiom->save();

        foreach ($request->idiom_definitions as $key => $definition)
        {
            $idiom_definition = new IdiomDefinition();
            $idiom_definition->idiom_id = $idiom->id;
            $idiom_definition->definition = $definition['definition'];
            $idiom_definition->level = $definition['level'];
            $idiom_definition->priority = $key + 1;
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
            'level.required' => 'سطح باید انتخاب شود',
            'level.in' => 'سطح باید یکی از موارد: A1, A2, B1, B2, C1, C2 باشد',
            'phrase.required' => 'متن اصطلاح نمی تواند خالی باشد',
            'idiom_definitions.*.definition.filled' => 'معنی اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.level.filled' => 'سطح معنی لغت نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.definition.filled' => 'معنی مثال اصطلاح نمی تواند خالی باشد.',
            'idiom_definitions.*.idiom_definition_examples.*.phrase.filled' => 'عبارت مثال اصطلاح نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'phrase' => 'required',
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
            'base' => 'required',
            'idiom_definitions.*.definition' => 'filled',
            'idiom_definitions.*.level' => 'filled',
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
        $idiom->level = $request->level;
        $idiom->phrase_base = (new IdiomHelper())->convertPhraseToBase($request->phrase);
        $idiom->base = $request->base;
        $idiom->type = $type;
        $idiom->save();

        // delete all idiom definition examples ...
        foreach ($idiom->idiom_definitions as $item){
            foreach ($item->idiom_definition_examples as $example_item){
                $example_item->delete();
            }
        }

        foreach ($request->idiom_definitions as $key => $definition)
        {
            if (isset($definition['id'])) {
                $idiom_definition = IdiomDefinition::find($definition['id']);
                if (!$idiom_definition) continue;
            } else {
                $idiom_definition = new IdiomDefinition();
                $idiom_definition->idiom_id = $idiom->id;
            }
            $idiom_definition->definition = $definition['definition'];
            $idiom_definition->level = $definition['level'];
            $idiom_definition->priority = $key + 1;
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

    public function updateIdiomLevel(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه اصطلاح نمی تواند خالی باشد',
            'level.required' => 'سطح باید انتخاب شود',
            'level.in' => 'سطح باید یکی از موارد: A1, A2, B1, B2, C1, C2 باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش اصطلاح با مشکل اعتبارسنجی مواجه شد",
            ], 422);
        }

        $idiom = Idiom::with('idiom_definitions')->find($request->id);
        if (!$idiom) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " اصطلاح یافت نشد.",
            ], 404);
        }

        $idiom->level = $request->level;
        $idiom->save();

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

    public function addImageToDefinition(Request $request)
    {
        $messages = array(
            'idiom_definition_id.required' => 'شناسه معنی لغت اجباری است',
            'image.required' => 'تصویر باید انتخاب شود',
            'image.file' => 'تصویر باید فایل باشد',
            'image.mimes' => 'نوع تصویر باید jpg باشد',
            'image.dimensions' => 'تصویر باید 300 در 450 باشد',
            'image.max' => 'حجم تصویر حداکثر باید 50 کیلو بایت باشد',
        );

        $validator = Validator::make($request->all(), [
            'idiom_definition_id' => 'required',
            'image' => 'required|file|mimes:jpg|dimensions:min_width=450,min_height=300,max_width=450,max_height=300|max:50'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "عملیات شکست خورد",
            ], 400);
        }

        $definition = IdiomDefinition::find($request->idiom_definition_id);
        if (!$definition) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "معنی اصطلاح یافت نشد",
            ], 400);
        }

        if ($definition->files) {
            File::deleteFile($definition->files, IdiomDefinition::IMAGE_FILE_TYPE);
        }

        File::createFile($request->image, $definition, IdiomDefinition::IMAGE_FILE_TYPE, true);

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "تصویر با موفقیت اضافه شد"
        ]);
    }
}
