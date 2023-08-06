<?php
 
namespace App\Console\Commands;
 
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Notification;
 
class NotificationSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification';
 
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
 
    /**
     * Execute the console command.
     *
     * @param  \App\Support\DripEmailer  $drip
     * @return mixed
     */
    public function handle()
    {
        $items = DB::table('notification_queue')->take(100)->get();

        $url = "https://fcm.googleapis.com/fcm/send";
        $apiKey = "AAAABTTSEFI:APA91bHnEDLP8s_WQQw-cMm7Rf7NsGtquWDT3JJPLnxDUBCJJUV3fvLbgQ5fAD4mh0TZOW77WnjVKLUnFlGxxk9wObBRFSl-9vnBcLUFwJQC-LaG4nhgq8LG6_tvtiMcmz0-ILAaskPd";
        $headers = array(
            'Authorization:key=' . $apiKey,
            'Content-Type: application/json'
        );

        foreach($items as $item)
        {
            $notif = Notification::find($item->notification_id);
            if(!$notif || $notif->type !== 'all'){
                DB::table('notification_queue')->where('id' , $item->id)->delete();
                continue;
            }

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

            $notifBody = [
                'notification' => $notificationData,
                'data' => $dataPayload,
                'time_to_live' => 3600,
                'to' => $item->token
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notifBody));

            curl_exec($ch);
            curl_close($ch);

            DB::table('notification_queue')->where('id' , $item->id)->delete();
        }

        return 'ok';
    }
}