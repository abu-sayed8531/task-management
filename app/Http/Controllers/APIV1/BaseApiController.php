<?php

namespace App\Http\Controllers\APIV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseApiController extends Controller
{
    public function success($data, $message = "success", $code = 200)
    {
        return response()->json(
            [
                'data' => $data,
                'message' => $message,
                'success' => true,
            ],
            $code,
        );
    }

    public function error($message = 'There is something went wrong', $code = 500, ?\Throwable $err = null)
    {
        $response = [
            'message' => $message,
            'success' => false,
        ];

        if (app()->environment('local') && $err) {
            $response['error'] = $err->getMessage();
        }
        return response()->json(
            $response,
            $code,
        );
    }
}
