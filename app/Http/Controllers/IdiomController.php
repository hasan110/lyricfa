<?php

namespace App\Http\Controllers;

use App\Models\Idiom;
use App\Models\Map;
use App\Models\Word;
use App\Models\WordEnEn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IdiomController extends Controller
{
    public function getWordIdiomsByRate(Request $request)
    {
        $messages = array(
            'word.required' => 'کلمه الزامی است.',
            'phrase.required' => 'عبارت الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required',
            'phrase' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "خطا در اعتبار سنجی رخ داد.",
            ], 400);
        }
        $word = $request->word;
        $phrase = $request->phrase;

        if(!str_contains($phrase , $word))
        {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "کلمه ارسالی درون عبارت وجود ندارد.",
            ], 400);
        }

        $word_base = Map::where('word' , $word)->first();
        if($word_base){
            $word_base = $word_base->ci_base;
        } else {
            $lowercase_word_base = Map::where('word' , strtolower($word))->first();
            if($lowercase_word_base){
                $word_base = $lowercase_word_base->ci_base;
            } else {
                $camelcase_word_base = Map::where('word' , ucfirst(strtolower($word)))->first();
                if($camelcase_word_base){
                    $word_base = $camelcase_word_base->ci_base;
                }
            }
        }

        $separateds = preg_split("/[- ,?;_\"!.}{)(]+/" , $phrase);
        $separated_words = [];
        foreach ($separateds as $separated){
            if(strlen($separated) > 0){
                $separated_words[] = strtolower($separated);
            }
        }

        $word_idioms = Idiom::with('idiom_definitions')->where('phrase_base' , 'regexp' , '\\b'.strtolower($word).'\\b')->get();
        if($word_base){
            $word_base_idioms = Idiom::with('idiom_definitions')->where('phrase_base' , 'regexp' , '\\b'.strtolower($word_base).'\\b')->get();
        }else{
            $word_base_idioms = [];
        }

        $word_index = array_search(strtolower($word) , $separated_words);

        $words_before_main_word = array_slice($separated_words , 0 , $word_index);
        $words_after_main_word = array_slice($separated_words , $word_index + 1 , count($separated_words) - $word_index);

        foreach ($words_before_main_word as $before_word){
            $before_word_base = Map::where('word' , $before_word)->first();
            if($before_word_base){
                if (!in_array($before_word_base->ci_base , $words_before_main_word))
                    $words_before_main_word[] = strtolower($before_word_base->ci_base);
            }
        }
        foreach ($words_after_main_word as $after_word){
            $after_word_base = Map::where('word' , $after_word)->first();
            if($after_word_base){
                if (!in_array($after_word_base->ci_base , $words_after_main_word))
                    $words_after_main_word[] = strtolower($after_word_base->ci_base);
            }
        }

        foreach ($word_idioms as $key => $word_idiom)
        {
            $word_idiom->rate = 0;
            $separated_phrase = preg_split("/[- ,?;_\"!.}{)(]+/" , $word_idiom->phrase_base);
            $phrase_separated_words = [];
            foreach ($separated_phrase as $separated){
                if(strlen($separated) > 0 && !in_array($separated , $phrase_separated_words)){
                    $phrase_separated_words[] = $separated;
                }
            }

            $main_word_index = array_search(strtolower($word) , $phrase_separated_words);
            $words_before = array_slice($phrase_separated_words , 0 , $main_word_index);
            $words_after = array_slice($phrase_separated_words , $main_word_index + 1 , count($phrase_separated_words) - $main_word_index);

            foreach ($words_before as $before_item){
                if(in_array($before_item , $words_before_main_word)){
                    $word_idiom->rate++;
                }
            }
            foreach ($words_after as $after_item){
                if(in_array($after_item , $words_after_main_word)){
                    $word_idiom->rate++;
                }
            }

            if($word_idiom->rate === 0)
            {
                unset($word_idioms[$key]);
            }
        }

        foreach ($word_base_idioms as $key => $word_base_idiom)
        {
            $word_base_idiom->rate = 0;
            $base_separated_phrase = preg_split("/[- ,?;_\"!.}{)(]+/" , $word_base_idiom->phrase_base);
            $base_phrase_separated_words = [];
            foreach ($base_separated_phrase as $base_separated){
                if(strlen($base_separated) > 0 && !in_array($base_separated , $base_phrase_separated_words)){
                    $base_phrase_separated_words[] = $base_separated;
                }
            }

            $base_main_word_index = array_search(strtolower($word_base) , $base_phrase_separated_words);
            $base_words_before = array_slice($base_phrase_separated_words , 0 , $base_main_word_index);
            $base_words_after = array_slice($base_phrase_separated_words , $base_main_word_index + 1 , count($base_phrase_separated_words) - $base_main_word_index);

            foreach ($base_words_before as $base_before_item){
                if(in_array($base_before_item , $words_before_main_word)){
                    $word_base_idiom->rate++;
                }
            }
            foreach ($base_words_after as $base_after_item){
                if(in_array($base_after_item , $words_after_main_word)){
                    $word_base_idiom->rate++;
                }
            }

            if($word_base_idiom->rate === 0)
            {
                unset($word_base_idioms[$key]);
            }
        }

        $all_idioms = $word_idioms->merge($word_base_idioms);

        $all_idioms = $all_idioms->sortBy(fn ($item, $key) => strlen($item['phrase']));
        $all_idioms = $all_idioms->sortByDesc('rate');

        return response()->json([
            'data' => array_values($all_idioms->take(30)->toArray()),
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getWordIdioms(Request $request)
    {
        $messages = array(
            'word.required' => 'کلمه الزامی است.'
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "خطا در اعتبار سنجی رخ داد.",
            ], 400);
        }

        $word = $request->word;
        $lower_word = strtolower($request->word);

        $word_base = Map::where('word' , $word)->orWhere('word' , $lower_word)->first();
        $lower_word_base = null;
        if($word_base){
            $word_base = $word_base->ci_base;
            $lower_word_base = strtolower($word_base);
        }

        $get_word = Word::where('english_word' , $word)->orWhere('english_word' , $lower_word)->orWhere('english_word' , ucfirst($lower_word))->first();
        $get_en_word = WordENEN::where('ci_word' , $word)->orWhere('ci_word' , $lower_word)->orWhere('ci_word' , ucfirst($lower_word))->first();
        if($get_word)
        {
            $word_data = [
                'word' => $word,
                'pronunciation' => $get_word->pronunciation,
                'definitions' => $get_word->word_definitions,
                'english_definitions' => $get_en_word ? $get_en_word->english_word_definitions : null,
                'idioms' => Idiom::with('idiom_definitions')->where('base' , $word)->orWhere('base' , $lower_word)->get()
            ];

        }else{
            $word_data = null;
        }

        $get_base_word = Word::where('english_word' , $word_base)->orWhere('english_word' , $lower_word_base)->first();
        $get_base_en_word = WordENEN::where('ci_word' , $word_base)->orWhere('ci_word' , $lower_word_base)->first();
        if($get_base_word)
        {
            $base_word_data = [
                'word' => $word_base,
                'pronunciation' => $get_base_word->pronunciation,
                'definitions' => $get_base_word->word_definitions,
                'english_definitions' => $get_base_en_word ? $get_base_en_word->english_word_definitions : null,
                'idioms' => Idiom::with('idiom_definitions')->where('base' , $word_base)->orWhere('base' , $lower_word_base)->get(),
            ];

        }else{
            $base_word_data = null;
        }

        return response()->json([
            'data' => [
                'word' => $word_data,
                'base_word' => $base_word_data,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getIdiomData(Request $request)
    {
        $messages = array(
            'idiom.required' => 'اصطلاح الزامی است.'
        );

        $validator = Validator::make($request->all(), [
            'idiom' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "خطا در اعتبار سنجی رخ داد.",
            ], 400);
        }

        $get_idiom = Idiom::with('idiom_definitions')->where('phrase' , $request->idiom)->first();

        if ($get_idiom){
            $response = [
                'data' => $get_idiom,
                'errors' => [],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
        } else {
            $response = [
                'data' => null,
                'errors' => [],
                'message' => "اصطلاح یافت نشد",
            ];
        }

        return response()->json($response);
    }
}
