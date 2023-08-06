<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;

class PositionController extends Controller
{
    public function showProfitBanner(Request $request)
    {
        $v = $this->validateData($request, [
            'trade_type' => 'required',
            'position_side' => 'required',
            'symbol' => 'required',
            'opener_id' => 'required',
            'entry_price' => 'required',
            'market_price' => 'required',
            'pnl' => 'required'
        ]);
        if ($v) {
            return $v;
        }

//        $trader = User::find($request->opener_id);
//        if($trader){
//            $username = $trader->username;
//        }else{
//            $username = '-';
//        }

        header("Content-type: image/jpeg");
        // header('Access-Control-Allow-Origin: *');

//        if ($request->trade_type == 1 && $request->pnl >= 0) {
//            $img = imagecreatefromjpeg(public_path() . "/banners/futures_green.jpg");
//        }
//
//        if ($request->trade_type == 1 && $request->pnl < 0) {
//            $img = imagecreatefromjpeg(public_path() . "/banners/futures_red.jpg");
//        }
//
//        if ($request->trade_type == 0 && $request->pnl >= 0) {
//            $img = imagecreatefromjpeg(public_path() . "/banners/spot_green.jpg");
//        }
//
//        if ($request->trade_type == 0 && $request->pnl < 0) {
//            $img = imagecreatefromjpeg(public_path() . "/banners/spot_red.jpg");
//        }
        $img = imagecreatefromjpeg(public_path() . "/banners/banner.jpg");
        $username = "Majid zare";
        $angle = 0;


        $fontFile = public_path() . "/banners/arial_black.ttf";
        $fontSize = 35;
        $fontColor = imagecolorallocate($img, 255, 221, 0);
        $posX = 140;
        $posY = 100;
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, "WWW.ALLWINCRYPTO.COM");



        $fontSize = 70;
        $fontColor = imagecolorallocate($img, 255, 255, 255);
        $posX = 40;
        $posY = 310;
        $fontFile = public_path() . "/banners/arial_black.ttf";
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $request->symbol);




        $fontSize = 35;
        $fontFile = public_path() . "/banners/arial_bold.ttf";
        $posY = 390;

        if ($request->trade_type == 1) {

            $posX = 40;
            if ($request->position_side == 0) {
                $longShort = "Short";
                $fontColor = imagecolorallocate($img, 209, 12, 19);
                imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $longShort);
            } else {
                $longShort = "Long";
                $fontColor = imagecolorallocate($img, 12, 209, 19);
                imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $longShort);
            }
            $fontColor = imagecolorallocate($img, 255, 255, 255);
            $posX = 180;
            imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, "| x" . $request->leverage ." | FUTURE");

        } else {

            $posX = 40;
            if ($request->position_side == 0) {
                $longShort = "Short";
                $fontColor = imagecolorallocate($img, 209, 12, 19);
                imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $longShort);
            } else {
                $longShort = "Long";
                $fontColor = imagecolorallocate($img, 12, 209, 19);
                imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $longShort);
            }
            $fontColor = imagecolorallocate($img, 255, 255, 255);
            $posX = 180;
            imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile,  "| SPOT");
        }

        $posX = 40;
        $posY = 600;
        $fontSize = 100;
        $fontFile = public_path() . "/banners/arial_black.ttf";
        if ($request->pnl >= 0) {
            $fontColor = imagecolorallocate($img, 12, 209, 19);
            imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, ('+' . $request->pnl . '%'));
        } else {
            $fontColor = imagecolorallocate($img, 209, 12, 19);
            imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, ($request->pnl . '%'));
        }



        $fontSize = 35;
        $fontFile = public_path() . "/banners/arial_bold.ttf";
        $fontColor = imagecolorallocate($img, 255, 255, 255);
        $posX = 40;
        $posY = 750;
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, "Entry Price: ");

        $posX = 310;
        $posY = 750;
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $request->entry_price);
        // (B) WRITE TEXT
        $posX = 40;
        $posY = 820;
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, "Mark Price: ");

        $posX = 310;
        $posY = 820;
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $request->market_price);






        $fontSize = 30;
        $fontColor = imagecolorallocate($img, 255, 255, 255);
        $posX = 40;
        $posY = 1020;
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, "Refferal Name:");

        $fontSize = 45;
        $fontColor = imagecolorallocate($img, 188, 188, 188);
        $posY = 1080;
        imagettftext($img, $fontSize, $angle, $posX, $posY, $fontColor, $fontFile, $username);


        // return 3;
        // (C) OUTPUT IMAGE
        // (C1) DIRECTLY SHOW IMAGE
//         imagejpeg($img);
//         imagedestroy($img);
        $filepathname = "uploads/demo.jpg";
        // (C2) OR SAVE TO A FILE
        $quality = 40; // 0 to 100
        imagejpeg($img, public_path() . "/uploads/demo.jpg", $quality);

        return Response::success('demo.jpg', null, 200);
    }
}
