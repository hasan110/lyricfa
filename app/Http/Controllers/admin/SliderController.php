<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Singer;
use App\Models\Slider;
use App\Models\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function slidersList(Request $request)
    {
        $sliders = Slider::orderBy("id")->paginate(25);
        return response()->json([
            'data' => $sliders,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function sliderUpdate(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id_singer_music_album.required' => 'id_singer_music_album نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
            'id_singer_music_album.numeric' => 'id_singer_music_album باید فقط شامل عدد باشد',
            'type.required' => 'type نمی تواند خالی باشد',
            'type.numeric' => 'type باید فقط شامل عدد باشد',
            'show_it.required' => 'show_it نمی تواند خالی باشد',
            'banner.file' => 'نوع عکس باید فایل باشد',
            'banner.mimes' => 'نوع فایل باید jpg باشد',
            'banner.dimensions' => 'عکس باید 1024 در 575 باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'id_singer_music_album' => 'required|numeric',
            'type' => 'required|numeric',
            'show_it' => 'required',
            'banner' => 'file|mimes:jpg|dimensions:min_width=1024,min_height=575,max_width=1024,max_height=575'

        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ویرایش اسلایدر شکست خورد",
            ], 400);
        }

        $slider = $this->getSliderById($request->id);
        if (!$slider) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این خواننده وجود ندارد برای به روز رسانی"
            ], 400);
        }
        $slider->id_singer_music_album = $request->id_singer_music_album;
        $slider->type = $request->type;
        $slider->title = $request->title;
        $slider->description = $request->description;
        $slider->show_it = $request->show_it ? 1 : 0;
        $slider->banner = "sliders/" . $request->id . '.jpg';

        $slider->save();

        if ($request->hasFile('banner')) {
            File::createFile($request->banner, $slider, Slider::BANNER_FILE_TYPE);
        }

        return response()->json([
            'data' => $slider,
            'errors' => null,
            'message' => "اسلایدر موفقیت به روز رسانی شد"
        ]);
    }


    public function sliderCreate(Request $request)
    {
        $messages = array(

            'id_singer_music_album.required' => 'id_singer_music_album نمی تواند خالی باشد',
            'id_singer_music_album.numeric' => 'id_singer_music_album باید فقط شامل عدد باشد',
            'type.required' => 'type نمی تواند خالی باشد',
            'show_it.required' => 'show_it نمی تواند خالی باشد',
            'show_it.numeric' => 'show_it باید فقط شامل عدد باشد',
            'banner.required' => 'عکس اسلایدر اجباری است',
            'banner.file' => 'نوع عکس باید فایل باشد',
            'banner.mimes' => 'نوع فایل باید jpg باشد',
            'banner.dimensions' => 'عکس باید 1024 در 575 باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_singer_music_album' => 'required|numeric',
            'type' => 'required|numeric',
            'show_it' => 'required',
            'banner' => 'required|file|mimes:jpg|dimensions:min_width=1024,min_height=575,max_width=1024,max_height=575'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ایجاد اسلایدر شکست خورد",
            ], 400);
        }

        $slider = new Slider();
        $slider->id_singer_music_album = $request->id_singer_music_album;
        $slider->type = $request->type;
        $slider->title = $request->title;
        $slider->description = $request->description;
        $slider->show_it = $request->show_it ? 1 : 0;

        $slider->save();

        $slider->banner = "sliders/" . $slider->id . '.jpg';
        $slider->save();
        if ($request->hasFile('banner')) {
            File::createFile($request->banner, $slider, Slider::BANNER_FILE_TYPE);
        }

        return response()->json([
            'data' => $slider,
            'errors' => null,
            'message' => "اسلایدر موفقیت به روز رسانی شد"
        ]);
    }


    public static function getSlider(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " گرفتن اطلاعات اسلایدر شکست خورد",
            ], 400);
        }

        $get_slider= Slider::where('id', $request->id)->first();
        if (!$get_slider) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " گرفتن اطلاعات اسلایدر شکست خورد",
            ], 400);
        }

        return response()->json([
            'data' => $get_slider,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ]);
    }

    public static function getSliderById($id)
    {

        $slider = Slider::where('id', $id)->first();

        return $slider;

    }


    public function removeSlider(Request $request)
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
                'message' => "  حذف کامنت شکست خورد",
            ], 400);
        }

        $slider = $this->getSliderById($request->id);
        if (isset($slider)) {
            $slider->delete();
            $this->deleteFile('sliders/' . $slider->id . '.jpg');
        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این کامنت وجود ندارد",
            ], 400);
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "حذف کامنت موفقیت آمیز بود",
        ]);
    }


}
