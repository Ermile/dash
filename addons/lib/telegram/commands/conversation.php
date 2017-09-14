<?php
namespace lib\telegram\commands;
// use telegram class as bot
use \lib\telegram\tg as bot;

class conversation
{
	/**
	 * execute user request and return best result
	 * @param  [type] $_cmd [description]
	 * @return [type]       [description]
	 */
	public static function exec($_cmd)
	{
		$response = null;
		// first call fa
		$response = self::fa($_cmd);
		// if has no result call en
		if(!$response)
		{
			$response = self::en($_cmd);
		}


		return $response;
	}

	/**
	 * give input message and create best response for it
	 * @param  [type] $_cmd [description]
	 * @return [type]       [description]
	 */
	public static function fa($_cmd)
	{
		$response = null;
		$text     = null;

		switch ($_cmd['text'])
		{
			case 'سلام':
			case 'salam':
				$text = 'سلام عزیزم';
				break;

			case 'خوبی':
			case 'khobi?':
			case 'khobi':
				$text = 'ممنون، خوبم';
				break;

			case 'خوبم':
			case 'خوبم?':
			case '/khobam?':
			case 'khobam?':
			case '/khobam':
			case 'khobam':
				$text = 'احتمالا خوب هستنید!';
				break;

			case 'چه خبرا':
			case 'چه خبرا?':
			case 'چخبر':
			case 'چخبر?':
			case 'چه خبر':
			case 'چه خبر?':
			case 'che khabar':
			case 'che khabar?':
				$text = 'سلامتی';
				break;

			case 'حالت خوبه':
				$text = 'عالی';
				break;

			case 'چاقی':
				$text = 'نه!';
				break;

			case 'سلامتی':
			case 'salamati':
			case 'salamati?':
				$text = 'خدا رو شکر';
				break;

			case 'بمیر':
				$text = 'مردن دست خداست';
				break;

			case 'بد':
				$text = 'من بد نیستم';
				break;

			case 'خوب':
				$text = 'ممنون';
				break;

			case 'زشت':
				$text = 'من خوشگلم';
				break;

			case 'هوا گرمه':
				$text = 'شاید!';
				break;

			case 'سردمه':
				$text = 'بخاری رو روشن کن';
				break;

			case 'بد':
				$text = 'من بد نیستم';
				break;

			case 'خر':
			case 'khar':
				$text = 'خر خودتی'."\r\n";
				$text .= 'باباته'."\r\n";
				$text .= 'بی تربیت'."\r\n";
				break;

			case 'سگ تو روحت':
			case 'sag to rohet':
			case 'sag to ruhet':
				$text = 'بله!'."\r\n";
				$text .= 'من روح ندارم!'."\r\n";
				break;

			case 'نفهم':
				$text = 'من خیلی هم میفهمم';
				break;

			case 'خوابی':
				$text = 'من همیشه بیدارم';
				break;

			case 'هی':
				$text = 'بفرمایید';
				break;

			case 'الو':
			case 'alo':
				$text = 'بله';
				break;

			case 'بلا':
				$text = 'با ادب باش';
				break;

			default:
				$text = false;
				break;
		}
		// create response format
		if($text)
		{
			$response =
			[
				'text' => $text
			];
		}
		// return response as result
		return $response;
	}


	/**
	 * give input message and create best response for it
	 * @param  [type] $_cmd [description]
	 * @return [type]       [description]
	 */
	public static function en($_cmd)
	{
		$response = null;
		$text     = null;

		switch ($_cmd['text'])
		{
			case 'hello':
				$text = 'hello!';
				break;

			case 'good':
			case '/howami':
			case 'howami':
			case 'ls':
			case 'ls-la':
			case 'ls-a':
				$text = ':)';
				break;

			case 'bad':
				$text = ':(';
				break;

			case '/fuck':
			case 'fuck':
			case 'f*ck':
				$text = "YOU ARE A PROGRAMMER🍆";
				break;

			case 'how are you':
			case 'how are you?':
				$text = "I'm fine, thanks";
				break;

			default:
				$text = false;
				break;
		}
		// create response format
		if($text)
		{
			$response =
			[
				'text' => $text
			];
		}
		// return response as result
		return $response;
	}
}
?>