<?php

namespace App\Helper;
class ResponseHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function success($message=null,$data=[], $statusCode=200){
        return response()->json([
            "status" => true,
            "message" => $message?? "Success",
            "data" => $data
        ],$statusCode);
    }
    public static function error($message=null,$data=null, $statusCode=400){
        return response()->json([
            "status" => false,
            "message" => $message?? "error in program",
            "data" => $data?? null
        ],$statusCode);
    }
    
}
