<?php

namespace App\Services;

use Carbon\Carbon;

class Notification
{
    public static function send(string $type , array $data)
    {
        switch ($type){
            case 'google_notification':

                if(!isset($data['token'])) return false;
                $url = env('GOOGLEAPIS_URL');
                $apiKey = env('GOOGLEAPIS_APIKEY');

                $headers = array(
                    'Authorization:Bearer ' . $apiKey,
                    'Content-Type: application/json'
                );

                //$notificationData = [
                //    'title' => $data['title'],
                //    'body' => $data['body'],
                //    'image' => isset($data['image']) ?? null
                //    // 'click_action' => 'activities.notifhandler'
                //];

                //$dataPayload = [
                //    'to' => 'VIP',
                //    'date' => Carbon::now(),
                //    'other_data' => 'not important',
                //    "sound" => "default"
                //];

                //$notificationBody = [
                //    'notification' => $notificationData,
                //    'data' => $dataPayload,
                //    'time_to_live' => 3600,
                //    'to' => $data['token']
                //];

                $notificationBody = [
                    'message' => [
                        "token" => $data['token'],
                        "notification" => [
                            "title" => $data['title'],
                            "body" => $data['body'],
                        ],
                    ]
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationBody));

                curl_exec($ch);
                curl_close($ch);

                return true;
            default:
                return false;
        }

        return false;
    }
}
