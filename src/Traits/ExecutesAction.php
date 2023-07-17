<?php

namespace Hanwoolderink\Flow\Traits;

use Hanwoolderink\Flow\Action;

trait ExecutesAction
{
    private function executeAction(string $action, array $parameters): mixed
    {
        $action = app($this->action);

        if (!$action instanceof Action) {
            throw new \Exception('Action must be an instance of ' . Action::class);
        }

        if (!method_exists($action, 'handle')) {
            throw new \Exception('Action must have an handle method');
        }

        return app()->call([$action, 'handle'], $parameters);
    }
}
