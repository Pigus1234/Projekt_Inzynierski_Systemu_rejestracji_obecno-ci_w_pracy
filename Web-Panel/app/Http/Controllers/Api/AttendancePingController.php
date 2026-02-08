<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class AttendancePingController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
