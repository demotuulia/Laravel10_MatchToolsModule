<?php

namespace Modules\Matches\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = \Auth::user();

        list ($controller, $action) = explode('@', class_basename(\Route::currentRouteAction()));
        $abilities = \Config::get('matches-abilities');
        if (isset($abilities[$controller][$action])) {

            if (!$user->can($abilities[$controller][$action])) {
                return response()
                    ->json(
                        [
                            'meta' => [
                                'status' => Response::HTTP_FORBIDDEN,
                                'message' => 'Acces denied'
                            ]
                        ],
                        Response::HTTP_FORBIDDEN
                    );
            };
        }
        return $next($request);
    }
}
