<?php

namespace App\Traits;

/**
 * This is for returning Http responses
 */
trait HttpResponses
{
    protected function success($data =[],$message = null, $code = 200){
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => empty($data) ? new \stdClass : $data ,
            'errors'=> new \stdClass,
        ],$code);
    }
    

      protected function error($data = [],$message = null, $errors, $code){
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => empty($data) ? new \stdClass :  $data,
            'errors' => empty($errors) ? new \stdClass : $errors,
        ],$code);
    }
    
}
 