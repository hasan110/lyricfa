<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function slidersList()
    {
        $sliders = Slider::orderBy("id")->paginate(25);
        return response()->json([
            'data' => $sliders,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function sliderCreate(Request $request)
    {
        $messages = array(
            'action.required' => 'نوع اسلایدر نمی تواند خالی باشد',
            'action.in' => 'نوع اسلایدر باید یکی از موارد: no-action, internal-link, external-link باشد',
            'title.required' => 'عنوان نمی تواند خالی باشد',
            'banner.required' => 'عکس اسلایدر اجباری است',
            'banner.file' => 'نوع عکس باید فایل باشد',
            'banner.mimes' => 'نوع فایل باید jpg باشد',
            'banner.dimensions' => 'عکس باید 1024 در 575 باشد',
        );

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:no-action,internal-link,external-link',
            'title' => 'required',
            'banner' => 'required|file|mimes:jpg|dimensions:min_width=1024,min_height=575,max_width=1024,max_height=575'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ایجاد اسلایدر شکست خورد",
            ], 400);
        }
        $linkable_id = null;
        $link = null;

        if ($request->action === "external-link") {
            if (filter_var($request->link, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'data' => null,
                    'errors' => $validator->errors(),
                    'message' => "لینک واردشده نامعتبر است",
                ], 400);
            }
            $link = $request->link;
        }

        if ($request->action === "internal-link") {
            if (!in_array($request->link, ['subscription','dictionary','media','category','music','film','singer'])) {
                return response()->json([
                    'data' => null,
                    'errors' => null,
                    'message' => "لینک انتخاب شده نامعتبر است",
                ], 400);
            }

            if (in_array($request->link , ['category','music','film','singer']) && !$request->linkable_id) {
                return response()->json([
                    'data' => null,
                    'errors' => null,
                    'message' => "شناسه مرتبط با لینک را وارد نمایید",
                ], 400);
            } else {
                $linkable_id = $request->linkable_id;
            }
            $link = $request->link;
        }

        $slider = new Slider();
        $slider->action = $request->action;
        $slider->link = $link;
        $slider->linkable_id = $linkable_id;
        $slider->title = $request->title;
        $slider->status = $request->status ? 1 : 0;
        $slider->save();
        if ($request->hasFile('banner')) {
            File::createFile($request->banner, $slider, Slider::BANNER_FILE_TYPE);
        }

        return response()->json([
            'data' => $slider,
            'errors' => null,
            'message' => "اسلایدر موفقیت ایجاد شد"
        ]);
    }

    public function sliderUpdate(Request $request)
    {
        $slider = Slider::find($request->input('id'));
        if ($slider) {
            $slider->update([
                'status' => intval($request->input('status')) === 0 ? 0 : 1,
            ]);
        }
        return response()->json([
            'data' => null,
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

    public function removeSlider(Request $request)
    {
        $slider = Slider::find($request->input('id'));
        if ($slider) {
            $slider->delete();
            File::deleteFile($slider->files, Slider::BANNER_FILE_TYPE);
        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این اسلایدر وجود ندارد",
            ], 400);
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "حذف اسلایدر موفقیت آمیز بود",
        ]);
    }
}
