<?php

namespace Ypa\AbstractApi;

use Closure;

class AbstractApiMiddleware
{
    use AbstractApiValidationTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $apiData = $this->validateValues($request->all());
        if ($apiData['success'] !== true) {
            return response()->json($apiData, 400);
        }
        return $next($request);
    }
}
