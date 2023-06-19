<?php

namespace Tki\Installer;

// Second half of 60.php
use Tki\Models\Zone;
use Illuminate\Console\OutputStyle;
use Tki\Types\ZonePermission;

class CreateZones extends Step implements InstallStep
{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        $this->timer->start();

        // Default permission is Allow, only need to set the denies

        // Insert Unchartered Zone
        $zone = new Zone();
        $zone->name = 'Unchartered space';
        $zone->save();

        $this->logger->info(__('create_universe.l_cu_setup_unchartered', ['elapsed' => $this->timer->sample()]));

        // Insert Federation Zone
        $zone = new Zone();
        $zone->name = 'Federation space';
        $zone->allow_beacon = ZonePermission::Deny;
        $zone->allow_attack = ZonePermission::Deny;
        $zone->allow_planetattack = ZonePermission::Deny;
        $zone->allow_warpedit = ZonePermission::Deny;
        $zone->allow_planet = ZonePermission::Deny;
        $zone->allow_defenses = ZonePermission::Deny;
        $zone->max_hull = config('game.max_fed_hull');
        $zone->save();

        $this->logger->info(__('create_universe.l_cu_setup_fedspace', ['elapsed' => $this->timer->sample()]));

        // Insert Free Trade Zone
        $zone = new Zone();
        $zone->name = 'Free-Trade space';
        $zone->allow_beacon = ZonePermission::Deny;
        $zone->allow_planetattack = ZonePermission::Deny;
        $zone->allow_warpedit = ZonePermission::Deny;
        $zone->allow_planet = ZonePermission::Deny;
        $zone->allow_defenses = ZonePermission::Deny;
        $zone->save();

        $this->logger->info(__('create_universe.l_cu_setup_free', ['elapsed' => $this->timer->sample()]));

        // Insert War Zone
        $zone = new Zone();
        $zone->name = 'War Zone';
        $zone->allow_trade = ZonePermission::Deny;
        $zone->save();

        $this->logger->info(__('create_universe.l_cu_setup_warzone', ['elapsed' => $this->timer->sample()]));

        return 0;
    }
}