<?php
namespace dash\engine;


class prepare
{
	public static function requirements()
	{
		self::hi_developers();
		self::minimum_requirement();

		self::xframe_option();

		self::error_handler();
		self::debug();

		self::set_db_log_name();
	}


	private static function check_is_unload()
	{
		// if the request is a unload request needless to run anything!
		if(\dash\request::is_unload())
		{
			\dash\code::boom();
		}
	}


	public static function basics()
	{

		// dont run on some condition
		self::dont_run_exception();
		// check comming soon page
		self::coming_soon();
		// check need redirect for lang or www or https or main domain
		self::fix_url_host();
		self::account_urls();
		// generate static files
		self::static_files();

		// start session
		self::session_start();

		self::user_country_redirect();

		self::dash_shutdown_function();

		// if the request is a unload request needless to run anything!
		self::check_is_unload();
	}


	private static function dash_shutdown_function()
	{
		if(\dash\option::config('visitor'))
		{
			register_shutdown_function(['\dash\utility\visitor', 'save']);
		}

		register_shutdown_function(['\dash\db', 'close']);
	}


	private static function set_db_log_name()
	{
		if(!defined('db_log_name'))
		{
			if(defined('db_name'))
			{
				define('db_log_name', db_name. '_log');
			}
		}
	}

	/**
	* if the user use 'en' language of site
	* and her country is "IR"
	* and no referer to this page
	* and no cookie set from this site
	* redirect to 'fa' page
	* WARNING:
	* this function work when the default lanuage of site is 'en'
	* if the default language if 'fa'
	* and the user work by 'en' site
	* this function redirect to tj.com/fa/en
	* and then redirect to tj.com/en
	* so no change to user interface ;)
	*/
	private static function user_country_redirect()
	{
		if(\dash\url::content() !== null)
		{
			return;
		}

		$referer = (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) ? true : false;
		if($referer)
		{
			return false;
		}

		$cookie = \dash\utility\cookie::read('language');

		if(!$cookie && !\dash\url::lang())
		{
			$default_site_language = \dash\language::primary();

			$redirect_lang = 'en';
			$ipCountry     = null;

			if(isset($_SERVER['HTTP_CF_IPCOUNTRY']))
			{
				$ipCountry = mb_strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
			}
			elseif(isset($_SERVER['HTTP_AR_REAL_COUNTRY']))
			{
				$ipCountry = mb_strtoupper($_SERVER['HTTP_AR_REAL_COUNTRY']);
			}
			else
			{
				return;
			}

			if($ipCountry === 'IR')
			{
				$redirect_lang = 'fa';
			}

			if(array_key_exists($redirect_lang, \dash\language::all()))
			{
				\dash\utility\cookie::write('language', $redirect_lang, (60*60*1), '.'. \dash\url::domain());

				$my_url = \dash\url::base();

				if($default_site_language === $redirect_lang)
				{
					// nothing
				}
				else
				{
					$my_url .= '/'. $redirect_lang;
				}

				if(\dash\url::path() !== '/')
				{
					$my_url .= \dash\url::path();
				}

				\dash\redirect::to($my_url);
			}
		}
	}


	/**
	 * start session
	 */
	private static function session_start()
	{
		if(is_string(\dash\url::root()))
		{
			session_name(\dash\url::root());
		}

		if(\dash\option::config('free_subdomain'))
		{
			$cookie_domain = \dash\url::subdomain(). '.'. \dash\url::domain();
		}
		else
		{
			$cookie_domain = '.'. \dash\url::domain();
		}

		if(\dash\url::isLocal())
		{
			session_set_cookie_params(0, '/', $cookie_domain, false, true);
		}
		else
		{
			// set session cookie params
			session_set_cookie_params(0, '/', $cookie_domain, true, true);
		}

		// start sessions
		session_start();
	}


	/**
	 * [account_urls description]
	 * @return [type] [description]
	 */
	private static function account_urls()
	{
		$param = \dash\url::query();
		if($param)
		{
			$param = '?'.$param;
		}

		$myrep = \dash\url::content();
		switch (\dash\url::module())
		{
			case 'signin':
			case 'login':
				$url = \dash\url::kingdom(). '/enter'. $param;
				\dash\redirect::to($url);
				break;

			case 'signup':
				if($myrep !== 'enter')
				{
					$url = \dash\url::kingdom(). '/enter/signup'. $param;
					\dash\redirect::to($url);
				}
				break;

			case 'register':

				$url = \dash\url::kingdom(). '/enter/signup'. $param;
				\dash\redirect::to($url);
				break;

			case 'signout':
			case 'logout':
				if($myrep !== 'enter')
				{
					$url = \dash\url::kingdom(). '/enter/logout'. $param;
					\dash\redirect::to($url);
				}

				break;
		}

		switch (\dash\url::directory())
		{
			case 'account/recovery':
			case 'account/changepass':
			case 'account/verification':
			case 'account/verificationsms':
			case 'account/signin':
			case 'account/login':
				$url = \dash\url::kingdom(). '/enter'. $param;
				\dash\redirect::to($url);
				break;

			case 'account/signup':
			case 'account/register':
				$url = \dash\url::kingdom(). '/enter/signup'. $param;
				\dash\redirect::to($url);
				break;

			case 'account/logout':
			case 'account/signout':
				$url = \dash\url::kingdom(). '/enter/logout'. $param;
				\dash\redirect::to($url);
				break;
		}
	}

	/**
	 * generate some static files automatically
	 * @return [type] [description]
	 */
	private static function static_files()
	{
		switch (\dash\url::directory())
		{
			case 'manifest.webmanifest':
				\dash\engine\pwa::manifest();
				break;

			case 'serviceWorker':
			case 'serviceWorker/v2':
			case 'serviceWorker/v3':
			case 'serviceWorker-v3':
				\dash\engine\pwa::service_worker();
				break;

			case 'offline.html':
				\dash\engine\pwa::offline();
				break;

			case 'robots.txt':
				\dash\engine\static_files::robots();
				break;

			case 'static/humans.txt':
			case 'humans.txt':
			case 'contributors':
				\dash\engine\static_files::human();
				break;
		}
	}


	/**
	 * set best domain and url
	 * @return [type] [description]
	 */
	private static function fix_url_host()
	{
		if(\dash\option::url('fix') !== true)
		{
			return null;
		}

		// decalare target url
		$target_host = '';

		// fix protocol
		if(\dash\option::url('protocol'))
		{
			$target_host = \dash\option::url('protocol').'://';
		}
		else
		{
			$target_host = \dash\url::protocol().'://';
		}

		// set www subdomain
		if(\dash\option::url('www'))
		{
			if(\dash\url::subdomain())
			{
				$target_host .= \dash\url::subdomain(). '.';
			}
			else
			{
				$target_host .= 'www.';
			}
		}
		elseif(\dash\url::subdomain() && \dash\url::subdomain() !== 'www')
		{

			$target_host .= \dash\url::subdomain(). '.';
		}

		// fix root domain
		if(\dash\option::url('root'))
		{
			if(\dash\option::url('root') !== \dash\url::root())
			{
				if(is_callable(['\lib\alias', 'url']) && \lib\alias::url())
				{
					$target_host .= \dash\url::root();
				}
				elseif(\dash\engine\content::enterprise_customers())
				{
					$target_host .= \dash\url::root();
				}
				else
				{
					$target_host .= \dash\option::url('root');
				}
			}
			else
			{
				$target_host .= \dash\option::url('root');
			}

		}
		elseif(\dash\url::root())
		{
			$target_host .= \dash\url::root();
		}

		// fix tld
		if(\dash\option::url('tld'))
		{
			if(is_callable(['\lib\alias', 'url']) && \lib\alias::url())
			{
				$target_host .= '.'. \dash\url::tld();
			}
			elseif(\dash\engine\content::enterprise_customers())
			{
				$target_host .= '.'. \dash\url::tld();
			}
			else
			{
				$target_host .= '.'.\dash\option::url('tld');
			}
		}
		elseif(\dash\url::tld())
		{
			$target_host .= '.'.\dash\url::tld();
		}

		if(\dash\option::url('port') && \dash\option::url('port') !== 80 && \dash\option::url('port') !== 443)
		{
			$target_host .= ':'.\dash\option::url('port');
		}
		elseif(\dash\url::port() && \dash\url::port() !== 80 && \dash\url::port() !== 443)
		{
			$target_host .= ':'.\dash\url::port();
		}

		if(\dash\url::related_url())
		{
			$target_host .= \dash\url::related_url();
		}

		// help new language detect in target site by set /fa
		if(!\dash\url::lang() && \dash\option::url('tld') && \dash\option::url('tld') !== \dash\url::tld())
		{
			switch (\dash\url::tld())
			{
				case 'ir':
					// $target_host .= '/fa';
					break;

				default:
					break;
			}
		}

		$target_url = $target_host;
		if(\dash\url::content() === 'hook' || \dash\url::content() === 'api')
		{
			$target_url .= \dash\url::path();
		}
		else if(\dash\url::lang() === \dash\language::primary())
		{
			// try to remove lang from url
			if(\dash\url::content())
			{
				$target_url .= '/'. \dash\url::content();
			}
			if(\dash\url::directory())
			{
				$target_url .= '/'. \dash\url::directory();
			}

			// set cookie of language to fix false detection after redirect
			\dash\utility\cookie::write('language', \dash\url::lang(), (60*60*1), '.'. \dash\url::domain());
		}
		else
		{
			$target_url .= \dash\url::path();
		}
		// set target url with path
		$target_url = self::fix_url_slash($target_url);

		$full_target = $target_url;

		if(\dash\url::query())
		{
			$query_string = \dash\url::query();

			if(substr($query_string, -1) === '/')
			{
				$query_string = substr($query_string, 0, (mb_strlen($query_string) - 1));
			}

			$full_target .= '?'. $query_string;
		}


		// if we have new target url, and dont on force show mode, try to change it
		if(!\dash\request::get('force'))
		{
			if($target_host === \dash\url::base())
			{
				// only check last slash
				if($target_url !== \dash\url::pwd())
				{
					\dash\redirect::to($full_target);
				}
			}
			else
			{
				// change host and slash together
				\dash\redirect::to($full_target);
			}
		}
	}


	/**
	 * fix slash, if needed add it else remove it
	 * @param  [type] $_url [description]
	 * @return [type]       [description]
	 */
	private static function fix_url_slash($_url)
	{
		$myBrowser = \dash\utility\browserDetection::browser_detection('browser_name');
		if($myBrowser === 'samsungbrowser')
		{
			// samsung is stupid!
		}
		else
		{
			// remove slash in normal condition
			$_url = trim($_url, '/');

			if(\dash\option::url('slash'))
			{
				// add slash if set in settings
				$_url .= '/';
			}
			elseif(\dash\url::path() === '/')
			{
				// add slash for homepage
				$_url .= '/';
			}
		}
		return $_url;
	}


	/**
	 * check coming soon status
	 * @return [type] [description]
	 */
	private static function coming_soon()
	{
		/**
		 * in coming soon period show public_html/pages/coming/ folder
		 * developer must set get parameter like site.com/dev=anyvalue
		 * for disable this attribute turn off it from config.php in project root
		 */
		if(\dash\option::config('coming'))
		{
			// if user set dev in get, show the site
			if(isset($_GET['dev']))
			{
				setcookie('preview','yes',time() + 30*24*60*60,'/','.'.\dash\url::domain());
			}
			elseif(\dash\url::content() === 'hook')
			{
				// allow telegram to commiunate on coming soon
			}
			elseif(!isset($_COOKIE["preview"]))
			{
				\dash\redirect::to(\dash\url::static(). '/page/coming/', true, 302);
			}
		}
	}


	/**
	 * set custom error handler
	 */
	private static function error_handler()
	{
		//Setting for the PHP Error Handler
		set_error_handler( "\\dash\\engine\\error::handle_error" );

		//Setting for the PHP Exceptions Error Handler
		set_exception_handler( "\\dash\\engine\\error::handle_exception" );

		//Setting for the PHP Fatal Error
		register_shutdown_function( "\\dash\\engine\\error::handle_fatal" );
	}


	/**
	 * set debug status
	 * @param  [type] $_status [description]
	 */
	public static function debug($_status = null)
	{
		if($_status === null)
		{
			$_status = \dash\option::config('debug');
		}

		if($_status)
		{
			ini_set('display_startup_errors', 'On');
			ini_set('error_reporting'       , 'E_ALL | E_STRICT');
			ini_set('track_errors'          , 'On');
			ini_set('display_errors'        , 1);
			error_reporting(E_ALL);
		}
		else
		{
			error_reporting(0);
			ini_set('display_errors', 0);
		}
	}


	/**
	 * check current version of server technologies like php and mysql
	 * and if is less than min, show error message
	 * @return [type] [description]
	 */
	private static function minimum_requirement()
	{
		// check php version to upper than 7.0
		if(version_compare(phpversion(), '7.0', '<'))
		{
			if(version_compare(phpversion(), '5.6', '>='))
			{
				// \dash\code::pretty("<p>For using Dash you must update php version to 7.0 or higher!</p>");
			}
			else
			{
				\dash\code::bye("<p>For using Dash you must update php version to 7.0 or higher!</p>");
			}
		}
	}


	private static function dont_run_exception()
	{
		// files
		if(strpos(\dash\url::path(), '/files') === 0)
		{
			\dash\header::status(404);
		}
		// static
		if(strpos(\dash\url::path(), '/static') === 0)
		{
			\dash\header::status(404);
		}
		// static
		if(strpos(\dash\url::path(), '/index.html') !== false || strpos(\dash\url::path(), '/index.php') !== false)
		{
			$myAddr = str_replace('/index.html', '', \dash\url::path());
			$myAddr = str_replace('/index.php', '', $myAddr);

			\dash\redirect::to(\dash\url::base(). $myAddr);
		}
		// favicon
		if(strpos(\dash\url::path(), '/favicon.ico') !== false)
		{
			\dash\redirect::to(\dash\url::static(). '/images/favicons/favicon.ico');
		}
	}

	/**
	 * set some header and say hi to developers
	 */
	private static function hi_developers()
	{
		// change header and remove php from it
		@header("X-Made-In: Ermile!");
		@header("X-Powered-By: Jibres");
	}



	private static function xframe_option()
	{
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$enamad = 'https://trustseal.enamad.ir/';

			if(strpos($_SERVER['HTTP_REFERER'], $enamad) !== false)
			{
				@header('X-Frame-Options: *');
				return;
			}

			if(\dash\url::root() === 'tejarak')
			{
				@header('X-Frame-Options: *');
				return;
			}
		}
		@header('X-Frame-Options: DENY');
	}

}
?>