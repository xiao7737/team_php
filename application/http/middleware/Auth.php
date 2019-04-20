<?php

namespace app\http\middleware;

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
        $token = Request::instance()->header('Authorization', '');

        if ($token) {
            $key = 'login_auth' . $token;
            if ($token !== Redis::get($key)) {
                return response('Unauthorized.', 401);
            }
            return $next($request);
        }
        return response('Unauthorized.', 401);
    }
}
