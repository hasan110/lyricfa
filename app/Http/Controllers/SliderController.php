<?php

namespace App\Http\Controllers;

use App\Models\Slider;

class SliderController extends Controller
{
    public function getSlidersForShow(){

        $sliders = Slider::where('show_it', '=', 1)->orderBy("id")->get();

        return response()->json([
            'data' => $sliders,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

}
