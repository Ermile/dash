<?php
namespace lib\utility;

/** Validation of several types of string : email, url, ip...etc **/
class validation
{

	/**
	 * Top level domains accepted in the URLs and emails
	 * cf. http://en.wikipedia.org/wiki/Top-level_domain
	 * @const string
	 */
	const TLDS = '[a-z]{2}|com|info|net|org|biz|name|pro|aero|asia|cat|coop|edu|gov|int|jobs|mil|museum|mobi|tel|travel|ir';

	/**
	 * Checks a username
	 *
	 * @param string $username
	 * @return True if the syntax matches, false otherwise
	 */
	public static function isUsername($username){
		$reg = "/^[^\x01-\x1F<>&\"']{3,20}$/i";
		return preg_match($reg, $username) && !preg_match("/(^ |  | $)/", $username);
	}

	/**
	 * Checks a password
	 *
	 * @param string $password
	 * @return True if the syntax matches, false otherwise
	 */
	public static function isPassword($password){
		$reg = "/^[^\x01-\x1F]{3,20}$/";
		return preg_match($reg, $password);
	}

	/**
	 * Checks an email address
	 * uses RFC 2822 (simplified)
	 * Regexp inspired by http://www.regular-expressions.info/email.html
	 *
	 * @param string $email
	 * @return True if the syntax matches, false otherwise
	 */
	public static function isEmail($email){
		// $reg = "/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:".self::TLDS.")$/i";
		// return preg_match($reg, $email);
		return filter_var($email, FILTER_SANITIZE_EMAIL) === false ? false : true;
	}

	/**
	 * Checks a URL
	 * uses RFC 3986 (simplified)
	 * Regexp inspired by http://www.mattfarina.com/2009/01/08/rfc-3986-url-validation
	 *
	 * @param string $url
	 * @return True if the syntax matches, false otherwise
	 */
	public static function isURL($url){
		$reg = "/^
(?:ftp|https?):\/\/
(?:
  (?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
  (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@
)?
(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:".self::TLDS.")
(?::[0-9]+)?
(?:[\/|\?]
  (?:[\w#!:\.\?\+=&@!$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})
*)?
$/xi";
		return preg_match($reg, $url);
	}

	/**
	 * Checks an IP address
	 *
	 * @param string $ip
	 * @return True if the syntax matches, false otherwise
	 */
	public static function isIP($ip){
		// $reg = "/^(?:\.(?:1?[0-9]{1,2}|2(?:[0-4][0-9]|5[0-5]))){4}$/";
		// return preg_match($reg, '.'.$ip);
		return filter_var($email, FILTER_VALIDATE_IP) === false ? false : true;
	}

	/**
	 * Checks an local IP (IP in a Local Area Network)
	 *
	 * @param string $ip
	 * @return True if the syntax matches, false otherwise
	 */
	public static function isLocalIP($ip){
		$reg = "/^(127\.0|192\.168|10|172\.[1-3][0-9])\./";
		return preg_match($reg, $ip);
	}

}
