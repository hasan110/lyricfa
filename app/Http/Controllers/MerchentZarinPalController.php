<?php

namespace App\Http\Controllers;

use App\Models\MerchentZarinPal;
use Illuminate\Http\Request;

class MerchentZarinPalController extends Controller
{
    public function getMerchentId(Request $request){
        $merchent = MerchentZarinPal::first();
        $merchentId = $merchent->merchent_id;

        if($merchentId){
            $response = [
                'data' => $merchent,
                'errors' => [
                ],
                'message' => "مرچنت با موفقیت گرفته شدl"
            ];
            return response()->json($response, 200);
        }else{
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مرچنت آیدی وجود ندارد"
            ];
            return response()->json($response, 400);

        }
    }

}
