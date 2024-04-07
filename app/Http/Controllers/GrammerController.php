<?php

namespace App\Http\Controllers;

use App\Models\Grammer;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class GrammerController extends Controller
{
    function grammerList(Request $request)
    {
        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);

        $list = Grammer::paginate(25);
//
//        foreach ($list as $item) {
//
//        }

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
    }

    function getGrammer(Request $request)
    {
        $grammer = Grammer::with('grammer_explanations')->where('id' , $request->grammer_id)->first();

        if (!$grammer) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "اطلاعات گرامر یافت نشد."
            ] , 404);
        }

        return response()->json([
            'data' => $grammer,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
    }

    function findGrammer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phrase' => 'required',
        ], [
            'phrase.required' => 'عبارت الزامی است'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "خطا در اعتبار سنجی رخ داد.",
            ], 422);
        }

        $phrase = $request->phrase;
        $separateds = preg_split("/[- ,?;_!.}{)(]+/" , $phrase);
        $separated_words = [];
        foreach ($separateds as $separated){
            if(strlen($separated) > 0){
                $separated_words[] = $separated;
            }
        }

        $grammers = Grammer::with(['grammer_rules' => function($q){
            $q->with('map_reason')->get();
        }])->get();

        $found_grammers = [];
        foreach ($grammers as $grammer) {

            $grammer_match = true;
            $max_level = 0;
            foreach ($grammer->grammer_rules as $grammer_rule) {
                $rule_level = $grammer_rule->pivot->level;
                if ($rule_level > $max_level) {
                    $max_level = (int)$rule_level;
                }
            }

            $checking_level = 1;
            $checked_rules = [];
            foreach ($grammer->grammer_rules as $grammer_rule) {
                $rule_level = (int)$grammer_rule->pivot->level;
                if($rule_level > $checking_level) {
                    $grammer_match = false;
                    break;
                }
                if ($rule_level !== $checking_level) continue;

                $proccess_method = $grammer_rule->proccess_method;
                $phrase_has_rule = false;
                $checked_rules[] = $grammer_rule;
                if ($proccess_method == 1) {
                    $map_reason = $grammer_rule->map_reason;
                    if (!$map_reason) {
                        continue;
                    }

                    $map = $map_reason->maps()->whereIn('word' , $separated_words)->first();
                    if ($map) {
                        $phrase_has_rule = true;
                    }

                } else if ($proccess_method == 2) {
                    if ($this->searchInText($grammer_rule , $phrase , $separated_words)) {
                        $phrase_has_rule = true;
                    }
                } else if ($proccess_method == 3) {
                    $type_is_exists = false;
                    $check_words = Word::whereIn('english_word' , $separated_words)->get();
                    foreach ($check_words as $word) {
                        $types = explode(',' , $word->word_types);
                        if (in_array($grammer_rule->type , $types)) {
                            $type_is_exists = true;
                        }
                    }
                    if ($type_is_exists) {
                        $phrase_has_rule = true;
                    }
                }

                if ($phrase_has_rule) {
                    $checking_level++;
                }

            }

            if ($grammer_match && $checking_level > $max_level) {
                $found_grammers[] = $grammer;
            }

        }

        $result = [];
        foreach ($found_grammers as $gr) {
            $result[] = [
                'id' => $gr->id,
                'english_name' => $gr->english_name,
                'persian_name' => $gr->persian_name,
                'description' => $gr->description,
                'level' => $gr->level
            ];
        }

        return response()->json([
            'data' => $result,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
    }

    function searchInText($rule , $phrase , $separated_words):bool
    {
        $type = $rule->type;
        $words = $rule->words;
        $separates = preg_split("/[- ,?;_!.}{)(]+/" , $words);
        if ($type == 'search_exact') {
            if ($this->checkIfContains($words , $phrase)) {
                return true;
            }
        }
        if ($type == 'search_order') {
            $operated_phrase = $phrase;
            foreach ($separates as $separated) {
                if ($this->checkIfContains($separated , $operated_phrase)) {
                    $operated_phrase = substr($operated_phrase , strpos($operated_phrase , $separated) + strlen($separated));
                } else {
                    return false;
                }
            }
            return true;
        }
        if ($type == 'search_disorder') {
            $match_items = 0;
            foreach ($separates as $separated) {
                foreach ($separated_words as $separated_word) {
                    if ($separated == $separated_word) {
                        $match_items++;
                    }
                }
            }
            return $match_items === count($separates);
        }
        if ($type == 'search_first_of_word') {
            foreach ($separated_words as $word) {
                if (str_starts_with($word , $words)) {
                    return true;
                }
            }
        }
        if ($type == 'search_end_of_word') {
            foreach ($separated_words as $word) {
                if (str_ends_with($word , $words)) {
                    return true;
                }
            }
        }
        if ($type == 'search_part_of_word') {
            foreach ($separated_words as $word) {
                if (str_contains($word , $words)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function checkIfContains( $needle, $haystack ) {
        return preg_match( '#\b' . preg_quote( $needle, '#' ) . '\b#i', $haystack ) !== 0;
    }
}
