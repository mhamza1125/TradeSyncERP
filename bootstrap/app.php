<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Deactivated users are signed out on their next request (audit F-07).
        $middleware->web(append: [\App\Http\Middleware\EnsureUserIsActive::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A write that reached a finished run's records through any path (docs/QC_RULES.md).
        $exceptions->render(function (\App\Exceptions\RunLockedException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => []], 422)
                : back()->with('error', $e->getMessage());
        });
    })->create();
