<?php

namespace Hanwoolderink\Flow;

use Illuminate\Support\ServiceProvider;

class FlowProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(Action::class, function (Action $action) {

            $authorized = method_exists($action, 'authorize') ? app()->call([$action, 'authorize']) : true;

            if (!$authorized) {
                throw new ActionUnauthorizedException('Authentication failed', 403);
            }

            return $action;
        });
    }
}
