<?php
namespace lib\telegram;

/** telegram **/
class tg
{
	/**
	 * this library get and send telegram messages
	 * v12.7
	 */
	public static $api_key     = null;
	public static $name        = null;
	public static $language    = 'en_US';
	public static $botan       = null;
	public static $cmd         = null;
	public static $cmdFolder   = null;
	public static $saveLog     = true;
	public static $hook        = null;
	public static $fill        = null;
	public static $user_id     = null;
	public static $defaultText = 'Undefined';
	public static $defaultMenu = null;
	public static $skipText    = null;
	public static $once_log    = null;
	public static $methods	   = [];
	public static $saveDest    = root.'public_html/files/telegram/';
	public static $priority    =
	[
		'handle',
		'callback',
		'user',
		'menu',
		'simple',
		'conversation',
	];


	/**
	 * handle tg requests
	 * @return [type] [description]
	 */
	public static function run($_allowSample = false)
	{
		// run hook and save it on $hook value
		if(!self::$hook){
			self::hook();
		}

		// generate response from defined commands
		$ans    = generate::answer();
		$result = [];
		if(!$ans && $_allowSample && !is_array($ans))
		{
			$ans = generate::answer(true);
		}
		// if we have some answer send each answer seperated
		if(isset($ans[0]))
		{
			foreach ($ans as $key => $eachAns)
			{
				$result[] = self::sendResponse($eachAns);
			}
		}
		// else run single answer
		else
		{
			$result[] = self::sendResponse($ans);
		}
		// return result of sending
		return $result;
	}


	/**
	 * hook telegram messages
	 * @param  boolean $_save [description]
	 * @return [type]         [description]
	 */
	public static function hook()
	{
		// if telegram is off then do not run
		if(!\lib\option::social('telegram', 'status'))
			return 'telegram is off!';
		self::$hook = json_decode(file_get_contents('php://input'), true);
		// if debug mode is enable give text from get parameter
		if(!isset(self::$hook['message']['text']) && \lib\option::social('telegram', 'debug') && \lib\utility::get('text'))
		{
			self::$hook['message']['text'] = \lib\utility::get('text');
		}
		// save log if allow
		log::save(self::$hook, true);
		// detect cmd and save it in static value
		self::cmd(self::response('text'));
		// if botan is set then save analytics with botan.io
		self::botan();
	}


	/**
	 * seperate input text to command
	 * @param  [type] $_input [description]
	 * @return [type]         [description]
	 */
	public static function cmd($_input = null)
	{
		// define variable
		$cmd =
		[
			'text'     => null,
			'command'  => null,
			'optional' => null,
			'argument' => null,
		];
		// save input value as text
		$cmd['text'] = $_input;
		// seperate text by space
		$text = explode(' ', $_input);
		// if we have parameter 1 save it as command
		if(isset($text[0]))
		{
			$cmd['command'] = $text[0];
			// if we have parameter 2 save it as optional
			if(isset($text[1]))
			{
				$cmd['optional'] = $text[1];
				// if we have parameter 3 save it as argument
				if(isset($text[2]))
				{
					$cmd['argument'] = $text[2];
				}
			}
		}
		// save cmd as global cmd value
		self::$cmd = $cmd;
		// return analysed text given from user
		return $cmd;
	}


	/**
	 * generate response and sending message
	 * @return [type] result of sending
	 */
	public static function sendResponse($_prop)
	{
		if(self::$skipText && !\lib\option::social('telegram', 'debug'))
		{
			return false;
		}
		// if method is not set user sendmessage method
		if(!isset($_prop['method']))
		{
			if(isset($_prop['text']))
			{
				$_prop['method'] = 'sendMessage';
			}
			else
			{
				return 'method is not set!';
			}
		}

		switch ($_prop['method'])
		{
			// create send message format
			case 'sendMessage':
				// if chat id is not set then set it
				if(!isset($_prop['chat_id']) && !isset($_prop['inline_message_id']))
				{
					// require chat id
					$_prop['chat_id']    = self::response('chat');
				}
				// add reply message id
				if(isset($_prop['reply_to_message_id']) && $_prop['reply_to_message_id'] === true)
				{
					$_prop['reply_to_message_id'] = $rsp;
					if(!$_prop['reply_to_message_id'])
					{
						unset($_prop['reply_to_message_id']);
					}
				}
				break;


			case 'editMessageText':
			case 'editMessageCaption':
			case 'editMessageReplyMarkup':
				if(!isset($_prop['chat_id']) && !isset($_prop['inline_message_id']))
				{
					$_prop['chat_id']    = self::response('chat');
					$_prop['message_id'] = self::response('message_id');
				}
				break;

			case 'getUserProfilePhotos':
				if(!isset($_prop['user_id']))
				{
					$_prop['user_id']    = self::response('from');
				}
				break;

			case 'sendPhoto':
			case 'sendAudio':
			case 'sendDocument':
			case 'sendSticker':
			case 'sendVideo':
			case 'sendVoice':
			case 'sendLocation':
			case 'sendVenue':
			case 'sendContact':
			case 'sendChatAction':
			default:
				if(!isset($_prop['chat_id']))
				{
					// require chat id
					$_prop['chat_id']    = self::response('chat');
				}
				break;
		}
		// if array key exist but is null
		if(array_key_exists('chat_id', $_prop) && is_null($_prop['chat_id']))
		{
			$_prop['chat_id'] = \lib\utility::get('id');
		}


		// if on answer we have callback analyse it and send answer
		if(isset($_prop['callback']) && isset($_prop['callback']['text']))
		{
			// generate callback query
			$data =
			[
				'callback_query_id' => self::response('callback_query_id'),
				'text'              => $_prop['callback']['text'],
			];
			if(isset($_prop['callback']['show_alert']))
			{
				$data['show_alert'] = $_prop['callback']['show_alert'];
			}
			// call callback answer
			self::answerCallbackQuery($data);
			// unset callback
			unset($_prop['callback']);
		}

		// replace values of text and markup
		$_prop = generate::replaceFill($_prop);
		// decode markup if exist
		if(isset($_prop['is_json']) && $_prop['is_json'] == false && isset($_prop['reply_markup']))
		{
			$_prop['reply_markup'] = json_encode($_prop['reply_markup'], JSON_UNESCAPED_UNICODE);
		}
		// markdown is enable by default
		if(isset($_prop['text']) && !isset($_prop['parse_mode']))
		{
			$_prop['parse_mode'] = 'markdown';
		}
		// call bot send message func
		$funcName = 'self::'. $_prop['method'];
		$result   = call_user_func($funcName, $_prop);
		// return result of sending
		return $result;
	}


	/**
	 * handle response and return needed key if exist
	 * @param  [type] $_needle [description]
	 * @return [type]          [description]
	 */
	public static function response($_needle = null, $_arg = 'id')
	{
		$data = null;

		switch ($_needle)
		{
			case 'update_id':
				if(isset(self::$hook['update_id']))
				{
					$data = self::$hook['update_id'];
				}
				break;

			case 'message_id':
				if(isset(self::$hook['message']['message_id']))
				{
					$data = self::$hook['message']['message_id'];
				}
				elseif(isset(self::$hook['callback_query']['message']['message_id']))
				{
					$data = self::$hook['callback_query']['message']['message_id'];
				}
				break;

			case 'message':
				if(isset(self::$hook['message']))
				{
					$data = self::$hook['message'];
				}
				elseif(isset(self::$hook['callback_query']['message']))
				{
					$data = self::$hook['callback_query']['message'];
				}
				break;

			case 'callback_query_id':
				if(isset(self::$hook['callback_query']['id']))
				{
					$data = self::$hook['callback_query']['id'];
				}
				break;

			case 'from':
				if(isset(self::$hook['callback_query']['from']))
				{
					$data = self::$hook['callback_query']['from'];
				}
				elseif(isset(self::$hook['message']['from']))
				{
					$data = self::$hook['message']['from'];
				}
				elseif(isset(self::$hook['edited_message']['from']))
				{
					$data = self::$hook['edited_message']['from'];
				}
				elseif(array_key_exists("chosen_inline_result", self::$hook))
				{
					$data = self::$hook['chosen_inline_result']['from'];
				}
				elseif(array_key_exists("inline_query", self::$hook))
				{
					$data = self::$hook['inline_query']['from'];
				}
				if($_arg)
				{
					if(isset($data[$_arg]))
					{
						$data = $data[$_arg];
					}
					elseif($_arg !== null)
					{
						$data = null;
					}
				}
				break;

			case 'chat':
			case 'new_chat_member':
			case 'new_chat_participant':
				if(isset(self::$hook['message'][$_needle]))
				{
					$data = self::$hook['message'][$_needle];
				}
				elseif(isset(self::$hook['callback_query']['message'][$_needle]))
				{
					$data = self::$hook['callback_query']['message'][$_needle];
				}
				if($_arg)
				{
					if(isset($data[$_arg]))
					{
						$data = $data[$_arg];
					}
					elseif($_arg !== null)
					{
						$data = null;
					}
				}
				break;

			case 'text':
				if(isset(self::$hook['message']['text']))
				{
					$data = self::$hook['message']['text'];
				}
				elseif(isset(self::$hook['callback_query']['data']))
				{
					$data = 'cb_'.self::$hook['callback_query']['data'];
				}
				elseif(isset(self::$hook['message']['contact'])
					&& isset(self::$hook['message']['contact']['phone_number'])
				)
				{
					if(isset(self::$hook['message']['contact']['fake']))
					{
						$data = 'type_contact '. self::$hook['message']['contact']['phone_number'] .' fake';
					}
					else
					{
						$data = 'type_contact '. self::$hook['message']['contact']['phone_number'];
					}
				}
				elseif(isset(self::$hook['message']['location'])
					&& isset(self::$hook['message']['location']['longitude'])
					&& isset(self::$hook['message']['location']['latitude'])
				)
				{
					$data = 'type_location ';
					$data .= self::$hook['message']['location']['longitude']. ' ';
					$data .= self::$hook['message']['location']['latitude'];
				}
				elseif(isset(self::$hook['message']['audio']))
				{
					$data = 'type_audio ';
				}
				elseif(isset(self::$hook['message']['document']))
				{
					$data = 'type_document ';
				}
				elseif(isset(self::$hook['message']['photo']))
				{
					$data = 'type_photo ';
				}
				elseif(isset(self::$hook['message']['sticker']))
				{
					$data = 'type_sticker ';
				}
				elseif(isset(self::$hook['message']['video']))
				{
					$data = 'type_video ';
				}
				elseif(isset(self::$hook['message']['voice']))
				{
					$data = 'type_voice ';
				}
				elseif(isset(self::$hook['message']['venue']))
				{
					$data = 'type_venue ';
				}

				// remove @bot_name
				$data = str_replace('@'.self::$name, '', $data);
				// trim text
				$data = trim($data);
				break;

			case 'contact':
				if(isset(self::$hook['message']['contact']))
				{
					$data = self::$hook['message']['contact'];
				}
				if($_arg)
				{
					if(isset($data[$_arg]))
					{
						$data = $data[$_arg];
					}
					elseif($_arg !== null)
					{
						$data = null;
					}
				}
				break;

			case 'location':
				if(isset(self::$hook['message']['location']))
				{
					$data = self::$hook['message']['location'];
				}
				if($_arg)
				{
					if(isset($data[$_arg]))
					{
						$data = $data[$_arg];
					}
					elseif($_arg !== null)
					{
						$data = null;
					}
				}
				break;

			default:
				break;
		}

		return $data;
	}


	/**
	 * connect to botan.io
	 * @return [type] [description]
	 */
	public static function botan()
	{
		if(!isset(self::$botan))
		{
			return false;
		}
		$botan  = new Botan(self::$botan);
		if(!self::response('message'))
		{
			return 'message is not correct!';
		}
		$result = $botan->track(self::response('message'), self::response('text'));
		return $result;
	}


	/**
	 * setWebhook for telegram
	 * @param string $_url  [description]
	 * @param [type] $_file [description]
	 */
	public static function setWebhook($_url = '', $_file = null)
	{
		if(empty($_url))
		{
			$_url = \lib\option::social('telegram', 'hook');
		}
		$answer = ['url' => $_url];
		// if (!is_null($_file))
		// {
		// 	$data['certificate'] = \CURLFile($_file);
		// }
		return exec::send('setWebhook', $answer, 'description') .': '. $_url;
	}

	public static function is_aerial()
	{
		$is_aerial = false;
		$aerial_method = ['callback_query', 'chosen_inline_result', 'inline_query'];
		foreach ($aerial_method as $key => $value) {
			if(array_key_exists($value, self::$hook))
			{
				$is_aerial = true;
				break;
			}
		}
		return $is_aerial;
	}

	/**
	 * execute telegram method
	 * @param  [type] $_name [description]
	 * @param  [type] $_args [description]
	 * @return [type]        [description]
	 */
	static function __callStatic($_name, $_args)
	{
		if(isset($_args[0]))
		{
			$_args = $_args[0];
		}
		if(!array_key_exists('before', self::$methods))
		{
			self::$methods['before'] = [];
		}
		foreach (self::$methods['before'] as $key => $value) {
			if(preg_match($key, $_name, $name))
			{
				$value($_name, $_args);
			}
		}
		if(empty($_args) || is_null($_args))
		{
			return false;
		}
		$return = exec::send($_name, $_args);
		if(!array_key_exists('after', self::$methods))
		{
			self::$methods['after'] = [];
		}
		foreach (self::$methods['after'] as $key => $value) {
			if(preg_match($key, $_name, $name))
			{
				$value($_name, $_args, $return);
			}
		}
		return $return;
	}
}
?>