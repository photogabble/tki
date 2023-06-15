<?php

namespace Tki\Installer;

// 70.php
use Tki\Models\Planet;
use Tki\Models\Universe;
use Tki\Models\Zone;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreatePlanets extends Step implements InstallStep
{
    /**
     * @throws \Exception
     */
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        $this->timer->start();

        // Get the sectors belonging to zones that allows planets
        /** @var Universe[]|Collection<Universe> $sectors */
        $sectors = Universe::query()
            ->inRandomOrder()
            ->join('zones', 'zone_id', '=', 'zones.id')
            ->where('zones.allow_planet', true)
            ->select('universes.*')
            ->get();


        $added = 0;
        $populatedSectors = 0;
        $default_prod_ore = config('game.default_prod_ore');
        $default_prod_organics = config('game.default_prod_organics');
        $default_prod_goods = config('game.default_prod_goods');
        $default_prod_energy = config('game.default_prod_energy');
        $default_prod_fighters = config('game.default_prod_fighters');
        $default_prod_torp = config('game.default_prod_torp');

        // Insert all planets within one transaction
        DB::beginTransaction();

        while ($added < $config->unownedPlanets) {
            /** @var Universe $sector */
            if (!$sector = $sectors->pop()) break; // Run out of sectors... this shouldn't happen but this break stops the infinite loop
            $adding = random_int(1, config('game.max_planets_sector'));

            // Ensure we don't add more than the total amount needed, if we are on the last loop, add enough
            // to complete the list.
            if ($adding + $added > $config->unownedPlanets) $adding = $config->unownedPlanets - $added;

            for ($i = 0; $i < $adding; $i++) {
                $planet = new Planet();
                $planet->colonists = 2;
                $planet->owner_id = null; // default
                $planet->team_id = null; // default
                $planet->prod_ore = $default_prod_ore;
                $planet->prod_organics = $default_prod_organics;
                $planet->prod_goods = $default_prod_goods;
                $planet->prod_energy = $default_prod_energy;
                $planet->prod_fighters = $default_prod_fighters;
                $planet->prod_torp = $default_prod_torp;
                $planet->sector_id = $sector->id;
                $planet->save();

                $added++;
            }

            $populatedSectors++;
        }

        DB::commit();

        $output->writeln(__('create_universe.l_cu_setup_unowned_planets', ['elapsed' => $this->timer->sample(), 'nump' => $populatedSectors]));

        return 0;
    }
}