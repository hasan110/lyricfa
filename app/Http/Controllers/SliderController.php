<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function getSlidersForShow(Request $request){

        $sliders = Slider::where('show_it', '=', 1)->orderBy("id")->get();

        $arr = [
            'data' => $sliders,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($arr, 200);
    }

}
