<?php
// The Kabal Invasion - A web-based 4X space game
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: common.php

if (strpos($_SERVER['PHP_SELF'], 'common.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

if (!extension_loaded('mbstring')) // Test to ensure mbstring extension is loaded
{
    die ('The Kabal Invasion - General error: The PHP mbstring extension is required. Please install it.');
}

require_once './vendor/autoload.php';              // Load the auto-loader
require_once './global_defines.php';               // Defines used in many places
mb_http_output('UTF-8');                           // Our output should be served in UTF-8 no matter what.
mb_internal_encoding('UTF-8');                     // We are explicitly UTF-8, with Unicode language variables.
ini_set('include_path', '.');                      // Set include path to avoid issues on a few platforms
ini_set('session.use_strict_mode', 1);             // Ensure that PHP will not accept uninitialized session ID
ini_set('session.use_only_cookies', 1);            // Ensure that sessions will only be stored in a cookie
ini_set('session.cookie_httponly', 1);             // Ensure that javascript cannot tamper with session cookies
ini_set('session.use_trans_sid', 0);               // Prevent session ID from being put in URLs
ini_set('session.entropy_file', '/dev/urandom');   // Use urandom as entropy source, to increase randomness
ini_set('session.entropy_length', '512');          // Increase the length of entropy gathered
ini_set('session.hash_function', 'sha512');        // Provides improved reduction for session collision
ini_set('session.hash_bits_per_character', 5);     // Explicitly set the number of bits per character of the hash
ini_set('url_rewriter.tags', '');                  // Do not pass Session id on the url for improved security on login
ini_set('default_charset', 'utf-8');               // Set PHP's default character set to utf-8

if (file_exists('dev'))                            // Create/touch a file named dev to activate development mode
{
    ini_set('error_reporting', -1);                // During development, output all errors, even notices
    ini_set('display_errors', 1);                  // During development, display all errors
}
else
{
    ini_set('error_reporting', 0);                 // Do not report errors
    ini_set('display_errors', 0);                  // Do not display errors
}

session_name('tki_session');                       // Change the default to defend better against session hijacking
date_default_timezone_set('UTC');                  // Set to your server's local time zone - Avoid a PHP notice
                                                   // Since header is now temlate driven, these weren't being passed
                                                   // along except on old crusty pages. Now everthing gets them!
header('Content-type: text/html; charset=utf-8');  // Set character set to utf-8, and using HTML as our content type
header('X-UA-Compatible: IE=Edge, chrome=1');      // IE - use the latest rendering engine (edge), and chrome shell
header('Cache-Control: public');                   // Tell browser and caches that it's ok to store in public caches
header('Connection: Keep-Alive');                  // Tell browser to keep going until it gets all data, please
header('Vary: Accept-Encoding, Accept-Language');  // Tell CDN's or proxies to keep a separate version of the page in
                                                   // various encodings - compressed or not, in english or french
                                                   // for example.
header('Keep-Alive: timeout=15, max=100');         // Ask for persistent HTTP connections (15sec), which give better
                                                   // per-client performance, but can be worse (for a server) for many
ob_start(array('Tki\Compress', 'compress'));       // Start a buffer, and when it closes (at the end of a request),
                                                   // call the callback function 'Tki\Compress' to properly handle
                                                   // detection of compression.

$pdo_db = new Tki\Db;
$pdo_db = $pdo_db->initDb('pdo');                  // Connect to db using pdo
$db = new Tki\Db;
$db = $db->initDb('adodb');                        // Connect to db using adodb also - for now - to be eliminated!

if ($pdo_db !== null)
{
    $tkireg = new Tki\Reg($pdo_db);                // TKI Registry object -  passing config variables via classes
    $tkireg->tkitimer = new Tki\Timer;             // Create a benchmark timer to get benchmarking data for everything
    $tkireg->tkitimer->start();                    // Start benchmarking immediately
}

$langvars = null;                                  // Language variables in every page, set them to a null value first
$template = new \Tki\Smarty();
$template->setTheme($tkireg->default_template);

if ($pdo_db !== null && Tki\Db::isActive($pdo_db))
{
    $tki_session = new Tki\Sessions($pdo_db);
    session_start();
}

$lang = $tkireg->default_lang;
