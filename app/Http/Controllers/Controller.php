<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Generate a JSON response
     *
     * @param bool $success
     * @param int $statusCode
     * @param string $message
     * @param array $data
     * @return JsonResponse
     */
    protected function toJson(bool $success, int $statusCode, string $message, array $data = []): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a success JSON response
     *
     * @param string $message
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function success(string $message = 'Operation successful', array $data = [], int $statusCode = 200): JsonResponse
    {
        return $this->toJson(true, $statusCode, $message, $data);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message
     * @param int $statusCode
     * @param array $data
     * @return JsonResponse
     */
    protected function error(string $message = 'Operation failed', int $statusCode = 400, array $data = []): JsonResponse
    {
        return $this->toJson(false, $statusCode, $message, $data);
    }
}
