<?php

namespace App\Http\Controllers\admin;

use App\Models\GrammerExample;
use App\Models\GrammerExplanation;
use App\Models\GrammerRule;
use App\Models\Grammer;
use App\Models\GrammerSection;
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
        $list = GrammerRule::with('map_reason');

        if ($request->search_key) {
            $list = $list->where('type', 'LIKE', "%{$request->search_key}%")
                ->orWhere('words', 'LIKE', "%{$request->search_key}%")
                ->orWhere('id', $request->search_key);
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
            'grammer_sections.*.title.required' => 'عنوان بخش نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.type.filled' => 'نوع توضیح گرامر نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.content.filled' => 'متن توضیح گرامر نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.english_content.filled' => 'متن انگلیسی مثال گرامر نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.persian_content.filled' => 'متن فارسی مثال گرامر نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'english_name' => 'required',
            'persian_name' => 'required',
            'description' => 'required',
            'level' => 'required',
            'rules' => 'required|array',
            'prerequisite' => 'array',
            'grammer_sections.*.title' => 'required',
            'grammer_sections.*.grammer_explanations.*.type' => 'filled',
            'grammer_sections.*.grammer_explanations.*.content' => 'filled',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.english_content' => 'filled',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.persian_content' => 'filled',
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
            $rules = [];
            foreach ($request->rules as $rule) {
                $rules[$rule['id']] = ['level' => $rule['level']];
            }
            $grammer->grammer_rules()->sync($rules);
        }
        if($request->has('prerequisite')) {
            $grammer->grammer_prerequisites()->sync($request->prerequisite);
        }

        foreach ($request->grammer_sections as $section)
        {
            $grammer_section = new GrammerSection();
            $grammer_section->grammer_id = $grammer->id;
            $grammer_section->title = $section['title'];
            $grammer_section->save();
            foreach ($section['grammer_explanations'] as $explanation)
            {
                $grammer_explanation = new GrammerExplanation();
                $grammer_explanation->grammer_id = $grammer->id;
                $grammer_explanation->grammer_section_id = $grammer_section->id;
                $grammer_explanation->type = $explanation['type'];
                $grammer_explanation->title = $explanation['title'];
                $grammer_explanation->content = $explanation['content'];
                $grammer_explanation->save();

                foreach ($explanation['grammer_examples'] as $example)
                {
                    $grammer_example = new GrammerExample();
                    $grammer_example->grammer_explanation_id = $grammer_explanation->id;
                    $grammer_example->english_content = $example['english_content'];
                    $grammer_example->persian_content = $example['persian_content'];
                    $grammer_example->save();
                }
            }
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
        $validator = Validator::make($request->all(), [
            'proccess_method' => 'required',
        ], [
            'proccess_method.required' => 'نوع جستجو نمی تواند خالی باشد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن قانون گرامر با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $proccess_method = $request->input('proccess_method');
        $map_reason = null;
        $type = null;
        $words = null;
        switch ($proccess_method) {
            case 1:
                $map_reason = $request->map_reason_id;
            break;
            case 2:
                $type = $request->type;
                $words = $request->words;
            break;
            case 3:
                $type = $request->word_type;
            break;
            default:
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "نوع جستجو نامعتبر می باشد.",
            ], 400);
        }


        $grammer_rule = new GrammerRule();
        $grammer_rule->proccess_method = $proccess_method;
        $grammer_rule->map_reason_id = $map_reason;
        $grammer_rule->type = $type;
        $grammer_rule->words = $words;
        $grammer_rule->save();

        $arr = [
            'data' => $grammer_rule,
            'errors' => null,
            'message' => "قانون گرامر با موفقیت اضافه شد"
        ];

        return response()->json($arr);
    }

    public function updateGrammerRule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'proccess_method' => 'required',
        ], [
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'proccess_method.required' => 'نوع جستجو نمی تواند خالی باشد',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش قانون گرامر با مشکل اعتبارسنجی مواجه شد",
            ], 400);
        }

        $proccess_method = $request->input('proccess_method');
        $map_reason = null;
        $type = null;
        $words = null;
        switch ($proccess_method) {
            case 1:
                $map_reason = $request->map_reason_id;
            break;
            case 2:
                $type = $request->type;
                $words = $request->words;
            break;
            case 3:
                $type = $request->word_type;
            break;
            default:
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "نوع جستجو نامعتبر می باشد.",
            ], 400);
        }


        $grammer_rule = GrammerRule::find($request->id);
        $grammer_rule->proccess_method = $proccess_method;
        $grammer_rule->map_reason_id = $map_reason;
        $grammer_rule->type = $type;
        $grammer_rule->words = $words;
        $grammer_rule->save();

        $arr = [
            'data' => $grammer_rule,
            'errors' => null,
            'message' => "قانون گرامر با موفقیت ویرایش شد"
        ];

        return response()->json($arr);
    }

    public function removeGrammerRule(Request $request)
    {
        $item = GrammerRule::find($request->id);
        if(!$item){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " قانون یافت نشد.",
            ], 404);
        }

        $item->delete();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => " تمامی اطلاعات این قانون با موفقیت حذف شد.",
        ];
        return response()->json($arr);
    }

    public function updateGrammer(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نمی تواند خالی باشد',
            'english_name.required' => 'نام انگلیسی نمی تواند خالی باشد',
            'persian_name.required' => 'نام فارسی نمی تواند خالی باشد',
            'description.required' => 'توضیحات نمی تواند خالی باشد',
            'level.required' => 'انتخاب سطح اجباری است.',
            'rules.required' => 'انتخاب قوانین اجباری است.',
            'rules.array' => 'قوانین باید آرایه باشد.',
            'prerequisite.array' => 'گرامر های پیش نیاز باید آرایه باشد.',
            'grammer_sections.*.title.required' => 'عنوان بخش نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.type.filled' => 'نوع توضیح گرامر نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.content.filled' => 'متن توضیح گرامر نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.english_content.filled' => 'متن انگلیسی مثال گرامر نمی تواند خالی باشد.',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.persian_content.filled' => 'متن فارسی مثال گرامر نمی تواند خالی باشد.',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'english_name' => 'required',
            'persian_name' => 'required',
            'description' => 'required',
            'level' => 'required',
            'rules' => 'required|array',
            'prerequisite' => 'array',
            'grammer_sections.*.title' => 'required',
            'grammer_sections.*.grammer_explanations.*.type' => 'filled',
            'grammer_sections.*.grammer_explanations.*.content' => 'filled',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.english_content' => 'filled',
            'grammer_sections.*.grammer_explanations.*.grammer_examples.*.persian_content' => 'filled',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش گرامر با مشکل اعتبارسنجی مواجه شد",
            ];
            return response()->json($arr, 400);
        }

        $grammer = Grammer::with('grammer_explanations')->find($request->id);
        $grammer->english_name = $request->english_name;
        $grammer->persian_name = $request->persian_name;
        $grammer->description = $request->description;
        $grammer->level = $request->level;
        $grammer->save();

        if($request->has('rules')) {
            $rules = [];
            foreach ($request->rules as $rule) {
                $rules[$rule['id']] = ['level' => $rule['level']];
            }
            $grammer->grammer_rules()->sync($rules);
        }
        if($request->has('prerequisite')) {
            $grammer->grammer_prerequisites()->sync($request->prerequisite);
        }

        // delete all explanation relations and ...
        foreach ($grammer->grammer_sections as $section_item){
            foreach ($section_item->grammer_explanations as $item){
                foreach ($item->grammer_examples as $example_item){
                    $example_item->delete();
                }
                $item->delete();
            }
            $section_item->delete();
        }

        foreach ($request->grammer_sections as $section)
        {
            $grammer_section = new GrammerSection();
            $grammer_section->grammer_id = $grammer->id;
            $grammer_section->title = $section['title'];
            $grammer_section->save();
            foreach ($section['grammer_explanations'] as $explanation)
            {
                $grammer_explanation = new GrammerExplanation();
                $grammer_explanation->grammer_id = $grammer->id;
                $grammer_explanation->grammer_section_id = $grammer_section->id;
                $grammer_explanation->type = $explanation['type'];
                $grammer_explanation->title = $explanation['title'];
                $grammer_explanation->content = $explanation['content'];
                $grammer_explanation->save();

                foreach ($explanation['grammer_examples'] as $example)
                {
                    $grammer_example = new GrammerExample();
                    $grammer_example->grammer_explanation_id = $grammer_explanation->id;
                    $grammer_example->english_content = $example['english_content'];
                    $grammer_example->persian_content = $example['persian_content'];
                    $grammer_example->save();
                }
            }
        }

        $arr = [
            'data' => $grammer,
            'errors' => null,
            'message' => "گرامر با موفقیت ویرایش شد"
        ];

        return response()->json($arr);
    }

    public function getGrammer(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه گرامر نمی تواند خالی باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت اطلاعات گرامر شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $get = Grammer::with('grammer_sections')->find($request->id);
        if(!$get){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " گرامر یافت نشد.",
            ], 404);
        }
        $get['prerequisite'] = $get->grammer_prerequisites()->pluck('id')->toArray();
        $rules = [];
        foreach ($get->grammer_rules()->get() as $item) {
            $rules[] = [
                'id' => intval($item->pivot->grammer_rule_id),
                'proccess_method' => intval($item->proccess_method),
                'type' => $item->type,
                'words' => $item->words,
                'level' => intval($item->pivot->level),
                'map_reason' => $item->map_reason
            ];
        }
        $get['rules'] = $rules;

        $arr = [
            'data' => $get,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ];
        return response()->json($arr);
    }

    public function removeGrammer(Request $request)
    {
        $grammer = Grammer::find($request->id);
        if(!$grammer){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " گرامر یافت نشد.",
            ], 404);
        }

        $grammer->delete();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => " تمامی اطلاعات این گرامر با موفقیت حذف شد.",
        ];
        return response()->json($arr);
    }
}
