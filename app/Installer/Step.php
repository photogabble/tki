<?php

namespace Tki\Installer;

use Psr\Log\LoggerInterface;
use Tki\Helpers\ExecutionTimer;

abstract class Step implements InstallStep {
    protected ExecutionTimer $timer;
    protected LoggerInterface $logger;

    public function __construct(ExecutionTimer $timer, LoggerInterface $logger) {
        $this->timer = $timer;
        $this->logger = $logger;
    }
}