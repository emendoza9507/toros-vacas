<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class Response {
    public function response($status = 200) {
        return new JsonResponse($this, $status);
    }
}