<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;



class SendSmsController extends Controller
{
    //

    public function send_sms(Request $request)
    {
        // validate phoneno and message variable
        // start validation
        $validator = Validator::make($request->all(), [
            'phone_no' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        // end validation

        // start send sms
        // put phone no and message into a variable
        $phone_no =  $request->phone_no;
        $message =  $request->message;

        // build API too send sms
        $url = "https://api.1s2u.io/bulksms?username=mmsdrc4&password=web53030&mno=$phone_no"."&sid=mafamille&msg=".rawurlencode($message)."&mt=0&fl=0";

        // send sms
        return file_get_contents($url);
        // end send sms

        #return response()->json(['message' => 'Already in your wishlist'], 409);
    }
}
