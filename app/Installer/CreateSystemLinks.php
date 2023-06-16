<?php

namespace Tki\Installer;

// Last half of 70.php
use Tki\Models\Link;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\DB;

class CreateSystemLinks extends Step implements InstallStep{

    /**
     * @throws \Exception
     */
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        $this->timer->start();

        // Add Sector Size amount of links to the links table
        DB::beginTransaction();

        for ($i = 0 ; $i < $config->maxSectors; $i++) {
            $link = new Link();

            $locationOne = random_int(1, $config->maxSectors - 1);
            $locationTwo = random_int(1, $config->maxSectors - 1);

            if ($locationOne === $locationTwo) continue;

            $link->start = $locationOne;
            $link->dest = $locationTwo;
            $link->save();
        }

        DB::commit();

        $this->logger->info(__('create_universe.l_cu_loop_random_oneway', ['elapsed' => $this->timer->sample()]));

        // Add (sector size * 2) amount of links to the links table
        DB::beginTransaction();

        for ($i = 0 ; $i < $config->maxSectors; $i++) {
            $locationOne = random_int(1, $config->maxSectors - 1);
            $locationTwo = random_int(1, $config->maxSectors - 1);

            if ($locationOne === $locationTwo) continue;

            $link = new Link();
            $link->start = $locationOne;
            $link->dest = $locationTwo;
            $link->save();

            $link = new Link();
            $link->start = $locationTwo;
            $link->dest = $locationOne;
            $link->save();
        }

        DB::commit();

        $this->logger->info(__('create_universe.l_cu_loop_random_twoway', ['elapsed' => $this->timer->sample()]));

        // TODO: there is a chance that the start and dest is the same
        // TODO: trim links that are over a certain distance

        return 0;
    }
}