<?php 

namespace App\Helpers;

class Response
{
    public static function errorResponse(\Exception $exception, $msg, $data = null)
    {
        return response()->json([
            'status' => false,
            'data' => $data,
            'message' => $msg,
            'errorDetails' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]
        ], 500);
    }

    public static function successResponse($msg, $data = null)
    {
        return response()->json([
            'status' => true,
            'message' => $msg,
            'data' => $data
        ], 200);
    }

    public static function failedResponse($msg, $data = null)
    {
        return response()->json([
            'status' => false,
            'message' => $msg,
            'data' => $data
        ], 500);
    }

    public static function notFoundResponse($msg, $data = null)
    {
        return response()->json([
            'status' => false,
            'message' => $msg,
            'data' => $data
        ], 404);
    }
}
