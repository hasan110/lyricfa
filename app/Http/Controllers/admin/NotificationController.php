<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SingerHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Music;
use App\Models\Notification;
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
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن نوتیفیکیشن شکست خورد"
            ], 400);
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
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش نوتیفیکیشن شکست خورد"
            ], 400);
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

        return response()->json([
            'data' => $notifications,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
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
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ارسال نوتیفیکیشن شکست خورد",
            ], 400);
        }

        $notif = Notification::find($request->id);

        if($notif->type == "one"){
            if(!isset($request->user_id)){
                return response()->json([
                    'data' => null,
                    'errors' => array("با توجه به اینکه نوتیفیکیشن تکی هست user_id ضروری هست"),
                    'message' => "ارسال نوتیفیکیشن شکست خورد",
                ], 400);
            }else{
                try{
                    $user = (new UserHelper())->getUserById($request->user_id);
                    if($user->fcm_refresh_token == ""){
                        return response()->json([
                            'data' => null,
                            'errors' => array("این کاربر در Fcm ثبت نام نشده است"),
                            'message' => "ارسال نوتیفیکیشن شکست خورد",
                        ], 400);
                    }
                }catch (Exception $e){
                    return response()->json([
                        'data' => null,
                        'errors' => array("چنین کاربری وجود ندارد"),
                        'message' => "ارسال نوتیفیکیشن شکست خورد",
                    ], 400);
                }
            }
        }

        if($notif->type == "all"){
            $tokens = UserController::getListUsersTokenNotifications();
            foreach($tokens as $token)
            {
                DB::table('notification_queue')->insert([
                    'notification_id' => $notif->id,
                    'token' => $token
                ]);
            }

        } else if($notif->type == "one" && isset((new UserHelper())->getUserById($request->user_id)->fcm_refresh_token) && (new UserHelper())->getUserById($request->user_id)->fcm_refresh_token != ""){

            $notificationData = [
                'token' => (new UserHelper())->getUserById($request->user_id)->fcm_refresh_token,
                'title' => $notif->title,
                'body' => $notif->body,
                'image' => 'https://dl.lyricfa.app/uploads/notifications/'.$notif->id.'.jpg'
            ];

            \App\Services\Notification::send('google_notification' , $notificationData);
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
                    $singers = (new SingerHelper())->getMusicSingers($music->id);
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

                $singers = (new SingerHelper())->getMusicSingers($music->id);
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
