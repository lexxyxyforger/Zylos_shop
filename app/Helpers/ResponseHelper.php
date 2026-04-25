<?php
namespace App\Helpers;

use Illuminate\Http\JsonResponse;
class ResponseHelper
{
    public static function success($data = null, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = 'Error', $code = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}