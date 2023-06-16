<?php declare(strict_types = 1);
/**
 * classes/Compress.php from The Kabal Invasion.
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

use Symfony\Component\HttpFoundation\Request;

/**
 * @todo delete this file
 * @deprecated
 */
class Compress
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    public static function compress(string $output): string
    {
        $request = Request::createFromGlobals();

        // Handle the supported compressions.
        $supported_enc = array();
        if (!is_null($request->headers->get('HTTP_ACCEPT_ENCODING')))
        {
            $supported_enc = explode(',', $request->headers->get('HTTP_ACCEPT_ENCODING'));
        }

        if (in_array('gzip', $supported_enc, true) === true)
        {
            header('Vary: Accept-Encoding');
            header('Content-Encoding: gzip');
            $encoded_output = gzencode($output, 9);
            return (string) $encoded_output;
        }
        elseif (in_array('deflate', $supported_enc, true) === true)
        {
            header('Vary: Accept-Encoding');
            header('Content-Encoding: deflate');
            $deflated_output = gzdeflate($output, 9);
            return (string) $deflated_output;
        }
        else
        {
            return $output;
        }
    }
}
