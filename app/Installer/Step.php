<?php

namespace Tki\Installer;

use Tki\Helpers\ExecutionTimer;

abstract class Step implements InstallStep {
    protected ExecutionTimer $timer;

    public function __construct(ExecutionTimer $timer) {
        $this->timer = $timer;
    }
}