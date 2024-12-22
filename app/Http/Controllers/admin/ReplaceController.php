<?php

namespace App\Http\Controllers\admin;

use App\Models\Film;
use App\Models\Map;
use App\Models\Music;
use App\Models\ReplaceRule;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;

class ReplaceController extends Controller
{
    public function processText(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'type.required' => 'جست و جو در نوع نمی تواند خالی باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "پردازش متن با مشکل اعتبارسنجی مواجه شد",
            ], 422);
        }

        $per_page = 200;
        $page = $request->input('page', 1);

        if ($request->input('type') == 'music') {
            if (!$music = Music::find($request->input('id'))) {
                return response()->json([
                    'data' => null,
                    'errors' => null,
                    'message' => "موزیک یافت نشد",
                ], 400);
            }
            $texts_query = $music->texts()->select(['id', 'text_english', 'text_persian'])->orderBy('start_time');
            $total = $texts_query->count();
            $texts = $texts_query->offset(($page - 1) * $per_page)->limit($per_page)->get();

        } else if ($request->input('type') == 'film') {
            if (!$film = Film::find($request->input('id'))) {
                return response()->json([
                    'data' => null,
                    'errors' => null,
                    'message' => "فیلم یافت نشد",
                ], 400);
            }
            $texts_query = $film->texts()->select(['id', 'text_english', 'text_persian'])->orderBy('start_time');
            $total = $texts_query->count();
            $texts = $texts_query->offset(($page - 1) * $per_page)->limit($per_page)->get();

        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " نوع جست و جو معتبر نیست.",
            ]);
        }

        $replace_rules = ReplaceRule::orderBy('priority')->get();

        foreach ($texts as $text) {
            foreach ($replace_rules as $rule) {
                $this->replaceText($text, $rule);
            }

            $this->findUntranslatedWords($text);
        }

        return response()->json([
            'data' => [
                'data' => $texts,
                'last_page' => ceil($total / $per_page),
                'page' => $page,
                'per_page' => $per_page,
            ],
            'errors' => null,
            'message' => "اطلاعات با موفقیت دریافت شد.",
        ]);
    }

    private function replaceText(&$text, ReplaceRule $rule): void
    {
        if ($rule->apply_on === 'persian_text' || $rule->apply_on === 'all') {
            if (isset($text->changed_text_persian)) {
                $on_change_text = trim($text->changed_text_persian);
            } else {
                $on_change_text = trim($text->text_persian);
            }
        } else if ($rule->apply_on === 'english_text' || $rule->apply_on === 'all') {
            if (isset($text->changed_text_english)) {
                $on_change_text = trim($text->changed_text_english);
            } else {
                $on_change_text = trim($text->text_english);
            }
        } else {
            return;
        }

        $final_text = $on_change_text;
        $striped_tags_text = strip_tags($on_change_text);
        if ($rule->similar) {
            if(preg_match("/$rule->find_phrase(\s|$|\.|\,)/", $striped_tags_text)) {
                $final_text = str_replace($rule->find_phrase, '<span class="yellow-mark">' . $rule->replace_phrase . '</span>', $striped_tags_text);
            }
        } else {

            if (strpos($striped_tags_text , $rule->find_phrase) !== false) {
                if ($rule->last_character) {
                    if (str_ends_with($striped_tags_text, $rule->find_phrase)) {
                        $separated = explode(' ' , $striped_tags_text);
                        $last_word = end($separated);
                        array_pop($separated);
                        $separated[] = str_replace($rule->find_phrase, '<span class="yellow-mark">' . $rule->replace_phrase . '</span>', $last_word);
                        $final_text = implode(' ', $separated);
                    }
                } else {
                    $final_text = str_replace($rule->find_phrase, '<span class="yellow-mark">' . $rule->replace_phrase . '</span>', $striped_tags_text);
                }
            }
        }

        if ($rule->apply_on === 'persian_text' || $rule->apply_on === 'all') {
            $text->changed_text_persian = $final_text;
            $text->changed_text_persian_raw = strip_tags($final_text);
            $text->changed_text_persian_raw_safe = strip_tags($final_text);
            $text->persian_changed = $text->persian_changed || ($final_text !== $on_change_text);
        } else if ($rule->apply_on === 'english_text' || $rule->apply_on === 'all') {
            $text->changed_text_english = $final_text;
            $text->changed_text_english_raw = strip_tags($final_text);
            $text->changed_text_english_raw_safe = strip_tags($final_text);
            $text->english_changed = $text->english_changed || ($final_text !== $on_change_text);
        }

        $text->enable_edit = false;
    }
    private function findUntranslatedWords(&$text): void
    {
        $content = explode("\n", $text->text_english);
        $text->untranslated_words_changed = false;
        $text->untranslated_words_text = null;
        $final_text = [];
        foreach ($content as $phrase) {

            $temp = preg_split("/[, ?;_!.}\n{)(\r]+/" , $phrase);
            $found_words = [];
            $separateds = [];
            foreach ($temp as $separated) {
                if ($separated && $separated !== '-') {
                    $separateds[] = $separated;
                }
            }
            foreach ($separateds as $key => $separated) {
                $separated = trim($separated , '"');
                $raw_word = $separated;
                if ($raw_word === "-") {
                    continue;
                }
                if (str_ends_with($separated , "'s")) {
                    $separated = str_replace("'s" , "" , $separated);
                }
                if ($key === 0) {
                    $word_capital_check = Word::where('english_word', ucwords($separated))->first();
                    $word_letter_check = Word::where('english_word', strtolower($separated))->first();
                    if (!$word_capital_check && !$word_letter_check) {
                        $map_capital_check = Map::where('word', ucwords($separated))->first();
                        $map_letter_check = Map::where('word', strtolower($separated))->first();
                        if (!$map_capital_check && !$map_letter_check) {
                            if (!in_array($raw_word, $found_words)) {
                                $found_words[$raw_word] = '<a href="/words/create?word='.$raw_word.'" target="_blank" class="red-mark new-word">'.$raw_word.'</a>';
                            }
                        }
                    }
                } else {
                    $word_check = Word::where('english_word', $separated)->first();
                    if (!$word_check) {
                        $map_check = Map::where('word', $separated)->first();
                        if (!$map_check) {
                            if (!in_array($raw_word, $found_words)) {
                                $found_words[$raw_word] = '<a href="/words/create?word='.$raw_word.'" target="_blank" class="red-mark new-word">'.$raw_word.'</a>';
                            }
                        }
                    }
                }
            }

            if (count($found_words) !== 0) {
                $text->untranslated_words_changed = true;
                $final_text[] = str_replace(
                    array_keys($found_words),
                    array_values($found_words),
                    $phrase
                );
            } else {
                $final_text[] = $phrase;
            }

        }
        $text->untranslated_words_text = implode("\n" , $final_text);
        $text->raw_untranslated_words_text = $text->text_english;
    }

    public function apply(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه الزامی است',
            'type.required' => 'نوع متن نمی تواند خالی باشد',
            'apply_on.required' => 'نوع اعمال را انتخاب کنید',
            'texts.required' => 'لیست متن باید آرایه باشد.',
            'texts.array' => 'لیست متن باید آرایه باشد.',
            'texts.*.id' => 'شناسه متن الزامی است',
            'texts.*.text' => 'متن نمیتواند خالی باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'apply_on' => 'required',
            'texts' => 'required|array',
            'texts.*.id' => 'required',
            'texts.*.text' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "اعمال تغییرات با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        try {
            if ($request->type === 'music') {
                $model = Music::find($request->input('id'));
            } else if ($request->type === 'film') {
                $model = Film::find($request->input('id'));
            } else {
                throw new Exception(" نوع متن معتبر نیست.");
            }

            if (!$model) {
                throw new Exception('مدل یافت نشد');
            }

            foreach ($request->input('texts') as $text) {
                $model_text = $model->texts()->where('id',$text['id'])->first();
                if (!$model_text) {
                    continue;
                }
                if ($request->apply_on === 'english') {
                    $model_text->text_english = $text['text'];
                }
                if ($request->apply_on === 'persian') {
                    $model_text->text_persian = $text['text'];
                }
                $model_text->update();
            }
        } catch (Exception $exception) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => $exception->getMessage(),
            ], 400);
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => 'عملیات با موفقیت انجام شد.',
        ]);
    }

    public function ruleList(Request $request)
    {
        $list = ReplaceRule::latest();

        if ($request->search_key) {
            $list = $list->where('find_phrase', 'LIKE', "%{$request->search_key}%")->
            orWhere('replace_phrase', 'LIKE', "%{$request->search_key}%");
        }
        if ($request->sort_by) {
            switch ($request->sort_by) {
                case 'asc':
                default:
                    $list = $list->orderBy('id', 'asc');
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

    public function createRule(Request $request)
    {
        $messages = array(
            'find_phrase.required' => 'عبارت مورد جست و جو نمی تواند خالی باشد',
            // 'replace_phrase.required' => 'عبارت جایگزین نمی تواند خالی باشد',
            'apply_on.required' => 'این که این قانون چطور اعمال شود را انتخاب کنید',
            'rules.array' => 'لیست اعمال بعد از باید آرایه باشد.',
        );

        $validator = Validator::make($request->all(), [
            'find_phrase' => 'required',
            'replace_phrase' => 'required',
            'apply_on' => 'required',
            'rules' => 'array',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن قانون با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $replace_rule = new ReplaceRule();
        $replace_rule->find_phrase = $request->find_phrase;
        $replace_rule->replace_phrase = $request->replace_phrase ?? null;
        $replace_rule->apply_on = $request->apply_on;
        $replace_rule->last_character = intval($request->last_character);
        $replace_rule->similar = intval($request->similar);

        if ($request->rules) {
            $max_rule_priority = ReplaceRule::whereIn('id' , $request->rules)->max('priority');
            $replace_rule->priority = $max_rule_priority + 1;
        }

        $replace_rule->save();

        return response()->json([
            'data' => $replace_rule,
            'errors' => null,
            'message' => "قانون با موفقیت اضافه شد"
        ]);
    }

    public function removeRule(Request $request)
    {
        $rule = ReplaceRule::find($request->id);
        if(!$rule){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " لغت یافت نشد.",
            ], 404);
        }

        $rule->delete();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "اطلاعات این قانون با موفقیت حذف شد.",
        ]);
    }
}
