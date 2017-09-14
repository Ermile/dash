<?php
namespace lib;
/**
 * define error name
 *
 * @var        integer
 */
const BAD_REQUEST              = 400;
const UNAUTHORIZED             = 401;
const FORBIDDEN                = 403;
const NOT_FOUND                = 404;
const METHOD_NOT_ALLOWED       = 405;
const REQUEST_TIME_OUT         = 408;
const GONE                     = 410;
const LENGTH_REQUIRED          = 411;
const PRECONDITION_FAILED      = 412;
const REQUEST_ENTITY_TOO_LARGE = 413;
const REQUEST_URI_TOO_LARGE    = 414;
const UNSUPPORTED_MEDIA_TYPE   = 415;
const INTERNAL_SERVER_ERROR    = 500;
const NOT_IMPLEMENTED          = 501;
const BAD_GATEWAY              = 502;
const SERVICE_UNAVAILABLE      = 503;
const VARIANT_ALSO_VARIES      = 506;


/**
 * Class for error.
 * make error page
 */
class error
{

	public static function string($code)
	{
		$error = array();
		$error[400] = 'BAD REQUEST';
		$error[401] = 'UNAUTHORIZED';
		$error[403] = 'FORBIDDEN';
		$error[404] = 'NOT FOUND';
		$error[405] = 'METHOD NOT ALLOWED';
		$error[408] = 'REQUEST TIME OUT';
		$error[410] = 'GONE';
		$error[411] = 'LENGTH REQUIRED';
		$error[412] = 'PRECONDITION FAILED';
		$error[413] = 'REQUEST ENTITY TOO LARGE';
		$error[414] = 'REQUEST URI TOO LARGE';
		$error[415] = 'UNSUPPORTED MEDIA TYPE';
		$error[500] = 'INTERNAL SERVER ERROR';
		$error[501] = 'NOT IMPLEMENTED';
		$error[502] = 'BAD GATEWAY';
		$error[503] = 'SERVICE UNAVAILABLE';
		$error[506] = 'VARIANT ALSO VARIES';
		return $error[$code];
	}

	public static function service($str = null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, SERVICE_UNAVAILABLE);
	}

	public static function unsupport($str = null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, NOT_IMPLEMENTED);
	}

	public static function page($str=null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, NOT_FOUND);
	}


	public static function core($str=null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, NOT_FOUND);
	}

	public static function login($str=null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, UNAUTHORIZED);
	}

	public static function access($str=null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, FORBIDDEN);
	}


	public static function bad($str=null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, BAD_REQUEST);
	}


	public static function internal($str=null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, INTERNAL_SERVER_ERROR);
	}

	public static function method($str=null)
	{
		$class = debug_backtrace(true);
		self::make($str, $class, METHOD_NOT_ALLOWED);
	}


	public static function make($STRING, $obj, $STATUS)
	{
		$HTTP_ERROR = self::string($STATUS);
		if(\dash::is_json_accept() || \lib\temp::get('api'))
		{
			header('Content-Type: application/json');
			header("HTTP/1.1 $STATUS ".$HTTP_ERROR);
			\lib\debug::title($HTTP_ERROR);
			\lib\debug::error($STRING, $STATUS, "HTTP");
			echo \lib\debug::compile(true);
		}
		else
		{
			header("HTTP/1.1 $STATUS ".$HTTP_ERROR);
			require_once(lib."error_page.php");
		}
		exit();
	}

	// error handler function
	public static function myErrorHandler($errno = null, $errstr = null, $errfile = null, $errline = null)
	{
		// This error code is not included in error_reporting
		if (!(error_reporting() & $errno))
		{
			return;
		}

		echo "<pre>";
		switch ($errno)
		{
			case E_USER_ERROR:
				echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
				echo "  Fatal error on line $errline in file $errfile";
				echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				echo "Aborting...<br />\n";
				exit(1);
				break;

			case E_USER_WARNING:
				echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
				break;

			case E_USER_NOTICE:
				echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
				break;

			default:
				echo "<b>Unknown error type</b>: [$errno] $errstr<br />\n";
				break;
		}
		echo "</pre>";

		/* Don't execute PHP internal error handler */
		return true;
	}
}
?>