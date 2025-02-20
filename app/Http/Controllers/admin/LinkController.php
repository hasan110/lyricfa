<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LinkController extends Controller
{
    public function addLink(Request $request)
    {
        $messages = array(
            'type.required' => 'نوع لینک اجباری است',
            'link_from_type.required' => 'نوع مبدا لینک اجباری است',
            'link_from_id.required' => 'مبدا لینک اجباری است.',
            'link_to_type.required' => 'نوع مقصد لینک اجباری است',
            'link_to_id.required' => 'مقصد لینک اجباری است',
        );

        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'link_from_type' => 'required',
            'link_from_id' => 'required',
            'link_to_type' => 'required',
            'link_to_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "اضافه کردن لینک شکست خورد",
            ], 400);
        }

        if ($request->link_from_type === $request->link_to_type && $request->link_from_id === $request->link_to_id) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "لینک به خود امکان پذیر نیست",
            ], 400);
        }

        $check = Link::where(function ($query) use ($request) {
            $query->where('link_from_type', $request->link_from_type)
                ->where('link_from_id', $request->link_from_id)
                ->where('link_to_type', $request->link_to_type)
                ->where('link_to_id', $request->link_to_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('link_from_type', $request->link_to_type)
                ->where('link_from_id', $request->link_to_id)
                ->where('link_to_type', $request->link_from_type)
                ->where('link_to_id', $request->link_from_id);
        })->first();

        if ($check) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "این لینک قبلا انجام شده است",
            ], 400);
        }

        $link = new Link();
        $link->type = $request->type;
        $link->link_from_type = $request->link_from_type;
        $link->link_from_id = $request->link_from_id;
        $link->link_to_type = $request->link_to_type;
        $link->link_to_id = $request->link_to_id;
        $link->save();


        return response()->json([
            'data' => $link,
            'errors' => null,
            'message' => "لینک با موفقیت اضافه شد"
        ]);
    }

    public function deleteLink(Request $request)
    {
        Link::whereId($request->input('id'))->delete();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "لینک با موفقیت حذف شد"
        ]);
    }
}
