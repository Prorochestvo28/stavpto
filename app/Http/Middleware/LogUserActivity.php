<?php

namespace App\Http\Middleware;

use App\Models\UserActivityLog;
use App\Support\ActivityRouteLabels;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    private const ATTR_USER_ID = '_sed_activity_log_user_id';

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set(self::ATTR_USER_ID, $request->user()?->id);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $actingUserId = $request->attributes->get(self::ATTR_USER_ID);

        if ($actingUserId === null) {
            return;
        }

        $method = $request->method();
        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        if ($request->hasSession() && $this->sessionHasNonEmptyErrors($request)) {
            return;
        }

        $description = ActivityRouteLabels::describe($request);
        $routeName = $request->route()?->getName();

        UserActivityLog::query()->create([
            'user_id' => $actingUserId,
            'description' => $description,
            'route_name' => $routeName,
            'http_method' => $method,
        ]);
    }

    private function sessionHasNonEmptyErrors(Request $request): bool
    {
        $errors = $request->session()->get('errors');

        if ($errors instanceof ViewErrorBag) {
            return $errors->isNotEmpty();
        }

        if ($errors instanceof MessageBag) {
            return $errors->isNotEmpty();
        }

        return false;
    }
}
