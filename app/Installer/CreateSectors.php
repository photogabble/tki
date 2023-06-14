<?php

namespace App\Installer;

// First half of 60.php
use Illuminate\Console\OutputStyle;

class CreateSectors implements InstallStep{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        // Create Sol
        $output->writeln(__('create_universe.l_cu_create_sol'));

        // Create Alpha Centauri
        $output->writeln(__('create_universe.l_cu_create_ac'));

        // Insert Sector Loop
        $output->writeln(__('create_universe.l_cu_insert_loop_sector_block'));
        return 0;
    }
}