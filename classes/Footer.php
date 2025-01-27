<?php declare(strict_types = 1);
/**
 * classes/Footer.php from The Kabal Invasion.
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

// FUTURE: This file should only be used when we have not converted a file to use templates.
// Once they use templates, the footer will be loaded correctly by layout.tpl

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

class Footer
{
    public function display(\PDO $pdo_db, string $lang, Registry $tkireg, Timer $tkitimer, Smarty $template): void
    {
        $request = Request::createFromGlobals();
        $langvars = Translate::load($pdo_db, $lang, array('footer'));
        $online = 0;
        if (Db::isActive($pdo_db))
        {
            $cur_time_stamp = date("Y-m-d H:i:s", time()); // Now (as seen by PHP)
            $since_stamp = date("Y-m-d H:i:s", time() - 5 * 60); // Five minutes ago
            $players_gateway = new Players\PlayersGateway($pdo_db);

            // Online is the (int) count of the numbers of players currently logged in via SQL select
            $online = (int) $players_gateway->selectPlayersLoggedIn($since_stamp, $cur_time_stamp);
        }

        $tkitimer->stop();

        // Suppress the news ticker on the IBANK and index pages
        $news_ticker_active = ((bool) preg_match("/index.php/i", (string) $request->server->get('SCRIPT_NAME')) || (bool) preg_match("/ibank.php/i", (string) $request->server->get('SCRIPT_NAME')) || (bool) preg_match("/new.php/i", (string) $request->server->get('SCRIPT_NAME')));

        // Suppress the news ticker if the database is not active
        if (!Db::isActive($pdo_db))
        {
            $news_ticker_active = false;
        }

        // Update counter
        $scheduler_gateway = new Scheduler\SchedulerGateway($pdo_db);

        // Last run is the (int) count of the numbers of players currently
        // logged in via SQL select or false if DB is not active
        $last_run = $scheduler_gateway->selectSchedulerLastRun();
        if ($last_run !== null)
        {
            $seconds_left = ($tkireg->sched_ticks * 60) - (time() - $last_run);
            $show_update_ticker = true;
        }
        else
        {
            $seconds_left = 0;
            $show_update_ticker = false;
        }

        // End update counter

        if ($news_ticker_active === true)
        {
            // Database driven language entries
            $langvars_temp = Translate::load($pdo_db, $lang, array('common',
                                             'footer', 'insignias',
                                             'logout', 'news'));
            // Use array merge so that we do not clobber the langvars array,
            // and only add to it the items needed for footer
            $langvars = array_merge($langvars, $langvars_temp);

            // Use array unique so that we don't end up with duplicate lang array entries
            // This is resulting in an array with blank values for specific keys,
            // so array_unique isn't entirely what we want
            // $langvars = array_unique ($langvars);

            // SQL call that selects all of the news items between the start date beginning of day, and the end of day.
            $news_gateway = new News\NewsGateway($pdo_db);
            $row = $news_gateway->selectNewsByDay(date('Y-m-d'));
            // Future: Handle bad row return, as it's causing issues for count($row)

            $news_ticker = array();
            if ($row === null)
            {
                array_push($news_ticker, array('url' => null,
                                               'text' => $langvars['l_news_none'],
                                               'type' => null,
                                               'delay' => 5));
            }
            else
            {
                foreach ($row as $item)
                {
                    array_push($news_ticker, array('url' => "news.php",
                                                   'text' => $item['headline'],
                                                   'type' => $item['news_type'],
                                                   'delay' => 5));
                }

                array_push($news_ticker, array('url' => null, 'text' => "End of News", 'type' => null, 'delay' => 5));
            }

            $template->assign('news', $news_ticker);
        }

        $mem_peak_usage = floor(memory_get_peak_usage() / 1024);
        $public_pages = array('ranking.php', 'new.php', 'faq.php', 'settings.php', 'news.php', 'index.php');
        $slash_position = strrpos($request->server->get('SCRIPT_NAME'), '/');
        $slash_position = (int) $slash_position + 1;
        $current_page = substr($request->server->get('SCRIPT_NAME'), $slash_position);
        if (in_array($current_page, $public_pages, true))
        {
            // If it is a non-login required page, such as ranking, new, faq,
            // settings, news, and index use the public SF logo, which increases project stats.
            $template->assign('suppress_logo', false);
        }
        else
        {
            // Else suppress the logo, so it is as fast as possible.
            $template->assign('suppress_logo', true);
        }

        // Set array with all used variables in page
        $template->assign('update_ticker', array("display" => $show_update_ticker,
                                           "seconds_left" => $seconds_left,
                                           "sched_ticks" => $tkireg->sched_ticks));
        $template->assign('players_online', $online);
        $template->assign('elapsed', $tkitimer->elapsed());
        $template->assign('mem_peak_usage', $mem_peak_usage);
        $template->assign('footer_show_debug', $tkireg->footer_show_debug);
        $template->assign('cur_year', date('Y'));
        $template->assign('langvars', $langvars);
        $template->display('footer.tpl');
    }
}
