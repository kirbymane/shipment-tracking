<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateTrackingNumber
 */
class ValidateTrackingNumber
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $trackingNumber = $request->route('tracking_number');

        if (!preg_match('/^[A-Za-z0-9]+$/', $trackingNumber)) {
            return response()->json(['error' => 'Invalid tracking number'], 400);
        }

        return $next($request);
    }
}
