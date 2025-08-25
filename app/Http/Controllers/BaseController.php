<?php

namespace App\Http\Controllers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
class BaseController extends Controller
{
    /**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int|null  $code
     * @return JsonResponse
     */
    protected function success($message = '', $data = null, $code = 200)
    {
        return response()->json([
            'code' => $code,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  array  $errors
     * @param  int  $code
     * @return JsonResponse
     */
    protected function error($message = '', $errors = [], $code = 400)
    {
        return response()->json([
            'code' => $code,
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ]);
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error('Validation failed', $validator->errors()->toArray(), 422)
        );
    }
}