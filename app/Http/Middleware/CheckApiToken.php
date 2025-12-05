<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class CheckApiToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $authToken = Auth::guard('api')->getToken();
        if(!$authToken){
            return response(['code'=> -1,'message'=>'请先登录后操作', 'data'=>[]]);
        }
        // 检测用户的登录状态，如果正常则通过
        if (Auth::guard('api')->check()) {
            $admin_id = Auth::guard('api')->payload()['sub'];
            $time = Auth::guard('api')->payload()['exp'];
            //刷新Token
            if(($time - time()) < 10*60 && ($time - time()) > 0){
                $token = Auth::guard('api')->refresh();
                if($token){
                    $request->headers->set('Authorization', 'Bearer '.$token);
                }else{
                    return response(['code'=> -1,'message'=>'请先登录后操作', 'data'=>[]]);
                }

                // 在响应头中返回新的 token
                $respone = $next($request);
                if(isset($token) && $token){
                    $respone->headers->set('Authorization', 'Bearer '.$token);
                }
                return $respone;
            }
            //token通过验证 执行下一补操作
            return $next($request);
        }
        return response(['code'=> -1,'message'=>'请先登录后操作', 'data'=>[]]);
    }
}
