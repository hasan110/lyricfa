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

        foreach($items as $item)
        {
            $notif = Notification::find($item->notification_id);
            if(!$notif || $notif->type !== 'all'){
                DB::table('notification_queue')->where('id' , $item->id)->delete();
                continue;
            }

            $notificationData = [
                'token' => $item->token,
                'title' => $notif->title,
                'body' => $notif->body,
                'image' => 'https://dl.lyricfa.app/uploads/notifications/'.$notif->id.'.jpg'
            ];

            \App\Services\Notification::send('google_notification' , $notificationData);

            DB::table('notification_queue')->where('id' , $item->id)->delete();
        }

        return 'ok';
    }
}
