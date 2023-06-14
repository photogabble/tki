<?php

namespace App\Helpers;

class ExecutionTimer{

    private ?float $initTime = null;
    private float $startTime;

    public function start()
    {
        $this->startTime = microtime(true);
        if (!$this->initTime) $this->initTime = microtime(true);
    }

    public function sample(): float
    {
        return round(microtime(true) - $this->startTime, 4);
    }

    public function stop(): float
    {
        $elapsedSeconds = microtime(true) - $this->initTime;
        $this->initTime = null;

        return $elapsedSeconds;
    }
}