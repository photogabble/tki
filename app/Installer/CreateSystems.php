<?php

namespace Tki\Installer;

// First half of 60.php
use Tki\Models\Universe;
use Tki\Models\Zone;
use Illuminate\Console\OutputStyle;

class CreateSystems extends Step implements InstallStep{

    /**
     * @throws \Exception
     */
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        $primeZone = Zone::find(1);

        // Create Sol
        $this->timer->start();
        $system = new Universe();
        $system->name = 'Sol';
        $system->port_type = 'special';
        $system->beacon = 'Sol: Hub of the Universe';
        $primeZone->sectors()->save($system);

        $this->logger->info(__('create_universe.l_cu_create_sol', ['elapsed' => $this->timer->sample()]));

        // Create Alpha Centauri
        $this->timer->start();
        $system = new Universe();
        $system->name = 'Alpha Centauri';
        $system->port_type = 'energy';
        $system->beacon = 'Alpha Centauri: Gateway to the Galaxy';
        $system->distance = 1;
        $primeZone->sectors()->save($system);

        $this->logger->info(__('create_universe.l_cu_create_ac', ['elapsed' => $this->timer->sample()]));

        // Create Remaining Sectors

        $loopsize = 1000;
        $loops = round(config('game.max_sectors') / $loopsize);
        if ($loops <= 0) $loops = 1;

        $finish = $loopsize;
        if ($finish > config('game.max_sectors')) $finish = config('game.max_sectors');

        // Already added two systems so start at 3.
        $start = 3;

        for ($i = 1; $i <= $loops; $i++) {

            $sectors = [];
            $this->timer->start();

            for ($j = $start; $j <= $finish; $j++)
            {
                $sector = new Universe();
                $sector->distance = random_int(1, config('game.universe_size'));
                $sector->angle1 = random_int(0, 180);
                $sector->angle2 = random_int(0, 90);
                $sectors[] = $sector;
            }

            $primeZone->sectors()->saveMany($sectors);
            $this->logger->info(__('create_universe.l_cu_insert_loop_sector_block', [
                'loop' => $i,
                'loops' => $loops,
                'start' =>$start,
                'finish' => $finish,
                'elapsed' => $this->timer->sample()
            ]));

            $start = $finish + 1;
            $finish += $loopsize;
            if ($finish > config('game.max_sectors')) $finish = config('game.max_sectors');
        }

        return 0;
    }
}