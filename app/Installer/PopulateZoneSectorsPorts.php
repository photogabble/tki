<?php

namespace Tki\Installer;

// Second half of 60.php
use Tki\Models\Universe;
use Tki\Models\Zone;
use Illuminate\Console\OutputStyle;

class PopulateZoneSectorsPorts extends Step implements InstallStep{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        // Initial Sell Limits?
        $initsore = config('game.ore_limit') * $config->initialCommoditiesSellPercentage / 100.0;
        $initsorganics = config('game.organics_limit') * $config->initialCommoditiesSellPercentage / 100.0;
        $initsgoods = config('game.goods_limit') * $config->initialCommoditiesSellPercentage / 100.0;
        $initsenergy = config('game.energy_limit') * $config->initialCommoditiesSellPercentage / 100.0;

        // Initial Buy Limits?
        $initbore = config('game.ore_limit') * $config->initialCommoditiesBuyPercentage / 100.0;
        $initborganics = config('game.organics_limit') * $config->initialCommoditiesBuyPercentage / 100.0;
        $initbgoods = config('game.goods_limit') * $config->initialCommoditiesBuyPercentage / 100.0;
        $initbenergy = config('game.energy_limit') * $config->initialCommoditiesBuyPercentage / 100.0;


        $this->timer->start();

        // Assign Fed Sectors, this will also include Sol and Alpha Centauri
        Universe::query()
            ->where('id', '<=', $config->federationSectors)
            ->update([
                'zone_id' => 2
            ]);

        $output->writeln(__('create_universe.l_cu_setup_fed_sectors', ['elapsed' => $this->timer->sample()]));

        // Insert special ports
        Universe::query()
            ->inRandomOrder()
            ->where('port_type', 'none')
            ->where('zone_id', 1) // Only swap out unexplored space
            ->limit($config->specialPorts - 1) // -1 for Sol
            ->update([
                'zone_id' => 3,
                'port_type' => 'special'
            ]);

        $output->writeln(__('create_universe.l_cu_setup_special_ports', ['elapsed' => $this->timer->sample()]));

        // Insert Ore Ports
        Universe::query()
            ->inRandomOrder()
            ->where('port_type', 'none')
            ->where('zone_id', 1) // Only swap out unexplored space
            ->limit($config->orePorts)
            ->update([
                'port_type' => 'ore',
                'port_ore' => $initsore,
                'port_organics' => $initborganics,
                'port_goods' => $initbgoods,
                'port_energy' => $initbenergy,
            ]);

        $output->writeln(__('create_universe.l_cu_setup_ore_ports', ['elapsed' => $this->timer->sample()]));

        // Insert organics ports
        Universe::query()
            ->inRandomOrder()
            ->where('port_type', 'none')
            ->where('zone_id', 1) // Only swap out unexplored space
            ->limit($config->organicPorts)
            ->update([
                'port_type' => 'organics',
                'port_ore' => $initbore,
                'port_organics' => $initsorganics,
                'port_goods' => $initbgoods,
                'port_energy' => $initbenergy,
            ]);

        $output->writeln(__('create_universe.l_cu_setup_organics_ports', ['elapsed' => $this->timer->sample()]));


        // Insert goods ports
        Universe::query()
            ->inRandomOrder()
            ->where('port_type', 'none')
            ->where('zone_id', 1) // Only swap out unexplored space
            ->limit($config->goodsPorts)
            ->update([
                'port_type' => 'goods',
                'port_ore' => $initbore,
                'port_organics' => $initborganics,
                'port_goods' => $initsgoods,
                'port_energy' => $initbenergy,
            ]);

        $output->writeln(__('create_universe.l_cu_setup_goods_ports', ['elapsed' => $this->timer->sample()]));

        // Insert energy ports
        Universe::query()
            ->inRandomOrder()
            ->where('port_type', 'none')
            ->where('zone_id', 1) // Only swap out unexplored space
            ->limit($config->energyPorts - 1) // -1 for Alpha Centauri
            ->update([
                'port_type' => 'energy',
                'port_ore' => $initbore,
                'port_organics' => $initborganics,
                'port_goods' => $initsgoods,
                'port_energy' => $initsenergy,
            ]);

        $output->writeln(__('create_universe.l_cu_setup_energy_ports', ['elapsed' => $this->timer->sample()]));

        return 0;
    }
}