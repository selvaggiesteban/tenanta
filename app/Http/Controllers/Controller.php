<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function currentTenant()
    {
        return app('current_tenant');
    }

    protected function currentUser()
    {
        return auth('api')->user();
    }

    protected function respondSuccess($data = null, string $message = 'Success', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function respondError(string $message, int $status = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function respondCreated($data = null, string $message = 'Created successfully')
    {
        return $this->respondSuccess($data, $message, 201);
    }

    protected function respondNoContent()
    {
        return response()->json(null, 204);
    }
}
