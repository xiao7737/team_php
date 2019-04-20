<?php

namespace app\http\middleware;

use think\facade\Cache;
use think\facade\Request;
use think\Response;

class Auth
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed|Response
     */
    public function handle($request, \Closure $next)
    {
        $token      = Request::instance()->header('Authorization', '');
        $token_true = explode('@', $token);
        $token_key  = $token_true[0];          //用户id

        if ($token) {
            $key = 'auth_' . $token_key;
            if (Cache::get($key) !== $token) {
                return response('Unauthorized.', 401);
            }
            return $next($request);
        }
        return response('Unauthorized.', 401);
    }
}
