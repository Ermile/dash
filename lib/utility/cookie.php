<?php
namespace lib\utility;

/** Cookies management **/
class cookie
{
	// Default configuration

	// @var int			Life time of the cookie in seconds
	const DURATION	= 0;

	// @var string		Path in which the cookie will be available
	const PATH		= '/';

	// @var string		Domain that the cookie is available
	const DOMAIN	= null;

	// @var bool
	// If true, the cookie should only be transmitted over a secure HTTPS connection from the client
	const SECURE	= false;

	// @var bool		If true, the cookie will be made accessible only through the HTTP protocol
	const HTTPONLY	= true;

	/**
	 * Reads a cookie and returns its value
	 *
	 * @param string $name	Name of the cookie
	 * @return mixed	Value of the cookie
	 */
	public static function read($name)
	{
		if(isset($_COOKIE[$name]))
			return $_COOKIE[$name];

		return null;
	}

	/**
	 * Creates or modify a cookie
	 *
	 * @param string $name		Name of the cookie
	 * @param string $value		Value of the cookie. Destroy the cookie if omitted or null
	 * @param int $duration 	Life time of the cookie. Uses default value if omitted or null
	 * @param string $domain	Domain that the cookie is available. Uses default value if omitted or null
	 * @param string $path		Path in which the cookie will be available. Uses default value if omitted or null
	 * @param bool $secure		If true, the cookie should only be transmitted over a secure HTTPS connection from the client. Uses default value if omitted or null
	 * @param bool $httponly	If true, the cookie will be made accessible only through the HTTP protocol. Uses default value if omitted or null
	 */
	public static function write ( $name,
										    $value    = null,
											 $duration = null,
											 $domain   = null,
											 $path     = null,
											 $secure   = null,
											 $httponly = null
											)
	{
		if(!isset($value))
			return self::delete($name);
		if(!isset($duration))
			$duration = self::DURATION;
		if(!isset($path))
			$path = self::PATH;
		if(!isset($domain))
			$domain = self::DOMAIN;
		if(!isset($secure))
			$secure = self::SECURE;
		if(!isset($httponly))
			$httponly = self::HTTPONLY;

		// Expiration date from the life time in seconds
		if($duration==0)
			$expire = 0;
		else
			$expire = time()+((int) $duration);

		// The value must be a string
		$value = (string) $value;

		// Writes the cookie
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
		$_COOKIE[$name] = $value;
	}

	/**
	 * Deletes a cookie
	 *
	 * @param string $name	Name of the cookie
	 */
	public static function delete($name)
	{
		setcookie($name, null, time()-3600*30);
		unset($_COOKIE[$name]);
	}

}

?>