<?php
namespace lib\telegram;

/** telegram execute last commits library**/
class exec extends tg
{
	/**
	 * this library send request to telegram servers
	 * v1.0
	 */


	/**
	 * Execute cURL call
	 * @return mixed Result of the cURL call
	 */
	public static function send($_method = null, array $_data = null, $_output = null)
	{
		if(!isset($_data['is_json']))
		{
			$is_json = true;
		}
		else
		{
			$is_json = $_data['is_json'];
			unset($_data['is_json']);
		}
		if(isset($_data['storage']))
		{
			unset($_data['storage']);
		}
		// if telegram is off then do not run
		if(!\lib\option::social('telegram', 'status'))
		{
			return 'telegram is off!';
		}
		// if method or data is not set return
		if(!$_method || !$_data)
		{
			$log = ['METHOD_NOT_FOUND'];
			foreach (debug_backtrace() as $key => $value) {
				if($key == 7) break;
				$log[] = $value;
			}
			\lib\db::log($log, null, 'telegram-error.json', 'json');
			return 'method or data is not set!';
		}
		if(array_key_exists('method', $_data))
		{
			if($_data['method'] == 'answerInlineQuery')
			{
				$is_json = true;
			}
			$_method = $_data['method'];
			unset($_data['method']);
		}
		$response_callback = null;
		if(array_key_exists('response_callback', $_data))
		{
			$response_callback = $_data['response_callback'];
			unset($_data['response_callback']);
		}
		// if api key is not set get it from options
		if(!self::$api_key)
		{
			self::$api_key = \lib\option::social('telegram', 'key');
		}
		// if key is not correct return
		if(strlen(self::$api_key) < 20)
		{
			return 'api key is not correct!';
		}

		// initialize curl
		$ch = curl_init();
		if ($ch === false)
		{
			return 'Curl failed to initialize';
		}

		$curlConfig =
		[
			CURLOPT_URL            => "https://api.telegram.org/bot".self::$api_key."/$_method",
			CURLOPT_HEADER         => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_SAFE_UPLOAD    => true,
			CURLOPT_SSL_VERIFYPEER => false,
		];
		curl_setopt_array($ch, $curlConfig);
		if (!empty($_data))
		{
			if($is_json)
			{
				$data_string = json_encode($_data);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string))
				);
			}
			else
			{
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $_data);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
				// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
				// curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($_data));
			}
		}
		if(Tld === 'dev')
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$result = curl_exec($ch);
		\lib\db::log([$curlConfig, curl_getinfo($ch)], null, 'telegram-info.json', 'json');

		if($response_callback)
		{
			if(is_object($response_callback))
			{
				call_user_func_array($response_callback, [json_decode($result), $_data]);
			}
			elseif(is_array($response_callback))
			{
				$args = array_splice($response_callback, 1);
				array_unshift($args, json_decode($result), $_data);
				call_user_func_array($response_callback[0], $args);
			}
		}
		if ($result === false)
		{
			return curl_error($ch). ':'. curl_errno($ch);
		}
		if (empty($result) | is_null($result))
		{
			return 'Empty server response';
		}
		curl_close($ch);
		//Logging curl requests
		if(substr($result, 0,1) === "{")
		{
			$result = json_decode($result, true);
			if($_output && isset($result[$_output]))
			{
				$result = $result[$_output];
			}
		}
		log::save($result);
		// return result
		return $result;
	}
}
?>