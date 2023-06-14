<?php

namespace App\Console\Commands;

use App\Installer\CreateAdminAccount;
use App\Installer\CreateNews;
use App\Installer\CreatePlanets;
use App\Installer\CreateSchedulers;
use App\Installer\CreateSectors;
use App\Installer\CreateZones;
use App\Installer\InstallConfig;
use App\Installer\InstallStep;
use Illuminate\Console\Command;

class BigBang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:big-bang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TKI Command Line Installer';

    private InstallConfig $installConfig;

    public function __construct()
    {
        $this->installConfig = new InstallConfig();
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // TODO: Identify available languages and ask user to select one:

        $lang = $this->choice('Please select your language', [
            'en' => 'English',
            'de' => 'German'
        ]);

        app()->setLocale($lang);

        $this->line(__('create_universe.l_cu_welcome'));
        $this->line(__('create_universe.l_cu_allow_create'));

        // Configuration
        if ($this->configureInstall() !== 0) return 1;

        // Fresh Migration
        if (!$this->confirm(__('create_universe.l_cu_table_drop_warn'))) return 1;
        $this->call('migrate:fresh');

        // Store Max Sectors Value
        // TODO: Write DB loaded config over-rides

        /** @var InstallStep[] $stages */
        $stages = [
            CreateSectors::class,       // 60
            CreateZones::class,         // 65
            CreatePlanets::class,       // 70
            CreateSchedulers::class,    // 80
            CreateNews::class,          // 90
            CreateAdminAccount::class   // 100
        ];

        foreach($stages as $stage) {
            (new $stage)->execute($this->output, $this->installConfig);
        }

        $this->line(__('create_universe.l_cu_congrats_success'));

        return 0;
    }

    private function configureInstall(): int {
        // Ports
        $this->line(__('create_universe.l_cu_base_n_planets'));

        // Default values
        $special = 1;
        $ore = 15;
        $organics = 10;
        $goods = 15;
        $energy = 10;

        $this->installConfig->initialCommoditiesSellPercentage = 100;
        $this->installConfig->initialCommoditiesBuyPercentage = 100;
        $this->installConfig->federationSectors = 5;
        $this->installConfig->loops = 2;
        $planets = 10;
        $autorun = false;

        while (true) {
            $empty = 100;

            $special = $this->askBetween(__('create_universe.l_cu_percent_special'), 0, 100, $special);
            $empty -= $special;

            $ore = $this->askBetween(__('create_universe.l_cu_percent_ore'), 0, $empty, $ore);
            $empty -= $ore;

            $organics = $this->askBetween(__('create_universe.l_cu_percent_organics'), 0, $empty, $organics);
            $empty -= $organics;

            $goods = $this->askBetween(__('create_universe.l_cu_percent_goods'), 0, $empty, $goods);
            $empty -= $goods;

            $energy = $this->askBetween(__('create_universe.l_cu_percent_energy'), 0, $empty, $energy);
            $empty -= $energy;

            $this->line(__('create_universe.l_cu_percent_empty', ['empty' => $empty]));

            // Commodities

            $this->installConfig->initialCommoditiesSellPercentage = $this->askBetween(__('create_universe.l_cu_init_comm_sell'), 0, 100, $this->installConfig->initialCommoditiesSellPercentage);
            $this->installConfig->initialCommoditiesBuyPercentage = $this->askBetween(__('create_universe.l_cu_init_comm_buy'), 0, 100, $this->installConfig->initialCommoditiesBuyPercentage);

            // Sector & link setup

            $this->line(__('create_universe.l_cu_sector_n_link'));

            $this->installConfig->maxSectors = $this->askBetween(__('create_universe.l_cu_sector_total'), 0, 10000, config('game.max_sectors'));

            $this->installConfig->federationSectors = $this->askBetween(__('create_universe.l_cu_fed_sectors'), 0, $this->installConfig->maxSectors, $this->installConfig->federationSectors); // TODO: no need for l_cu_fedsec_smaller with $this->maxSectors here
            $this->installConfig->loops = $this->askBetween(__('create_universe.l_cu_num_loops'), 0, 100, $this->installConfig->loops); // TODO: discover value purpose and update min/max
            $planets = $this->askBetween(__('create_universe.l_cu_percent_unowned'), 0, 100, $planets);

            // Unsure what $autorun does...

            // $this->line(__('create_universe.l_cu_autorun'));

            // Confirm Values

            $this->installConfig->specialPorts = round($this->installConfig->maxSectors * $special / 100);
            $this->installConfig->orePorts = round($this->installConfig->maxSectors * $ore / 100);
            $this->installConfig->organicPorts = round($this->installConfig->maxSectors * $organics / 100);
            $this->installConfig->goodsPorts = round($this->installConfig->maxSectors * $goods / 100);
            $this->installConfig->energyPorts = round($this->installConfig->maxSectors * $energy / 100);

            $this->installConfig->unownedPlanets = round($this->installConfig->maxSectors * $planets / 100);

            $this->installConfig->emptySectors = $this->installConfig->maxSectors - $this->installConfig->specialPorts - $this->installConfig->orePorts - $this->installConfig->organicPorts - $this->installConfig->goodsPorts - $this->installConfig->energyPorts;

            $this->table(['Setting', 'Value'], [
                [__('create_universe.l_cu_special_ports'), $this->installConfig->specialPorts],
                [__('create_universe.l_cu_ore_ports'), $this->installConfig->orePorts],
                [__('create_universe.l_cu_organics_ports'), $this->installConfig->organicPorts],
                [__('create_universe.l_cu_goods_ports'), $this->installConfig->goodsPorts],
                [__('create_universe.l_cu_energy_ports'), $this->installConfig->energyPorts],
                [__('create_universe.l_cu_init_comm_sell'), $this->installConfig->initialCommoditiesSellPercentage],
                [__('create_universe.l_cu_init_comm_buy'), $this->installConfig->initialCommoditiesBuyPercentage],
                [__('create_universe.l_cu_empty_sectors'), $this->installConfig->emptySectors],
                [__('create_universe.l_cu_fed_sectors'), $this->installConfig->federationSectors],
                [__('create_universe.l_cu_loops'), $this->installConfig->loops],
                [__('create_universe.l_cu_unowned_planets'), $this->installConfig->unownedPlanets],
            ]);

            if ($this->confirm(__('create_universe.l_cu_confirm_settings', ['max_sectors' => $this->installConfig->maxSectors,]))) {
                return 0;
            }
        }
    }

    private function askBetween(string $ask, int $min, int $max, int $default): int
    {
        while(true) {
            $value = $this->ask("$ask [$min-$max]", $default);
            if ($value >= $min && $value <= $max) return $value;
            $this->line("<error>[!]</error> Invalid input, please input a number between $min and $max");
        }
    }
}
