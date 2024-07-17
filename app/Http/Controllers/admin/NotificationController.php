<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\SingerController;
use App\Models\Music;
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
        $messages = array(
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
        ], $messages);

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
        $messages = array(
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
        ], $messages);


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
        return response()->json($response);
    }

    public function sendFCM(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه نوتیفیکیشن الزامی است',
            'id.numeric' => 'شناسه نوتیفیکیشن باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

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
            if(!isset($request->user_id)){
                $arr = [
                    'data' => null,
                    'errors' => array("با توجه به اینکه نوتیفیکیشن تکی هست user_id ضروری هست"),
                    'message' => "ارسال نوتیفیکیشن شکست خورد",
                ];
                return response()->json($arr, 400);
            }else{
                try{
                    $user = UserController::getUserById($request->user_id);
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

        $notificationData = [
            'title' => $notif->title,
            'body' => $notif->body,
            'image' => 'https://dl.lyricfa.app/uploads/notifications/'.$notif->id.'.jpg'
        ];

        $dataPayload = [
            'to' => 'VIP',
            'date' => Carbon::now(),
            'other_data' => 'not important',
            "sound" => "default"
        ];

        if($notif->type == "all"){
            $tokens = UserController::getListUsersTokenNotifications();
            foreach($tokens as $token)
            {
                DB::table('notification_queue')->insert([
                    'notification_id' => $notif->id,
                    'token' => $token
                ]);
            }

        }else if($notif->type == "one" && isset(UserController::getUserById($request->user_id)->fcm_refresh_token) && UserController::getUserById($request->user_id)->fcm_refresh_token != ""){
            $notifBody = [
                'notification' => $notificationData,
                'data' => $dataPayload,
                'time_to_live' => 3600,
                'to' => UserController::getUserById($request->user_id)->fcm_refresh_token
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

    public function getMusicData(Request $request)
    {
        try {
            $ids = $request->music_ids;
            $text = '';
            if (str_contains($ids, '-')) {
                $ids = explode('-', $ids);
                $from = $ids[0];
                $to = $ids[1];

                if (!is_numeric($from) || !is_numeric($to)) {
                    throw new Exception('لطفا بازه شناسه موزیک را به درستی وارد نمایید');
                }

                $musics_count = Music::whereBetween('id', [$from, $to])->count();
                if ($musics_count > 50) {
                    throw new Exception('تعداد موزیک های یافت شده بیش از 50 عدد است');
                }

                $musics = Music::whereBetween('id', [$from, $to])->get();
                foreach ($musics as $music) {
                    $singers = SingerController::getSingerById($music->id);
                    $singer_names = [];
                    foreach ($singers as $singer) {
                        $singer_names[] = $singer->english_name;
                    }
                    $singer_text = implode(", ", $singer_names);

                    $text .= $music->name . "->" . $singer_text . "\n";
                }
            } else {
                if (!is_numeric($ids)) {
                    throw new Exception($ids);
                }

                $music = Music::where('id', $ids)->first();
                if (!$music) {
                    throw new Exception('موزیک یافت نشد');
                }

                $singers = SingerController::getSingerById($music->id);
                $singer_names = [];
                foreach ($singers as $singer) {
                    $singer_names[] = $singer->english_name;
                }
                $singer_text = implode(", ", $singer_names);

                $text = $music->name . "->" . $singer_text;
            }

        } catch (Exception $exception) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => $exception->getMessage(),
            ], 400);
        }
        return response()->json([
            'data' => $text,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

}
