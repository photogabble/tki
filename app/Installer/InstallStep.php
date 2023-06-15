<?php

namespace Tki\Installer;

use Illuminate\Console\OutputStyle;

interface InstallStep {
    public function execute(OutputStyle $output, InstallConfig $config): int;
}