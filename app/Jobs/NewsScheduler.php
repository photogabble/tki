<?php declare(strict_types = 1);
/**
 * scheduler/sched_news.php from The Kabal Invasion.
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

namespace Tki\Jobs;

use Tki\Models\News;
use Tki\Models\Planet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsScheduler extends ScheduledTask
{
    public function periodMinutes(): int
    {
        return 15;
    }

    public function maxCatchup(): int
    {
        return 1; // Only need to run once
    }

    protected function run(): void
    {
        Log::info(__('scheduler.l_sched_news_title'));

        // TODO: ships should be users...
        /** @var Planet $playerPlanets */
        $playerPlanets = Planet::query()
            ->join('ships', 'owner_id', '=', 'ships.id')
            ->whereNotNull('owner_id')
            ->groupBy('owner_id')
            ->select([
                DB::raw('IF(COUNT(*)>0,SUM(colonists), 0) AS total_colonists'),
                DB::raw('COUNT(owner) AS total_planets'),
                'owner_id',
                'ships.character_name'
            ]);

        foreach ($playerPlanets as $planet) {
            Log::info(__('scheduler.l_sched_news_processing',[
                'name' => $planet->character_name,
                'owner' => $planet->owner_id,
                'planet_row' => number_format($planet->total_planets),
                'colonists_row' => number_format($planet->total_colonists)
            ]));

            // Generation of planet amount

            if ($planet->total_planets >= 1000 && !News::alreadyPublished($planet->owner_id, 'planet1000')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 1000 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text1000', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet1000'
                ]);
            } else if ($planet->total_planets >= 500 && !News::alreadyPublished($planet->owner_id, 'planet500')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 500 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text500', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet500'
                ]);
            } else if ($planet->total_planets >= 250 && !News::alreadyPublished($planet->owner_id, 'planet250')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 250 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text250', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet250'
                ]);
            } else if ($planet->total_planets >= 100 && !News::alreadyPublished($planet->owner_id, 'planet100')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 100 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text100', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet100'
                ]);
            } else if ($planet->total_planets >= 50 && !News::alreadyPublished($planet->owner_id, 'planet50')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 50 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text50', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet50'
                ]);
            } else if ($planet->total_planets >= 25 && !News::alreadyPublished($planet->owner_id, 'planet25')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 25 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text25', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet25'
                ]);
            } else if ($planet->total_planets >= 10 && !News::alreadyPublished($planet->owner_id, 'planet10')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 10 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text10', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet10'
                ]);
            } else if ($planet->total_planets >= 5 && !News::alreadyPublished($planet->owner_id, 'planet5')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 10 ' . __('news.l_news_planets'),
                    'body' => __('news.l_news_p_text5', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'planet5'
                ]);
            }

            // Generation of colonist amount

            if ($planet->total_colonists >= 1000000000 && !News::alreadyPublished($planet->owner_id, 'col1000')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 1000 ' . __('news.l_news_cols'),
                    'body' => __('news.l_news_c_text1000', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'col1000'
                ]);
            } else if ($planet->total_colonists >= 500000000 && !News::alreadyPublished($planet->owner_id, 'col500')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 500 ' . __('news.l_news_cols'),
                    'body' => __('news.l_news_c_text500', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'col500'
                ]);
            } else if ($planet->total_colonists >= 100000000 && !News::alreadyPublished($planet->owner_id, 'col100')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 100 ' . __('news.l_news_cols'),
                    'body' => __('news.l_news_c_text100', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'col100'
                ]);
            } else if ($planet->total_colonists >= 25000000 && !News::alreadyPublished($planet->owner_id, 'col25')) {
                News::create([
                    'headline' => __('news.l_news_p_headline', ['player' => $planet->character_name]) . ' 25 ' . __('news.l_news_cols'),
                    'body' => __('news.l_news_c_text25', ['name' => $planet->character_name]),
                    'user_id' => $planet->owner_id,
                    'type' => 'col25'
                ]);
            }
        }

        Log::info(__('scheduler.l_sched_news_end'));
    }
}
