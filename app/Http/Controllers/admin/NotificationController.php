<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\admin\UserController;
use App\Models\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function addNotification(Request $request)
    {


        $messsages = array(
            'title.required' => 'عنوان نوتیفیکیشن الزامی است',
            'body.required' => 'بدنه نوتیفیکیشن الزامی است',
            'type.required' => 'نوع نوتیفیکیشن الزامی است',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
            'image.dimensions' => 'عکس باید (200 تا 500) در (200 تا 500) باشد',

        );

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'body' => 'required',
            'type' => 'required',
            'image' => 'file|mimes:jpg|dimensions:min_width=200,min_height=200,max_width=500,max_height=500',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن نوتیفیکیشن شکست خورد"
            ];
            return response()->json($arr, 400);
        }


        $notification = new Notification();
        $notification->title = $request->title;
        $notification->body = $request->body;
        $notification->type = $request->type;
        $notification->status_send = 0;
        $notification->save();

        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image,"notifications", $notification->id);
        }

        return $notification;
    }

    public function editNotification(Request $request)
    {


        $messsages = array(
            'id.required' => 'شناسه ی نوتیفیکیشن الزامی است',
            'id.numeric' => 'شناسه نوتیفیکیشن باید عدد باشد',
            'title.required' => 'عنوان نوتیفیکیشن الزامی است',
            'body.required' => 'بدنه نوتیفیکیشن الزامی است',
            'type.required' => 'نوع نوتیفیکیشن الزامی است',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
            'image.dimensions' => 'عکس باید (200 تا 500) در (200 تا 500) باشد',

        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'title' => 'required',
            'body' => 'required',
            'type' => 'required',
            'image' => 'file|mimes:jpg|dimensions:min_width=200,min_height=200,max_width=500,max_height=500',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش نوتیفیکیشن شکست خورد"
            ];
            return response()->json($arr, 400);
        }


        $notification = $this->getNotificationById($request->id);
        $notification->title = $request->title;
        $notification->body = $request->body;
        $notification->type = $request->type;
        $notification->status_send = 1;
        $notification->save();

        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image,"notifications", $notification->id);
        }

        return $notification;
    }

    public static function getNotificationById($id): Notification
    {
        return  Notification::where('id', $id)->first();
    }

    public function getNotifications(Request $request)
    {
        $notifications = Notification::where('title', 'LIKE', "%{$request->search_key}%")->orderBy("id", "DESC")->paginate(50);

        $response = [
            'data' => $notifications,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public function sendFCM(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه نوتیفیکیشن الزامی است',
            'id.numeric' => 'شناسه نوتیفیکیشن باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ارسال نوتیفیکیشن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $notif = Notification::find($request->id);

        if($notif->type == "one"){
            if(!isset($request->api_token)){
                $arr = [
                    'data' => null,
                    'errors' => array("با توجه به اینکه نوتیفیکیشن تکی هست api_token ضروری هست"),
                    'message' => "ارسال نوتیفیکیشن شکست خورد",
                ];
                return response()->json($arr, 400);
            }else{
                try{
                    $user = UserController::getUserByToken($request->api_token);
                    if($user->fcm_refresh_token == ""){
                        $arr = [
                            'data' => null,
                            'errors' => array("این کاربر در Fcm ثبت نام نشده است"),
                            'message' => "ارسال نوتیفیکیشن شکست خورد",
                        ];
                        return response()->json($arr, 400);
                    }
                }catch (Exception $e){
                    $arr = [
                        'data' => null,
                        'errors' => array("چنین کاربری وجود ندارد"),
                        'message' => "ارسال نوتیفیکیشن شکست خورد",
                    ];
                    return response()->json($arr, 400);
                }
            }
        }
        $url = "https://fcm.googleapis.com/fcm/send";
        //serverKey
        $apiKey = "AAAABTTSEFI:APA91bHnEDLP8s_WQQw-cMm7Rf7NsGtquWDT3JJPLnxDUBCJJUV3fvLbgQ5fAD4mh0TZOW77WnjVKLUnFlGxxk9wObBRFSl-9vnBcLUFwJQC-LaG4nhgq8LG6_tvtiMcmz0-ILAaskPd";
        $headers = array(
            'Authorization:key=' . $apiKey,
            'Content-Type: application/json'
        );

        //notification content
        $notificationData = [
            'title' => $notif->title,
            'body' => $notif->body,
            'image' => 'https://dl.lyricfa.app/uploads/notifications/'.$notif->id.'.jpg'
            // 'click_action' => 'activities.notifhandler'
        ];

        //Optional
        $dataPayload = [
            'to' => 'VIP',
            'date' => Carbon::now(),
            'other_data' => 'not important',
            "sound" => "default"
        ];

        //Create Api body
        if($notif->type == "all"){
            $tokens = UserController::getListUsersTokenNotifications();
            foreach($tokens as $token)
            {
                DB::table('notification_queue')->insert([
                    'notification_id' => $notif->id,
                    'token' => $token
                ]);
            }

        }else if($notif->type == "one" && isset(UserController::getUserByToken($request->api_token)->fcm_refresh_token) && UserController::getUserByToken($request->api_token)->fcm_refresh_token != ""){
            $notifBody = [
                'notification' => $notificationData,
                //data payload is optional
                'data' => $dataPayload,
                //optional in seconds max_time : 4 week
                'time_to_live' => 3600,
                'to' => UserController::getUserByToken($request->api_token)->fcm_refresh_token
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notifBody));
    
            //Execute
            curl_exec($ch);
            curl_close($ch);
        }

        $notif->status_send = 1;
        $notif->save();

        return true;
    }

}
