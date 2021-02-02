<?php declare(strict_types = 1);
/**
 * classes/Reg.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tki;

/**
 * Registry properties to help with type-hinting.
 * These are dynamically generated by database calls
 *
 * @property-read bool $game_closed
 * @property-read string $game_name
 * @property-read bool $game_closed
 * @property-read bool $account_creation_closed
 * @property-read string $release_version
 * @property-read string $admin_mail
 * @property-read string $email_server
 * @property-read int $turns_per_tick
 * @property-read int $sched_ticks
 * @property-read int $sched_turns
 * @property-read int $sched_ports
 * @property-read int $sched_planets
 * @property-read int $sched_ibank
 * @property-read int $sched_ranking
 * @property-read int $sched_news
 * @property-read int $sched_degrade
 * @property-read int $sched_apocalypse
 * @property-read int $sched_thegovernor
 * @property-read int $doomsday_value
 * @property-read int $max_turns
 * @property-read bool $allow_fullscan
 * @property-read bool $allow_navcomp
 * @property-read bool $allow_ibank
 * @property-read bool $allow_genesis_destroy
 * @property-read bool $allow_sofa
 * @property-read bool $allow_ksm
 * @property-read float $ibank_interest
 * @property-read float $ibank_paymentfee
 * @property-read float $ibank_loaninterest
 * @property-read float $ibank_loanfactor
 * @property-read float $ibank_loanlimit
 * @property-read float $ibank_min_turns
 * @property-read float $ibank_svalue
 * @property-read float $ibank_trate
 * @property-read float $ibank_lrate
 * @property-read float $ibank_tconsolidate
 * @property-read float $default_prod_ore
 * @property-read float $default_prod_organics
 * @property-read float $default_prod_goods
 * @property-read float $default_prod_energy
 * @property-read float $default_prod_fighters
 * @property-read float $default_prod_torp
 * @property-read int $ore_price
 * @property-read int $ore_delta
 * @property-read int $ore_rate
 * @property-read float $ore_prate
 * @property-read int $ore_limit
 * @property-read int $organics_price
 * @property-read int $organics_delta
 * @property-read int $organics_rate
 * @property-read float $organics_prate
 * @property-read int $organics_limit
 * @property-read int $goods_price
 * @property-read int $goods_delta
 * @property-read int $goods_rate
 * @property-read float $goods_prate
 * @property-read int $goods_limit
 * @property-read int $energy_price
 * @property-read int $energy_delta
 * @property-read int $energy_rate
 * @property-read float $energy_prate
 * @property-read int $energy_limit
 * @property-read int $dev_genesis_price
 * @property-read int $dev_beacon_price
 * @property-read int $dev_emerwarp_price
 * @property-read int $dev_warpedit_price
 * @property-read int $dev_minedeflector_price
 * @property-read int $dev_escapepod_price
 * @property-read int $dev_fuelscoop_price
 * @property-read int $dev_lssd_price
 * @property-read int $armor_price
 * @property-read int $fighter_price
 * @property-read int $torpedo_price
 * @property-read int $colonist_price
 * @property-read int $torp_dmg_rate
 * @property-read int $max_emerwap
 * @property-read float $fighter_prate
 * @property-read float $torpedo_prate
 * @property-read float $credits_prate
 * @property-read float $colonist_production_rate
 * @property-read float $colonist_reproduction_rate
 * @property-read float $interest_rate
 * @property-read int $base_ore
 * @property-read int $base_goods
 * @property-read int $base_organics
 * @property-read int $base_credits
 * @property-read string $color_header
 * @property-read string $color_line1
 * @property-read string $color_line2
 * @property-read bool $newbie_nice
 * @property-read int $newbie_hull
 * @property-read int $newbie_engines
 * @property-read int $newbie_power
 * @property-read int $newbie_computer
 * @property-read int $newbie_sensors
 * @property-read int $newbie_armor
 * @property-read int $newbie_shields
 * @property-read int $newbie_beams
 * @property-read int $newbie_torp_launchers
 * @property-read int $newbie_cloak
 * @property-read int $upgrade_cost
 * @property-read int $upgrade_factor
 * @property-read float $level_factor
 * @property-read int $inventory_factor
 * @property-read float $max_bountyvalue
 * @property-read float $bounty_ratio
 * @property-read int $bounty_minturns
 * @property-read int $fullscan_cost
 * @property-read int $scan_error_factor
 * @property-read int $kabal_unemployment
 * @property-read int $kabal_aggression
 * @property-read int $kabal_planets
 * @property-read int $mine_hullsize
 * @property-read int $max_ewdhullsize
 * @property-read int $max_sectors
 * @property-read int $max_links
 * @property-read int $universe_size
 * @property-read int $max_fed_hull
 * @property-read int $max_ranks
 * @property-read float $rating_combat_factor
 * @property-read int $base_defense
 * @property-read int $colonist_limit
 * @property-read float $organics_consumption
 * @property-read float $starvation_death_rate
 * @property-read int $max_planets_sector
 * @property-read int $max_traderoutes_player
 * @property-read int $min_bases_to_own
 * @property-read bool $team_planet_transfers
 * @property-read int $min_value_capture
 * @property-read float $defense_degrade_rate
 * @property-read float $energy_per_fighter
 * @property-read float $space_plague_kills
 * @property-read int $max_credits_without_base
 * @property-read int $port_regenerate
 * @property-read bool $footer_show_debug
 * @property-read bool $sched_planet_valid_credits
 * @property-read int $max_upgrades_devices
 * @property-read int $max_emerwarp
 * @property-read int $max_genesis
 * @property-read int $max_beacons
 * @property-read int $max_warpedit
 * @property-read bool $bounty_all_special
 * @property-read string $link_forums
 * @property-read string $admin_name
 * @property-read string $admin_ship_name
 * @property-read string $admin_zone_name
 * @property-read bool $enable_gravatars
 * @property-read string $default_template
 * @property-read int $max_presets
 * @property-read string $default_lang
 */
class Reg
{
    private array $store = array();

    public function __construct(\PDO $pdo_db)
    {
        if ($this->loadFromDb($pdo_db) === false)
        {
            $this->loadFromIni();
        }
    }

    public function loadFromDb(\PDO $pdo_db): ?bool
    {
        // Get the config_values from the DB - This is a pdo operation
        $stmt = "SELECT name,value,type FROM ::prefix::gameconfig";
        $result = $pdo_db->query($stmt);
        Db::logDbErrors($pdo_db, $stmt, __LINE__, __FILE__);

        if ($result !== false) // Result is "false" during no-db status (fresh install or CU after step4/stop)
        {
            $db_keys = $result->fetchAll();
            Db::logDbErrors($pdo_db, 'fetchAll from gameconfig', __LINE__, __FILE__);

            if (!empty($db_keys))
            {
                foreach ($db_keys as $config_line)
                {
                    settype($config_line['value'], $config_line['type']);
                    $this->store[$config_line['name']] = $config_line['value'];
                }

                return null;
            }
        }

        return false;
    }

    public function loadFromIni(): void
    {
        // Slurp in config variables from the ini file directly
        // This is hard-coded for now, but when we get multiple game support, we may need to change this.
        $ini_keys = parse_ini_file('config/classic_config.ini', true, INI_SCANNER_TYPED);
        if (is_array($ini_keys))
        {
            foreach ($ini_keys as $config_line)
            {
                foreach ($config_line as $key => $value)
                {
                    $this->store[$key] = $value;
                }
            }
        }
    }

    public function __set(string $key, mixed $value): void
    {
        $this->store[$key] = $value;
    }

    public function __get(string $key): mixed
    {
        if (!property_exists($this, $key)) // When the key *does not* exist, return "null".
        {
            $this->store[$key] = null;
        }

        return $this->store[$key];
    }
}
