<?php

namespace App\Installer;

// 70.php
use Illuminate\Console\OutputStyle;

class CreateNews implements InstallStep{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        // Create big bang news event

        return 0;
    }
}