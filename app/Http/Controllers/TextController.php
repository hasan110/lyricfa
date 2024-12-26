<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\GrammerSection;
use App\Models\IdiomDefinition;
use App\Models\Music;
use App\Models\WordDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TextController extends Controller
{
    public function getTextList(Request $request)
    {
        if ((new UserHelper())->isUserSubscriptionValid($request->header("ApiToken"))) {

            $music = Music::where('id', $request->id_music)->first();
            if (!$music) {
                return response()->json([
                    'data' => null,
                    'errors' => [],
                    'message' => "آهنگ یافت نشد",
                ], 400);
            }

            $texts = $music->texts()->orderBy("start_time")->get();

            return response()->json([
                'data' => $texts,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);

        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'status' => 1000,
                'message' => "اشتراک شما به پایان رسیده است لطفا اشتراک خود را تمدید کنید",
            ], 400);
        }
    }

    public function getTextJoins(Request $request)
    {
        $messages = array(
            'joinable_id.required' => 'شناسه الزامی است',
            'joinable_type.required' => 'نوع الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'joinable_id' => 'required',
            'joinable_type' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت لیست با مشکل مواجه شد"
            ], 400);
        }

        $joinable_type = $request->input("joinable_type");
        $joinable_id = $request->input("joinable_id");
        $joinable = null;
        if ($joinable_type === "word_definition") {
            $joinable = WordDefinition::find($joinable_id);
        } else if ($joinable_type === "idiom_definition") {
            $joinable = IdiomDefinition::find($joinable_id);
        } else if ($joinable_type === "grammer_section") {
            $joinable = GrammerSection::find($joinable_id);
        }

        if (!$joinable) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "نوع یا شناسه نامعتبر می باشد",
            ], 400);
        }

        $joins = $joinable->text_joins()->with(['text' => function ($q) {
            $q->with('textable');
        }])->take(50)->get();

        return response()->json([
            'data' => $joins,
            'errors' => [],
            'message' => "لیست با موفقیت دریافت شد",
        ]);
    }
}
