<?php
namespace addons\content_cp\tools\log;

class controller extends \addons\content_cp\home\controller
{
	public function _route()
	{
		parent::_route();

		$this->showLog();
		$this->get()->ALL();
	}


	public function showLog()
	{
		parent::_permission('cp', 'tools', 'view');
		$exist      = true;
		$output     = '<html>';
		$name       = \lib\utility::get('name');
		$isClear    = \lib\utility::get('clear');
		$isZip      = \lib\utility::get('zip');
		$clearName  = '';
		$clearURL   = '';
		$page       = \lib\utility::get('p') * 100000;
		if($page< 0)
		{
			$page = 0;
		}
		$lenght      = \lib\utility::get('lenght');
		if($lenght< 100000)
		{
			$lenght = 100100;
		}
		$filepath   = '';
		$fileFormat = 'sql';

		// check server software
		$software_loc = '';
		if(isset($_SERVER['SERVER_SOFTWARE']))
		{
			if(strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache') !== false)
			{
				$software_loc = '/var/log/apache2/';
			}
			else
			{
				if(strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'nginx') !== false)
				{
					$software_loc = '/var/log/nginx/';
				}
				else
				{
					var_dump($_SERVER['SERVER_SOFTWARE']);
				}
			}
		}


		switch ($name)
		{
			case '':
				return;
				break;

			case 'accesslog':
				$clearName = 'log_access_bak_' .date("Ymd_His");
				$clearExt  = '.sql';
				$clearURL = database.'log/backup-log/'. $clearName. $clearExt;
				$filepath = $software_loc. 'access.log';
				$lang     = 'sql';
				break;

			case 'errorlog':
				$clearName = 'log_erro_bak_' .date("Ymd_His");
				$clearExt  = '.sql';
				$clearURL = database.'log/backup-log/'. $clearName. $clearExt;
				$filepath = $software_loc. 'error.log';
				$lang     = 'sql';
				break;

			case 'sql':
				$clearName = 'log_bak_' .date("Ymd_His");
				$clearExt  = '.sql';
				$clearURL = database.'log/backup-db/'. $clearName. $clearExt;
				$filepath = database.'log/log.sql';
				$lang     = 'sql';
				break;

			case 'sql_check':
				$clearName = 'log_check_bak_' .date("Ymd_His");
				$clearExt  = '.sql';
				$clearURL = database.'log/backup-db/'. $clearName. $clearExt;
				$filepath = database.'log/log-check.sql';
				$lang     = 'sql';
				break;

			case 'sql_warn':
				$clearName = 'log_warn_bak_' .date("Ymd_His");
				$clearExt  = '.sql';
				$clearURL = database.'log/backup-db/'. $clearName. $clearExt;
				$filepath = database.'log/log-warn.sql';
				$lang     = 'sql';
				break;

			case 'sql_critical':
				$clearName = 'log_critical_bak_' .date("Ymd_His");
				$clearExt  = '.sql';
				$clearURL = database.'log/backup-db/'. $clearName. $clearExt;
				$filepath = database.'log/log-critical.sql';
				$lang     = 'sql';
				break;

			case 'sql_error':
				$clearName = 'error_bak_' .date("Ymd_His");
				$clearExt  = '.sql';
				$clearURL = database.'log/backup-db/'. $clearName. $clearExt;
				$filepath = database.'log/error.sql';
				$lang     = 'sql';
				break;

			case 'telegram':
				$clearName = 'telegram_bak_' .date("Ymd_His");
				$clearExt  = '.json';
				$clearURL = database.'log/backup-tg/'. $clearName. $clearExt;
				$filepath = database.'log/telegram.json';
				$lang     = 'json';
				break;

			case 'telegram_info':
				$clearName = 'telegram_info_bak_' .date("Ymd_His");
				$clearExt  = '.json';
				$clearURL = database.'log/backup-tg/'. $clearName. $clearExt;
				$filepath = database.'log/telegram-info.json';
				$lang     = 'json';
				break;

			case 'telegram_error':
				$clearName = 'telegram_error_bak_' .date("Ymd_His");
				$clearExt  = '.json';
				$clearURL = database.'log/backup-tg/'. $clearName. $clearExt;
				$filepath = database.'log/telegram-error.json';
				$lang     = 'json';
				break;

			default:
				$output .= T_('Do you wanna something here!?');
				break;
		}
		// if wanna clear this file, transfer it to new address and clear it
		if($isClear)
		{
			\lib\utility\file::rename($filepath, $clearURL);
			$this->redirector('?name='. $name)->redirect();
		}
		if($isZip)
		{
			$newZipAddr = database.'log/dl.zip';
			// create zip
			if(\lib\utility\zip::create($filepath, $newZipAddr) === true)
			{
				\lib\utility\zip::download_on_fly($newZipAddr, $clearName);
			}
		}

		// read file data
		$fileData = @file_get_contents($filepath, FILE_USE_INCLUDE_PATH, null, $page, $lenght);
		if($fileData)
		{
			$myURL    = Protocol."://". \lib\router::get_root_domain().'/static';
			$myCommon = Protocol."://ermile.".Tld.'/static/js/common.js';
			$myCode   = Protocol."://code.ermile.".Tld.'/';

			$output .= "<head>";
			$output .= ' <title>Log | '. $name. '</title>';
			$output .= ' <script src="'. $myCommon. '"></script>';
			$output .= ' <script src="'. $myCode. 'highlight/highlight.min.js"></script>';
			$output .= ' <link rel="stylesheet" href="'. $myCode. 'highlight/atom-one-dark.css">';
			$output .= ' <style>';
			$output .= 'body{margin:0;height:100%;} .clear{position:absolute;top:1em;right:2em;border:1px solid #fff;color:#fff;border-radius:3px;padding:0.5em 1em;text-decoration:none} .zip{position:absolute;bottom:1.5em;right:2em;background-color:#000;color:#fff;border-radius:3px;padding:0.5em 1em;text-decoration:none} .hljs{padding:0;max-height:100%;height:100%;}';
			$output .= ' </style>';

			$output .= ' <script>$(document).ready(function() {$("pre").each(function(i, block) {hljs.highlightBlock(block);}); });</script>';
			$output .= "</head><body>";
			$output .= '<a class="clear" href="?name='. $name. '&clear=true">Clear it!</a>';
			$output .= '<a class="zip" href="?name='. $name. '&zip=true">ZIP it!</a>';
			$output .= "<pre class='$lang'>";
			$output .= $fileData;
			$output .= "</pre>";
		}
		else
		{
			$output .= 'File does not exist!';
		}

		$output .= "</body></html>";
		echo $output;
		exit();

	}


}
?>