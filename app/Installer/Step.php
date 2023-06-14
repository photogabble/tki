<?php

namespace App\Installer;

use App\Helpers\ExecutionTimer;

abstract class Step implements InstallStep {
    protected ExecutionTimer $timer;

    public function __construct(ExecutionTimer $timer) {
        $this->timer = $timer;
    }
}