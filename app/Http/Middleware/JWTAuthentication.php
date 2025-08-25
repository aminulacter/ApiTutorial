<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JWTAuthentication extends BaseMiddleware
{
   
    public function handle($request, Closure $next)
    {
        try{
            $this->authenticate($request);
        }catch(\Exception $e){
           return response()->json([
            'code' => 400,
            'status' => 'error',
            'message' => 'Sorry, you have been logged out. Please login again to continue.',
            'errors' => ['token' => 'Token could not be parsed', 'exception' => $e->getMessage()]
           ], 400);
        }
       

        return $next($request);
    }

    public function checkForToken(Request $request)
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();
        }catch(\Exception $e){
            throw new TokenInvalidException($e->getMessage());
        }
    }

    /**
     * Attempt to authenticate a user via the token in the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function authenticate(Request $request)
    {
      //  $this->checkForToken($request);

      try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                throw new TokenInvalidException('Token is not valid');
            }
        } catch (TokenExpiredException $e) {
            throw $e; // Re-throw the specific exception
        } catch (JWTException $e) {
            throw new TokenInvalidException('Token is invalid');
        } catch (\Exception $e) {
            throw new TokenInvalidException('Something went wrong');
        }
    }

    /**
     * Set the authentication header.
     *
     * @param  \Illuminate\Http\Response|\Illuminate\Http\JsonResponse  $response
     * @param  string|null  $token
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function setAuthenticationHeader($response, $token = null)
    {
        $token = $token ?: $this->auth->refresh();
        $response->headers->set('Authorization', 'Bearer '.$token);

        return $response;
    }
}
