<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TextIdioms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TextIdiomsController extends Controller
{
    public function getTextIdioms(Request $request)
    {
        $messages = array(
            'id_text.required' => 'id_text نمی تواند خالی باشد',
            'id_text.numeric' => 'id_text باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id_text' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " شکست در وارد کردن اطلاعات",
            ], 400);
        }

        $idioms = TextIdioms::orderBy('id', 'ASC')->
        where('id_text', "$request->id_text")->get();

        $idiomsDetails = array();
        foreach ($idioms as $item) {
            $item->details = IdiomController::getIdiomById($item->id_idiom);
            $idiomsDetails[] = $item;
        }

        return response()->json([
            'data' => $idiomsDetails,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function createTextIdiom(Request $request)
    {
        $messages = array(
            'id_text.required' => 'id_text نمی تواند خالی باشد',
            'id_text.numeric' => 'id_text باید عدد باشد',
            'id_idiom.required' => 'id_idiom نمی تواند خالی باشد',
            'id_idiom.numeric' => 'id_idiom باید عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_text' => 'required|numeric',
            'id_idiom' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " شکست در وارد کردن اطلاعات",
            ], 400);
        }

        $idiomText = new TextIdioms();
        $idiomText->id_text = $request->id_text;
        $idiomText->id_idiom = $request->id_idiom;
        if (isset($idiomText->description))
            $idiomText->description = $request->description;
        $idiomText->save();

        return response()->json([
            'data' => $idiomText,
            'errors' => null,
            'message' => "اصطلاح با موفقیت به متن اضافه شد"
        ]);
    }

    public function deleteTextIdiom(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه کامنت الزامی است',
            'id.numeric' => 'شناسه کامنت باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  حذف کامنت  شکست خورد",
            ], 400);
        }

        $name = $this->idiomTextIsExist($request);

        if (!isset($name)) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "چنین اصطلاحی وجود ندارد جهت حذف",
            ], 400);
        }

        $idiom = $name;
        $idiom->id = (int)$name->id;
        $idiom->delete();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "حذف اصطلاح متن موفقیت آمیز بود",
        ]);
    }

    public function idiomTextIsExist(Request $request)
    {
        return TextIdioms::where('id', $request->id)->first();
    }
}
