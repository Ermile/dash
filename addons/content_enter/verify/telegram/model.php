<?php
namespace addons\content_enter\verify\telegram;
use \lib\utility;
use \lib\debug;
use \lib\db;
use \lib\telegram\tg as bot;


class model extends \addons\content_enter\main\model
{

	/**
	 * send verification code to the user telegram
	 *
	 * @param      <type>  $_chat_id  The chat identifier
	 * @param      <type>  $_text     The text
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function send_telegram_code()
	{
		// the telegram is off for this project
		if(!\lib\option::social('telegram', 'status'))
		{
			return false;
		}

		$my_chat_id = null;

		if(self::user_data('chatid'))
		{
			$my_chat_id = self::user_data('chatid');
		}
		elseif(self::get_enter_session('temp_chat_id'))
		{
			$my_chat_id = self::get_enter_session('temp_chat_id');
		}

		if(!$my_chat_id)
		{
			return false;
		}

		$code = self::get_enter_session('verification_code');
		// make text
		$text = '';
		$text .= T_("Your login code is :code", ['code' => \lib\utility\human::number($code)]);
		$text .= "\n\n". T_("This code can be used to log in to your account. Do not give it to anyone!");
		$text .= "\n" . T_("If you didn't request this code, ignore this message.");

		\lib\utility\telegram::sendMessage($my_chat_id, $text);
		return true;
	}


	/**
	* check sended code
	*/
	public function post_verify()
	{
		self::check_code('telegram');
	}
}
?>
