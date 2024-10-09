<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Google_Client;
use Illuminate\Support\Facades\Storage;

class Notification
{
    public static function send(string $type , array $data): bool
    {
        switch ($type){
            case 'google_notification':

                ob_start();
                if(!isset($data['token'])) return false;
                $url = env('GOOGLEAPIS_URL');
                $apiKey = self::getFCMToken();

                $headers = array(
                    'Authorization:Bearer ' . $apiKey,
                    'Content-Type: application/json'
                );

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
                ob_clean();
                return true;
            default:
                return false;
        }
    }

    public static function getFCMToken()
    {
        $token = Setting::getItem('google_fcm_access_token');
        $expire_at = Setting::getItem('google_fcm_expires_at');

        if ($token && $expire_at && $expire_at > Carbon::now()) {
            return $token;
        }

        try {
            $credentialsFilePath = Storage::path('json/lyricfa.json');
            $client = new Google_Client();
            $client->setApplicationName("Lyricfa");
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();
        } catch (Exception $e) {
            return null;
        }

        Setting::setItem('google_fcm_access_token', $token['access_token']);
        Setting::setItem('google_fcm_expires_at', Carbon::now()->addSeconds(3500));
        return $token['access_token'];
    }
}
