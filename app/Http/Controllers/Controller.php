<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function uploadFileById($file , $path , $id) : bool
    {
        if(config('app.deployed')){
            $path_upload = 'uploads/'. $path;
            $file_name = $id . '.' .$file->getClientOriginalExtension();
            $image_path = $path_upload .'/'. $file_name;
            Storage::disk('ftp')->put($image_path, fopen($file, 'r+'));
            return $path .'/'. $file_name;
        }else{
            $file_path = public_path().'/uploads/'.$path;
            File::ensureDirectoryExists($file_path);
            $file_name = $id . '.' .$file->getClientOriginalExtension();
            $file->move($file_path , $file_name);
            return true;
        }
    }
    public function deleteFile($path)
    {
        if(config('app.deployed')){
            Storage::disk('ftp')->delete('uploads/'.$path);
        }else{
            File::delete(public_path().'/uploads/'.$path);
        }
        return true;
    }

    public function validateData($request , $rules)
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::error(null , null , $validator->errors() , 422);
        }
        return false;
    }

    public function Homogenization($text)
    {
        $text = trim($text);
        if (strlen($text) == 0) return '';
        $text = str_replace("ي", "ی", $text);
        $text = str_replace("ك", "ک", $text);
        $text = str_replace('`', " ", $text);
        // Arabic Number
        $text = str_replace("٠", "0", $text);
        $text = str_replace("١", "1", $text);
        $text = str_replace("٢", "2", $text);
        $text = str_replace("٣", "3", $text);
        $text = str_replace("٤", "4", $text);
        $text = str_replace("٥", "5", $text);
        $text = str_replace("٦", "6", $text);
        $text = str_replace("٧", "7", $text);
        $text = str_replace("٨", "8", $text);
        $text = str_replace("٩", "9", $text);
        // Persian Number
        $text = str_replace("۰", "0", $text);
        $text = str_replace("۱", "1", $text);
        $text = str_replace("۲", "2", $text);
        $text = str_replace("۳", "3", $text);
        $text = str_replace("۴", "4", $text);
        $text = str_replace("۵", "5", $text);
        $text = str_replace("۶", "6", $text);
        $text = str_replace("۷", "7", $text);
        $text = str_replace("۸", "8", $text);
        return str_replace("۹", "9", $text);
    }
}
