<?php

namespace Hanwoolderink\Flow;

use Hanwoolderink\Flow\Traits\ExecutesAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActionDispatcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ExecutesAction;

    public function __construct(
        private string $action,
        private array $parameters = [],
    ) {
    }

    public function handle(): void
    {
        $this->executeAction($this->action, $this->parameters);
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
