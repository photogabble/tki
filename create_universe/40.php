<?php declare(strict_types = 1);
/**
 * create_univserse/40.php from The Kabal Invasion.
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

// Determine current step, next step, and number of steps
$step_finder = new Tki\BigBang();
$create_universe_info = $step_finder->findStep(__FILE__);

// Set variables
$variables = array();
$variables['templateset']            = $tkireg->default_template;
$variables['body_class']             = 'create_universe';
$variables['steps']                  = $create_universe_info['steps'];
$variables['current_step']           = $create_universe_info['current_step'];
$variables['next_step']              = $create_universe_info['next_step'];
$variables['max_sectors']            = (int) filter_input(INPUT_POST, 'max_sectors', FILTER_SANITIZE_NUMBER_INT); // Sanitize the input
$variables['spp']                    = filter_input(INPUT_POST, 'spp', FILTER_SANITIZE_NUMBER_INT);
$variables['oep']                    = filter_input(INPUT_POST, 'oep', FILTER_SANITIZE_NUMBER_INT);
$variables['ogp']                    = filter_input(INPUT_POST, 'ogp', FILTER_SANITIZE_NUMBER_INT);
$variables['gop']                    = filter_input(INPUT_POST, 'gop', FILTER_SANITIZE_NUMBER_INT);
$variables['enp']                    = filter_input(INPUT_POST, 'enp', FILTER_SANITIZE_NUMBER_INT);
$variables['nump']                   = filter_input(INPUT_POST, 'nump', FILTER_SANITIZE_NUMBER_INT);
$variables['empty']                  = $variables['max_sectors'] - $variables['spp'] - $variables['oep'] - $variables['ogp'] - $variables['gop'] - $variables['enp'];
$variables['initscommod']            = filter_input(INPUT_POST, 'initscommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['initbcommod']            = filter_input(INPUT_POST, 'initbcommod', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$variables['fedsecs']                = filter_input(INPUT_POST, 'fedsecs', FILTER_SANITIZE_NUMBER_INT);
$variables['loops']                  = filter_input(INPUT_POST, 'loops', FILTER_SANITIZE_NUMBER_INT);
$variables['swordfish']              = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
$tki_schema = new Tki\Schema();

if (\Tki\SecureConfig::DB_TYPE == 'postgres9')
{
    $variables['create_seq_results'] = $tki_schema->createSequences($pdo_db, \Tki\SecureConfig::DB_TABLE_PREFIX, \Tki\SecureConfig::DB_TYPE); // Create all tables in the database
    if ($variables['create_seq_results'] !== null)
    {
        $variables['create_seq_count']   = count($variables['create_seq_results']) - 1;
        $create_array_size               = count($variables['create_seq_results']);
    }
    else
    {
        $variables['create_seq_results'] = array(); // Create all tables in the database
        $variables['create_seq_count']   = 0;
        $create_array_size               = 0;
    }
}
else
{
    $variables['create_seq_results'] = array(); // Create all tables in the database
    $variables['create_seq_count']   = 0;
    $create_array_size               = 0;
}

$variables['create_tables_results']  = $tki_schema->createTables($pdo_db, \Tki\SecureConfig::DB_TABLE_PREFIX, \Tki\SecureConfig::DB_TYPE); // Create all tables in the database
$variables['create_tables_count']    = count($variables['create_tables_results']) - 1;
$variables['autorun']                = filter_input(INPUT_POST, 'autorun', FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

for ($i = 0; $i < $create_array_size; $i++)
{
    if ($variables['create_seq_results'][$i]['result'] !== true)
    {
        $variables['autorun'] = false; // We disable autorun if any errors occur in processing
    }
}

$create_array_size = count($variables['create_tables_results']);
for ($i = 0; $i < $create_array_size; $i++)
{
    if ($variables['create_tables_results'][$i]['result'] !== true)
    {
        $variables['autorun'] = false; // We disable autorun if any errors occur in processing
    }
}

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common',
                                'create_universe', 'footer', 'insignias',
                                'news', 'regional'));
$variables['title'] = $langvars['l_cu_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('langvars', $langvars);
$template->addVariables('variables', $variables);
$template->display('templates/classic/create_universe/40.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
