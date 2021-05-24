<?php
/**
 * default of all define
 */

// Define Global variables ****************************************************
// Core name
define('core_name', 'dash');


// Define Dash variables ****************************************************
$dir = __DIR__;
$dir = str_replace('\\', '/', $dir);
$dir = str_replace('/lib/engine', '', $dir);
define("core", preg_replace("[\\\\]", "/", $dir).'/' );


// Dash library
define("lib", "lib/");

// set include path for lib
// Dash plugin
define("addons", core."addons/");

// Define Project variables ***************************************************
define("root", dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/' );


set_include_path(get_include_path() . PATH_SEPARATOR . root.'includes/');
set_include_path(get_include_path() . PATH_SEPARATOR . core.'addons/');
set_include_path(get_include_path() . PATH_SEPARATOR . core);



// Project database
define("database", root.'includes/database/');

// Set default timezone to Asia/Tehran, Please set timezone in your php.ini
if(defined("timezone"))
{
	date_default_timezone_set(constant('timezone'));
}
else
{
	date_default_timezone_set('Asia/Tehran');
}


// if has subdomain and have private database for subdomain, set db
if(isset($_SERVER['HTTP_HOST']))
{
	$subdomain       = null;
	$urlHostSegments = explode('.', $_SERVER['HTTP_HOST']);
	// if have subdomain
    if(count($urlHostSegments) > 2)
    {
		$subdomain    = $urlHostSegments[0];
		$subdomain_db = root.'customer/subdomain/'. $subdomain;
		// if file of special database exist
		if(file_exists($subdomain_db))
		{
			$private_db = trim(file_get_contents($subdomain_db));
			if(!defined('db_name'))
			{
				define("db_name", $private_db);
			}
		}
    }
}

// if personal config exist, require it
if(file_exists(root .'config.me.php'))
{
	require_once(root .'config.me.php');
}
else
{
	// A config file doesn't exist
	// echo("<p>There doesn't seem to be a <code>config.php</code> file. I need this before we can get started.</p>");
}

// define a to check array and
function a()
{
	return \dash\get::index(...func_get_args());
}


?>