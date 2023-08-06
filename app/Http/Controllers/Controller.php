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
        if(env('DEPLOYED')){
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
        if(env('DEPLOYED')){
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

}
