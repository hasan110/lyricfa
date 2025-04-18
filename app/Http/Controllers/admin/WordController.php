<?php

namespace App\Http\Controllers\admin;

use App\Models\File;
use App\Models\Film;
use App\Models\Word;
use App\Models\WordDefinition;
use App\Models\WordDefinitionExample;
use App\Models\WordEnEn;
use App\Models\WordEnEnDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class WordController extends Controller
{
    public function WordsList(Request $request)
    {
        $list = Word::with('word_definitions');

        if ($request->search_key) {
            if (isset($request->equals) && $request->equals) {
                $list = $list->where('english_word', '=', $request->search_key)->
                orWhere('pronunciation', '=', $request->search_key);
            } else {
                $list = $list->where('english_word', 'LIKE', "%{$request->search_key}%")->
                orWhere('pronunciation', 'LIKE', "%{$request->search_key}%");
            }
        }
        if ($request->sort_by) {
            switch ($request->sort_by) {
                case 'word_asc':
                default:
                    $list = $list->orderBy('english_word', 'asc');
                    break;
                case 'word_desc':
                    $list = $list->orderBy('english_word', 'desc');
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

    public function createWord(Request $request)
    {
        $messages = array(
            'english_word.required' => 'لغت نمی تواند خالی باشد',
            'english_word.unique' => 'این لغت قبلا ثبت شده و تکراری است',
            'level.required' => 'سطح نمیتواند خالی باشد',
            'level.in' => 'سطح باید یکی از موارد: A1, A2, B1, B2, C1, C2 باشد',
            'definitions.*.definition.filled' => 'معنی لغت نمی تواند خالی باشد.',
            'definitions.*.level.filled' => 'سطح معنی لغت نمی تواند خالی باشد.',
            'definitions.*.type.filled' => 'نوع معنی لغت نمی تواند خالی باشد.',
            'english_definitions.*.definition.filled' => 'معنی انگلیسی لغت نمی تواند خالی باشد.',
            'definitions.*.definition_examples.*.definition.filled' => 'معنی مثال لغت نمی تواند خالی باشد.',
            'definitions.*.definition_examples.*.phrase.filled' => 'عبارت مثال لغت نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'english_word' => 'required|unique:words',
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
            'definitions.*.definition' => 'filled',
            'definitions.*.level' => 'filled',
            'definitions.*.type' => 'filled',
            'english_definitions.*.definition' => 'filled',
            'definitions.*.definition_examples.*.definition' => 'filled',
            'definitions.*.definition_examples.*.phrase' => 'filled',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن لغت با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $word = new Word();
        $word->english_word = $request->english_word;
        $word->level = $request->level;
        $word->us_pronunciation = $request->us_pronunciation;
        $word->uk_pronunciation = $request->uk_pronunciation;
        $word->word_types = $request->word_type ? implode(',', $request->word_type) : null;

        $word->save();

        foreach ($request->definitions as $key => $definition)
        {
            $word_definition = new WordDefinition();
            $word_definition->word_id = $word->id;
            $word_definition->definition = $definition['definition'];
            $word_definition->level = $definition['level'];
            $word_definition->type = $definition['type'];
            $word_definition->description = $definition['description'] ?? null;
            $word_definition->priority = $key + 1;
            $word_definition->save();

            foreach ($definition['definition_examples'] as $definition_example)
            {
                $word_definition_example = new WordDefinitionExample();
                $word_definition_example->word_definition_id = $word_definition->id;
                $word_definition_example->phrase = $definition_example['phrase'];
                $word_definition_example->definition = $definition_example['definition'];
                $word_definition_example->save();
            }
        }

        if($request->english_definitions) {
            $check = WordEnEn::where('ci_word' , $request->english_word)->first();
            if ($check) {
                $english_word = $check;
            }else{
                $english_word = WordEnEn::create([
                    'ci_word' => $request->english_word
                ]);
            }
            foreach ($request->english_definitions as $english_definition)
            {
                $english_word_definition = new WordEnEnDefinition();
                $english_word_definition->english_word_id = $english_word->id;
                $english_word_definition->pronounciation = $english_definition['pronunciation'] ?? null;
                $english_word_definition->word_type = $english_definition['word_type'] ?? null;
                $english_word_definition->definition = $english_definition['definition'];
                $english_word_definition->save();
            }
        }

        return response()->json([
            'data' => $word,
            'errors' => null,
            'message' => "لغت با موفقیت اضافه شد"
        ]);
    }

    public function updateWord(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'english_word.required' => 'لغت نمی تواند خالی باشد',
            'level.required' => 'سطح نمیتواند خالی باشد',
            'level.in' => 'سطح باید یکی از موارد: A1, A2, B1, B2, C1, C2 باشد',
            'word_definitions.*.definition.filled' => 'معنی لغت نمی تواند خالی باشد.',
            'word_definitions.*.level.filled' => 'سطح معنی لغت نمی تواند خالی باشد.',
            'word_definitions.*.type.filled' => 'نوع معنی لغت نمی تواند خالی باشد.',
            'english_definitions.*.definition.filled' => 'معنی انگلیسی لغت نمی تواند خالی باشد.',
            'word_definitions.*.word_definition_examples.*.definition.filled' => 'معنی مثال لغت نمی تواند خالی باشد.',
            'word_definitions.*.word_definition_examples.*.phrase.filled' => 'عبارت مثال لغت نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'english_word' => 'required',
            // 'level' => 'in:A1,A2,B1,B2,C1,C2',
            'word_definitions.*.definition' => 'filled',
            //'word_definitions.*.level' => 'filled',
            //'word_definitions.*.type' => 'filled',
            'english_definitions.*.definition' => 'filled',
            'word_definitions.*.word_definition_examples.*.definition' => 'filled',
            'word_definitions.*.word_definition_examples.*.phrase' => 'filled',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن لغت با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $word = Word::with('word_definitions')->find($request->id);
        if(!$word){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " لغت یافت نشد.",
            ], 404);
        }

        // delete word definition examples
        foreach ($word->word_definitions as $item){
            foreach ($item->word_definition_examples as $example_item){
                $example_item->delete();
            }
        }

        $word->english_word = $request->english_word;
        $word->level = $request->level ?? null;
        $word->us_pronunciation = $request->us_pronunciation;
        $word->uk_pronunciation = $request->uk_pronunciation;
        $word->word_types = $request->word_type ? implode(',', $request->word_type) : null;
        $word->save();

        foreach ($request->word_definitions as $key => $definition)
        {
            if (isset($definition['id'])) {
                $word_definition = WordDefinition::find($definition['id']);
                if (!$word_definition) continue;
            } else {
                $word_definition = new WordDefinition();
                $word_definition->word_id = $word->id;
            }
            $word_definition->definition = $definition['definition'];
            $word_definition->level = $definition['level'] ?? null;
            $word_definition->type = $definition['type'] ?? null;
            $word_definition->description = $definition['description'] ?? null;
            $word_definition->priority = $key + 1;
            $word_definition->save();

            if (isset($definition['word_definition_examples'])) {
                foreach ($definition['word_definition_examples'] as $definition_example)
                {
                    $word_definition_example = new WordDefinitionExample();
                    $word_definition_example->word_definition_id = $word_definition->id;
                    $word_definition_example->phrase = $definition_example['phrase'];
                    $word_definition_example->definition = $definition_example['definition'];
                    $word_definition_example->save();
                }
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

        return response()->json([
            'data' => $word,
            'errors' => null,
            'message' => "لغت با موفقیت اضافه شد"
        ]);
    }

    public function updateWordLevel(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'level.required' => 'سطح نمیتواند خالی باشد',
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
                'message' => "افزودن لغت با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $word = Word::with('word_definitions')->find($request->id);
        if (!$word) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " لغت یافت نشد.",
            ], 404);
        }

        $word->level = $request->level;
        $word->save();

        return response()->json([
            'data' => $word,
            'errors' => null,
            'message' => "لغت با موفقیت اضافه شد"
        ]);
    }

    public function getWord(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه لغت نمی تواند خالی باشد'
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

        $get = Word::with(['word_definitions' => function ($query) {
            $query->with(['text_joins' => function ($q) {
                $q->with('text');
            } , 'categories']);
        }])->find($request->id);
        if(!$get){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " لغت یافت نشد.",
            ], 404);
        }

        foreach ($get->word_definitions as $word_definition) {
            $word_definition->categories_ids = $word_definition->categories->pluck('id')->toArray();
        }

        $english_word = WordEnEn::where('ci_word' , $get->english_word)->first();
        if($english_word){
            $get['english_definitions'] = $english_word->english_word_definitions;
        } else {
            $get['english_definitions'] = [];
        }

        return response()->json([
            'data' => $get,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ]);
    }

    public function removeWord(Request $request)
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

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => " تمامی اطلاعات این لغت با موفقیت حذف شد.",
        ]);
    }

    public function removeWordDefinition(Request $request)
    {
        $word_definition = WordDefinition::find($request->id);
        if(!$word_definition){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " معنی لغت یافت نشد.",
            ], 404);
        }

        if($word_definition->joins_count > 0){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این معنی دارای اتصال می باشد و قابل حذف نیست.",
            ], 404);
        }

        if(count($word_definition->files) > 0){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این معنی دارای فایل می باشد و قابل حذف نیست.",
            ], 404);
        }

        foreach ($word_definition->word_definition_examples as $example_item){
            $example_item->delete();
        }
        $word_definition->delete();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "معنی لغت باموفقیت حذف شد"
        ]);
    }

    public function addImageToDefinition(Request $request)
    {
        $messages = array(
            'word_definition_id.required' => 'شناسه معنی لغت اجباری است',
            'image.required' => 'تصویر باید انتخاب شود',
            'image.file' => 'تصویر باید فایل باشد',
            'image.mimes' => 'نوع تصویر باید jpg باشد',
            'image.dimensions' => 'تصویر باید 300 در 450 باشد',
            'image.max' => 'حجم تصویر حداکثر باید 50 کیلو بایت باشد',
        );

        $validator = Validator::make($request->all(), [
            'word_definition_id' => 'required',
            'image' => 'required|file|mimes:jpg|dimensions:min_width=450,min_height=300,max_width=450,max_height=300|max:50'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "عملیات شکست خورد",
            ], 400);
        }

        $definition = WordDefinition::find($request->word_definition_id);
        if (!$definition) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "معنی لغت یافت نشد",
            ], 400);
        }

        if ($definition->files) {
            File::deleteFile($definition->files, WordDefinition::IMAGE_FILE_TYPE);
        }

        File::createFile($request->image, $definition, WordDefinition::IMAGE_FILE_TYPE, true);

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "تصویر با موفقیت اضافه شد"
        ]);
    }

    public function getTypes()
    {
        return response()->json([
            'data' => Word::getWordTypes(),
            'errors' => null,
            'message' => "لیست انواع لغات با موفقیت دریافت شد.",
        ]);
    }
}
