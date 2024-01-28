<?php

namespace App\Http\Controllers\admin;

use App\Models\GrammerRule;
use App\Models\Word;
use App\Models\Grammer;
use App\Models\WordDefinition;
use App\Models\WordDefinitionExample;
use App\Models\WordEnEn;
use App\Models\WordEnEnDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class GrammerController extends Controller
{
    public function GrammersList(Request $request)
    {
        $list = Grammer::query();

        if ($request->search_key) {
            $list = $list->where('english_name', 'LIKE', "%{$request->search_key}%")
            ->orWhere('persian_name', 'LIKE', "%{$request->search_key}%");
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

    public function GrammerRulesList(Request $request)
    {
        $list = GrammerRule::query();

        if ($request->search_key) {
            $list = $list->where('type', 'LIKE', "%{$request->search_key}%")
                ->orWhere('words', 'LIKE', "%{$request->search_key}%");
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

    public function createGrammer(Request $request)
    {
        $messages = array(
            'english_name.required' => 'نام انگلیسی نمی تواند خالی باشد',
            'persian_name.required' => 'نام فارسی نمی تواند خالی باشد',
            'description.required' => 'توضیحات نمی تواند خالی باشد',
            'level.required' => 'انتخاب سطح اجباری است.',
            'rules.required' => 'انتخاب قوانین اجباری است.',
            'rules.array' => 'قوانین باید آرایه باشد.',
            'prerequisite.array' => 'گرامر های پیش نیاز باید آرایه باشد.',
        );

        $validator = Validator::make($request->all(), [
            'english_name' => 'required',
            'persian_name' => 'required',
            'description' => 'required',
            'level' => 'required',
            'rules' => 'required|array',
            'prerequisite' => 'array',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن گرامر با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
        }

        $grammer = new Grammer();
        $grammer->english_name = $request->english_name;
        $grammer->persian_name = $request->persian_name;
        $grammer->description = $request->description;
        $grammer->level = $request->level;
        $grammer->save();

        if($request->has('rules')) {
            $grammer->grammer_rules()->sync($request->rules);
        }
        if($request->has('prerequisite')) {
            $grammer->grammer_prerequisites()->sync($request->prerequisite);
        }

        $arr = [
            'data' => $grammer,
            'errors' => null,
            'message' => "گرامر با موفقیت اضافه شد"
        ];

        return response()->json($arr);
    }

    public function createGrammerRule(Request $request)
    {
        $messages = array(
            'type.required' => 'نوع قانون نمی تواند خالی باشد',
            'words.required' => 'فیلد لغات نمی تواند خالی باشد',
        );

        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'words' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن قانون گرامر با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
        }

        $grammer_rule = new GrammerRule();
        $grammer_rule->type = $request->type;
        $grammer_rule->words = $request->words;
        $grammer_rule->save();

        $arr = [
            'data' => $grammer_rule,
            'errors' => null,
            'message' => "قانون گرامر با موفقیت اضافه شد"
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
