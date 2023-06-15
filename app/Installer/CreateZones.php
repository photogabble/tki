<?php

namespace Tki\Installer;

// Second half of 60.php
use Tki\Models\Zone;
use Illuminate\Console\OutputStyle;

class CreateZones extends Step implements InstallStep
{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        $this->timer->start();

        // Insert Unchartered Zone
        $zone = new Zone();
        $zone->name = 'Unchartered space';
        $zone->team_zone = false;
        $zone->allow_beacon = true;
        $zone->allow_attack = true;
        $zone->allow_planetattack = true;
        $zone->allow_warpedit = true;
        $zone->allow_planet = true;
        $zone->allow_trade = true;
        $zone->allow_defenses = true;
        $zone->save();

        $output->writeln(__('create_universe.l_cu_setup_unchartered', ['elapsed' => $this->timer->sample()]));

        // Insert Federation Zone
        $zone = new Zone();
        $zone->name = 'Federation space';
        $zone->team_zone = false;
        $zone->allow_beacon = false;
        $zone->allow_attack = false;
        $zone->allow_planetattack = false;
        $zone->allow_warpedit = false;
        $zone->allow_planet = false;
        $zone->allow_trade = true;
        $zone->allow_defenses = false;
        $zone->max_hull = config('game.max_fed_hull');
        $zone->save();

        $output->writeln(__('create_universe.l_cu_setup_fedspace', ['elapsed' => $this->timer->sample()]));

        // Insert Free Trade Zone
        $zone = new Zone();
        $zone->name = 'Free-Trade space';
        $zone->team_zone = false;
        $zone->allow_beacon = false;
        $zone->allow_attack = true;
        $zone->allow_planetattack = false;
        $zone->allow_warpedit = false;
        $zone->allow_planet = false;
        $zone->allow_trade = true;
        $zone->allow_defenses = false;
        $zone->save();

        $output->writeln(__('create_universe.l_cu_setup_free', ['elapsed' => $this->timer->sample()]));

        // Insert War Zone
        $zone = new Zone();
        $zone->name = 'War Zone';
        $zone->team_zone = false;
        $zone->allow_beacon = true;
        $zone->allow_attack = true;
        $zone->allow_planetattack = true;
        $zone->allow_warpedit = true;
        $zone->allow_planet = true;
        $zone->allow_trade = false;
        $zone->allow_defenses = true;
        $zone->save();

        $output->writeln(__('create_universe.l_cu_setup_warzone', ['elapsed' => $this->timer->sample()]));

        return 0;
    }
}