<?php
namespace lib\telegram;

/** telegram generate needle library**/
class generate extends tg
{
	/**
	 * this library generate telegram tools
	 * v1.1
	 */


	/**
	 * default action to handle message texts
	 * @param  [type] [description]
	 * @return [type]       [description]
	 */
	public static function answer($forceSample = null)
	{
		$answer  = null;
		// read from main command template
		$cmdFolder = __NAMESPACE__ .'\commands\\';

		// use user defined command
		if(!$forceSample && self::$cmdFolder)
		{
			$cmdFolder = self::$cmdFolder;
		}
		foreach (self::$priority as $class)
		{
			$funcName = $cmdFolder. $class.'::exec';
			// generate func name
			if(is_callable($funcName))
			{
				// get response
				$answer = call_user_func($funcName, self::$cmd);
				// if has response break loop
				if($answer || is_array($answer))
				{
					break;
				}
			}
		}
		// if we dont have answer text then use default text
		if(!$answer)
		{
			if(self::response('chat', 'type') === 'group')
			{
				// if your bot joied to group show thanks message
				if(self::response('new_chat_member', 'username') === self::$name)
				{
					$msg = "Thanks for using me!\r\n\nI'm Bot.";
					$msg = "با تشکر از شما عزیزان به خاطر دعوت از من!\r\n\nمن یک ربات هستم.";
					$answer = ['text' => $msg ];
				}
			}
			elseif(\lib\option::social('telegram', 'debug') && !is_array($answer))
			{
				// then if not exist set default text
				$answer = ['text' => self::$defaultText];
				if(self::$defaultMenu && is_object(self::$defaultMenu))
				{
					$answer['reply_markup'] = call_user_func(self::$defaultMenu);
				}
			}
		}
		return $answer;
	}


	/**
	 * replace fill values if exist
	 * @param  [type] $_data [description]
	 * @return [type]        [description]
	 */
	public static function replaceFill($_data)
	{
		if(!self::$fill)
		{
			return $_data;
		}

		// replace all texts
		if(isset($_data['text']))
		{
			foreach (self::$fill as $search => $replace)
			{
				$search	= '_'.$search.'_';
				$_data['text'] = str_replace($search, $replace, $_data['text']);
			}
		}

		// replace all texts
		if(isset($_data['caption']))
		{
			foreach (self::$fill as $search => $replace)
			{
				$search	= '_'.$search.'_';
				$_data['caption'] = str_replace($search, $replace, $_data['caption']);
			}
		}

		if(isset($_data['reply_markup']['keyboard']))
		{
			foreach ($_data['reply_markup']['keyboard'] as $itemRowKey => $itemRow)
			{
				foreach ($itemRow as $key => $itemValue)
				{
					if(!is_array($itemValue))
					{
						foreach (self::$fill as $search => $replace)
						{
							$search	= '_'.$search.'_';
							$newValue = str_replace($search, $replace, $itemValue);

							$_data['reply_markup']['keyboard'][$itemRowKey][$key] = $newValue;
						}
					}
				}
			}
		}
		return $_data;
	}
}
?>