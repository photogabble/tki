<?php

namespace App\Installer;

// 70.php
use Illuminate\Console\OutputStyle;

class CreatePlanets implements InstallStep{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        // Get the sector id for any sector that allows planets

        // Insert all of the planets in one mega sql shot

        // Add Sector Size amount of links to the links table

        // Add (sector size * 2) amount of links to the links table

        return 0;
    }
}