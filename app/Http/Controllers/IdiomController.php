<?php

namespace App\Http\Controllers;

use App\Models\Idiom;
use App\Models\Map;
use App\Models\Word;
use App\Models\WordEnEn;
use App\Models\WordEnEnDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IdiomController extends Controller
{
    public function getIdiomsWord(Request $request)
    {
        $word = $request->word;
        $get_idioms = Idiom::where('base', $word)->get();

        if (!$get_idioms) {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "هیچ کلمه ای پیدا نشد",
            ];
            return response()->json($arr, 200);
        } else {
            $arr = [
                'data' => $get_idioms,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($arr, 200);
        }

    }

    public static function getIdiomById(int $id)
    {
        $idiom =  Idiom::where('id', $id)->first();

        $rooms = json_decode($idiom->definition, true);

        $idiom->parse = $rooms;
        return $idiom;
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

        $response = [
            'data' => $idioms,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);

    }

    public function getWordIdiomsByRate(Request $request)
    {
        $messsages = array(
            'word.required' => 'کلمه الزامی است.',
            'phrase.required' => 'عبارت الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required',
            'phrase' => 'required',
        ], $messsages);

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
        }

        $seprateds = preg_split("/[- ,?;_!.}{)(]+/" , $phrase);
        $seprated_words = [];
        foreach ($seprateds as $seprated){
            if(strlen($seprated) > 0 && !in_array($seprated , $seprated_words)){
                $seprated_words[] = $seprated;
            }
        }

        $word_idioms = Idiom::with('idiom_definitions')->where('phrase' , 'regexp' , '\\b'.$word.'\\b')->get();
        if($word_base){
            $word_base_idioms = Idiom::with('idiom_definitions')->where('phrase' , 'regexp' , '\\b'.$word_base.'\\b')->get();
        }else{
            $word_base_idioms = [];
        }

        // $all_idioms = $word_idioms->merge($word_base_idioms);
        $word_index = array_search($word , $seprated_words);

        $words_before_main_word = array_slice($seprated_words , 0 , $word_index);
        $words_after_main_word = array_slice($seprated_words , $word_index + 1 , count($seprated_words) - $word_index);

        foreach ($words_before_main_word as $before_word){
            $before_word_base = Map::where('word' , $before_word)->first();
            if($before_word_base){
                if (!in_array($before_word_base->ci_base , $words_before_main_word))
                    $words_before_main_word[] = $before_word_base->ci_base;
            }
        }
        foreach ($words_after_main_word as $after_word){
            $after_word_base = Map::where('word' , $after_word)->first();
            if($after_word_base){
                if (!in_array($after_word_base->ci_base , $words_after_main_word))
                    $words_after_main_word[] = $after_word_base->ci_base;
            }
        }

        foreach ($word_idioms as $key => $word_idiom)
        {
            $word_idiom->rate = 0;
            $seprated_phrase = preg_split("/[- ,?;_!.}{)(]+/" , $word_idiom->phrase);
            $phrase_seprated_words = [];
            foreach ($seprated_phrase as $seprated){
                if(strlen($seprated) > 0 && !in_array($seprated , $phrase_seprated_words)){
                    $phrase_seprated_words[] = $seprated;
                }
            }

            $main_word_index = array_search($word , $phrase_seprated_words);
            $words_before = array_slice($phrase_seprated_words , 0 , $main_word_index);
            $words_after = array_slice($phrase_seprated_words , $main_word_index + 1 , count($phrase_seprated_words) - $main_word_index);

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
            $base_seprated_phrase = preg_split("/[- ,?;_!.}{)(]+/" , $word_base_idiom->phrase);
            $base_phrase_seprated_words = [];
            foreach ($base_seprated_phrase as $base_seprated){
                if(strlen($base_seprated) > 0 && !in_array($base_seprated , $base_phrase_seprated_words)){
                    $base_phrase_seprated_words[] = $base_seprated;
                }
            }

            $base_main_word_index = array_search($word_base , $base_phrase_seprated_words);
            $base_words_before = array_slice($base_phrase_seprated_words , 0 , $base_main_word_index);
            $base_words_after = array_slice($base_phrase_seprated_words , $base_main_word_index + 1 , count($base_phrase_seprated_words) - $base_main_word_index);

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

        $response = [
            'data' => array_values($all_idioms->toArray()),
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];

        return response()->json($response, 200);
    }

    public function getWordIdioms(Request $request)
    {
        $messsages = array(
            'word.required' => 'کلمه الزامی است.'
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required'
        ], $messsages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "خطا در اعتبار سنجی رخ داد.",
            ], 400);
        }
        $word = $request->word;
        $lower_word = strtolower($request->word);

        $word_base = Map::where('word' , $word)->first();
        $lower_word_base = null;
        if($word_base){
            $word_base = $word_base->ci_base;
            $lower_word_base = strtolower($word_base);
        }

        $get_word = Word::where('english_word' , $word)->orWhere('english_word' , $lower_word)->first();
        $get_en_word = WordENEN::where('ci_word' , $word)->orWhere('ci_word' , $lower_word)->first();
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


        $response = [
            'data' => [
                'word' => $word_data,
                'base_word' => $base_word_data,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];

        return response()->json($response, 200);
    }

    public function getIdiomData(Request $request)
    {
        $messsages = array(
            'idiom.required' => 'اصطلاح الزامی است.'
        );

        $validator = Validator::make($request->all(), [
            'idiom' => 'required'
        ], $messsages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "خطا در اعتبار سنجی رخ داد.",
            ], 400);
        }
        $idiom = $request->idiom;

        $get_idiom = Idiom::with('idiom_definitions')->where('phrase' , $idiom)->first();
        if($get_idiom){
            $response = [
                'data' => $get_idiom,
                'errors' => [],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
        }else{
            $response = [
                'data' => null,
                'errors' => [],
                'message' => "اصطلاح یافت نشد",
            ];
            return response()->json($response, 200);
        }
    }
}
