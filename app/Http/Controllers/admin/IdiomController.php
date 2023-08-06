<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Idiom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

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

    public static function getIdiomById(int $id)
    {
        $idiom =  Idiom::where('id', $id)->first();

            $rooms = json_decode($idiom->definition, true);

            $idiom->parse = $rooms;
            return $idiom;
    }
    
    

    public function createIdiom(Request $request)
    {
        $messsages = array(
            'phrase.required' => 'عبارت یا اصطلاح را وارد کنید',
            'base.required' => 'کلمه ی base عبارت یا اصطلاح را وارد کنید',
            'ss.required' => 'لیست معانی همراه با لیست مثال ها را وارد کنید',
            'ss.array' => 'لیست معانی با مثال ها باید آرایه باشد',


        );

        $validator = Validator::make($request->all(), [
            'phrase' => 'required',
            'base' => 'required',
            'ss' => 'required|array',
            'ss.*.s' => 'required',
            'ss.*.es' => 'array',
            'ss.*.es.*.e' => 'required',
            'ss.*.es.*.t' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن اصطلاح یا عبارت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $idiom = new Idiom();
        $idiom->phrase = $request->phrase;
        $idiom->base = $request->base;


        $object =[
            'i' => $request->phrase,
            'ss' => $request->ss
        ];
//        $object->i = $request->phrase;
//        $object->ss = $request->ss;

        $idiom->definition = json_encode($object);


        $idiom->save();

        $arr = [
            'data' => $idiom,
            'errors' => null,
            'message' => "اصطلاح با موفقیت اضافه شد"
        ];

        return response()->json($arr, 200);


    }


    //TODO dont forget add
    public static function getIdiomByIdForUpdate(int $id)
    {
        return  Idiom::where('id', $id)->first();
    }

    public function updateIdiom(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه اصطلاح را وارد کنید',
            'id.numeric' => 'شناسه اصطلاح باید عددی باشد',
            'phrase.required' => 'عبارت یا اصطلاح را وارد کنید',
            'base.required' => 'کلمه ی base عبارت یا اصطلاح را وارد کنید',
            'ss.required' => 'لیست معانی همراه با لیست مثال ها را وارد کنید',
            'ss.array' => 'لیست معانی با مثال ها باید آرایه باشد',


        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'phrase' => 'required',
            'base' => 'required',
            'ss' => 'required|array',
            'ss.*.s' => 'required',
            'ss.*.es' => 'array',
            'ss.*.es.*.e' => 'required',
            'ss.*.es.*.t' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ویرایش اصطلاح یا عبارت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $idiom = self::getIdiomByIdForUpdate($request->id);
        $idiom->phrase = $request->phrase;
        $idiom->base = $request->base;

        $object =[
            'i' => $request->phrase,
            'ss' => $request->ss
        ];
        $idiom->definition = json_encode($object);
        $idiom->save();

        $arr = [
            'data' => $idiom,
            'errors' => null,
            'message' => "اصطلاح با موفقیت ویرایش شد"
        ];

        return response()->json($arr, 200);


    }



}
