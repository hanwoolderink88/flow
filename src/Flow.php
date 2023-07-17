<?php

namespace Hanwoolderink\Flow;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Flow
{
    private Collection $steps;

    private bool $wrapInTransaction = false;

    public function __construct()
    {
        $this->steps = new Collection();
    }

    public function action(string $action, null|array|Closure $parameters = null, bool $queue = false): self
    {
        $step = new Step($action, $parameters, $queue);

        $this->steps->push($step);

        return $this;
    }

    public function run(): self
    {
        if ($this->wrapInTransaction) {
            DB::beginTransaction();
        }

        $this->steps->each(function (Step $step) {
            $step->execute($this);
        });

        if ($this->wrapInTransaction) {
            DB::commit();
        }

        return $this;
    }

    public function getResult(string $action): mixed
    {
        return $this->steps->first(fn (Step $step) => $step->getAction() === $action)->getResult();
    }

    public function wrapInTransaction(): self
    {
        $this->wrapInTransaction = true;

        return $this;
    }
}
