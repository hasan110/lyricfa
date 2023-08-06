<?php

namespace App\Http\Controllers;

use App\Models\PlayList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayListController extends Controller
{

    function insertUserPlayList(Request $request)
    {


        $messsages = array(
            'name.required' => 'نام پلی لیست الزامی است'

        );

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن پلی لیست شکست خورد"
            ];
            return response()->json($arr, 400);
        }


        $name = $this->nameIsExistInListUserPlayList($request);

        if (isset($name)) { // array use count
            $arr = [
                'data' => $name,
                'errors' => [
                ],
                'message' => "لیست پخشی با این اسم وجود دارد"
            ];


            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            $playList = new PlayList;
            $playList->user_id = $user_id;
            $playList->name = $request->name;
            $playList->save();

            $arr = [
                'data' => $playList,
                'errors' => [
                ],
                'message' => "لیست پخش با موفقیت اضافه شد"
            ];
            return response()->json($arr, 200);
        } else {

            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کاربر شناسایی نشد"
            ];
            return response()->json($arr, 401);
        }
    }


    function getUserPlayList(Request $request)
    {



        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if($user) {
            $user_id = $user->id;
            $playlists = PlayList::where("user_id", $user_id)->get();
                foreach($playlists as $item){
                    $musics = PlayListMusicController::getRandom4Music($item->id);
                    $item->musics = $musics;
                }
            $arr = [
                'data' => $playlists,
                'errors' => [
                ],
                'message' => "لیست پخش با موفقیت دریافت شد"
            ];
            return response()->json($arr, 200);
        }else{
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "خطا در احراز هویت کاربر"
            ];
            return response()->json($arr, 401);
        }


    }


    function nameIsExistInListUserPlayList(Request $request)
    {

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            return PlayList::where('name', $request->name)->where('user_id', $user_id)->first();
        }
        else{
            return  null;
        }
    }

    function removePlayListById(Request $request)
    {


        $messsages = array(
            'id.required' => 'شناسه پلی لیست الزامی است',
            'id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "حذف کردن پلی لیست شکست خورد"
            ];
            return response()->json($arr, 400);
        }


        $remove = $this->getPlayListById($request);
        if (isset($remove)) {
            $remove->delete();

            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "حذف با موفقیت انجام شد"
            ];
            return response()->json($arr, 200);

        } else {

            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "این شناسه برای حذف وجود ندارد"
            ];
            return response()->json($arr, 200);
        }
    }


    function editPlayListById(Request $request)
    {


        $messsages = array(
            'id.required' => 'شناسه پلی لیست الزامی است',
            'name.required' => 'نام جهت ویرایش الزامی است',
            'id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required'
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش کردن پلی لیست شکست خورد"
            ];
            return response()->json($arr, 400);
        }


        $edit = $this->getPlayListById($request);
        if (isset($edit)) {
            $edit->name = $request->name;
            $edit->save();

            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "ویرایش با موفقیت انجام شد"
            ];
            return response()->json($arr, 200);

        } else {

            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "این شناسه برای ویرایش وجود ندارد"
            ];
            return response()->json($arr, 200);
        }
    }

    function getPlayListById(Request $request)
    {
        return PlayList::where('id', $request->id)->first();

    }


}
