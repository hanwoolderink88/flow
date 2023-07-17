<?php

namespace Hanwoolderink\Flow;

use Closure;
use Hanwoolderink\Flow\Traits\ExecutesAction;

class Step
{
    use ExecutesAction;

    private mixed $result;

    public function __construct(
        private string $action,
        private Closure|array $parameters = [],
        private bool $queue =  false,
    ) {
    }

    // todo: probably belongs in a separate class (flow is needed for context but circular isnt pretty)
    public function execute(Flow $flow): self
    {
        if ($this->parameters instanceof Closure) {
            $parameters = $this->parameters;
            $parameters = $parameters($flow);
        } else {
            $parameters = $this->parameters;
        }

        if ($this->shouldQueue()) {
            ActionDispatcher::dispatch($this->action, $parameters);
        } else {
            $result = $this->executeAction($this->action, $parameters ?? []);

            $this->setResult($result);
        }

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getParameters(): null|Closure|array
    {
        return $this->parameters;
    }

    public function shouldQueue(): bool
    {
        return $this->queue;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): self
    {
        $this->result = $result;

        return $this;
    }
}
