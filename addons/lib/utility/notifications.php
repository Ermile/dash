<?php
namespace lib\utility;
use \lib\db;

class notifications
{
	public $user_details = [];
	public $telegram     = [];
	public $sms          = [];
	public $email        = [];

	/**
	 * start sending at time()
	 *
	 * @var        integer
	 */
	public $start    = 0;
	public $time_out = 40; // break at 40 second

	/**
	 * the status of notification class
	 *
	 * @var        boolean
	 */
	public $status = true;


	/**
	 * check not sended notify and send it
	 */
	public function send()
	{
		$this->config();

		if(!$this->status)
		{
			return;
		}

		$this->start = time();

		$this->send_telegram();

		$this->send_sms();

		$this->send_email();
	}


	/**
	 * Determines ability to not send.
	 *
	 * @param      <type>  $_id    The identifier
	 * @param      <type>  $_way   The way
	 */
	public function can_not_send($_id, $_way)
	{
		if($_id && is_numeric($_id) && $_way)
		{
			$field = $_way. 'date';

			$date  = date("Y-m-d H:i:s");
			$query =
			"
				UPDATE
					notifications
				SET
					notifications.$field   = '$date',
					notifications.$_way = 0
				WHERE
					notifications.id = $_id
				LIMIT 1
			";
			\lib\db::query($query);
		}
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $value  The data
	 */
	public function compelete($_id, $_way, $_status = null)
	{
		if($_id && is_numeric($_id) && $_way)
		{
			$status_query = null;

			if($_status === 'block')
			{
				$status_query = " , notifications.status = 'block' ";
			}

			$field = $_way. 'date';
			$date  = date("Y-m-d H:i:s");

			$query =
			"
				UPDATE
					notifications
				SET
					notifications.$field   = '$date'
					$status_query
				WHERE
					notifications.id = $_id
				LIMIT 1
			";
			\lib\db::query($query);
		}
	}


	/**
	 * Sends by telegram.
	 *
	 * @param      <type>  $value  The data
	 */
	public function send_telegram()
	{
		if(time() - $this->start > $this->time_out)
		{
			return;
		}

		foreach ($this->telegram as $key => $value)
		{
			$value = $this->make($value);

			if($this->is_block($value->user_id, $value->category))
			{
				$this->compelete($value->id, 'telegram', 'block');
				continue;
			}

			if($this->get_chat_id($value->user_id))
			{
				$msg = $value->title . ' '. $value->content;
				$this->compelete($value->id, 'telegram');
				\lib\utility\telegram::sendMessage($this->get_chat_id($value->user_id), $msg, ['sort' => 1]);

			}
			else
			{
				$this->can_not_send($value->id, 'telegram');
			}

			if(time() - $this->start > $this->time_out)
			{
				return;
			}
		}

		\lib\utility\telegram::sort_send();
		\lib\utility\telegram::clean_cash();
	}


	/**
	 * Send by sms.
	 *
	 * @param      <type>  $value  The data
	 */
	public function send_sms()
	{

		if(time() - $this->start > $this->time_out)
		{
			return;
		}

		foreach ($this->sms as $key => $value)
		{
			$value = $this->make($value);

			if($this->is_block($value->user_id, $value->category))
			{
				$this->compelete($value->id, 'sms', 'block');
				continue;
			}

			if($this->get_mobile($value->user_id))
			{
				$this->compelete($value->id, 'sms');
				$sms           = [];
				$sms['mobile'] = $this->get_mobile($value->user_id);
				$sms['msg']    = $value->title . ' '. $value->content;
				$sms['args']   = null;
				\lib\utility\sms::send($sms);
			}
			else
			{
				$this->can_not_send($value->id, 'sms');
			}

			if(time() - $this->start > $this->time_out)
			{
				return;
			}
		}

	}


	/**
	 * Sends an email.
	 *
	 * @param      <type>  $value  The data
	 */
	public function send_email()
	{
		if(time() - $this->start > $this->time_out)
		{
			return;
		}

		foreach ($this->email as $key => $value)
		{
			$value = $this->make($value);

			if($this->is_block($value->user_id, $value->category))
			{
				$this->compelete($value->id, 'email', 'block');
				continue;
			}

			if($this->get_email($value->user_id))
			{
				$this->compelete($value->id, 'email');
				$mail =
				[
					'from'    => 'info@tejarak.com',
					'to'      => $this->get_email($value->user_id),
					'subject' => $value->title,
					'body'    => $value->content,
					'debug'   => false,
				];
				\lib\utility\mail::send($mail);

			}
			else
			{
				$this->can_not_send($value->id, 'email');
			}

			if(time() - $this->start > $this->time_out)
			{
				return;
			}
		}

	}


	/**
	 * check if isset variable or return null
	 * to not use if(isset(...)) :)
	 *
	 * @param      <type>  $_value  The value
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function make($_value)
	{
		$new_value = (object) [];
		$field =
		[
			'id',
			'user_id',
			'user_idsender',
			'title',
			'content',
			'url',
			'read',
			'star',
			'status',
			'category',
			'createdate',
			'expiredate',
			'readdate',
			'datemodified',
			'desc',
			'meta',
			'telegram',
			'telegramdate',
			'sms',
			'smsdate',
			'smsdeliverdate',
			'email',
			'emaildate',
		];

		foreach ($field as $key => $value)
		{
			if(array_key_exists($value, $_value))
			{
				$new_value->$value = $_value[$value];
			}
			else
			{
				$new_value->$value = null;
			}
		}
		return $new_value;
	}


	/**
	 * Gets the query.
	 *
	 * @param      <type>   $_      { parameter_description }
	 *
	 * @return     boolean  The query.
	 */
	public function get_query($_type)
	{
		$fielddate = $_type. 'date';

		$query =
		"
			SELECT
				*
			FROM
				notifications
			WHERE
				notifications.$fielddate IS NULL AND
				notifications.read IS NULL AND
				notifications.$_type = 1 AND
				notifications.status = 'enable'
		";
		return \lib\db::get($query);
	}


	/**
	 * get not sended notifications
	 * load data and user data
	 */
	public function config()
	{
		// notification status is off
		if(!\lib\option::config('notification', 'status'))
		{
			$this->status = false;
			return false;
		}

		// get all not sended notifications

		$this->telegram = $this->get_query('telegram');
		$this->sms      = $this->get_query('sms');
		$this->email    = $this->get_query('email');

		$user_ids = [];
		$l        = [];
		$l[]      = array_column($this->telegram, 'user_id');
		$l[]      = array_column($this->sms, 'user_id');
		$l[]      = array_column($this->email, 'user_id');

		foreach ($l as $key => $value)
		{
			foreach ($value as $user_id)
			{
				array_push($user_ids, $user_id);
			}
		}

		$this->find_way($user_ids);

	}


	/**
	 * find way to send notify
	 *
	 * @param      <type>  $_user_ids  The user identifiers
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function find_way($_user_ids)
	{
		if($_user_ids && is_array($_user_ids))
		{
			$_user_ids = array_unique($_user_ids);
			$_user_ids = implode(',', $_user_ids);
			$query =
			"
				SELECT
					users.id,
					users.mobile,
					users.email,
					users.chatid,
					users.notification,
					users.facebookmail,
					users.twittermail,
					users.googlemail
				FROM
					users
				WHERE users.id IN ($_user_ids)
			";
			$user_details       = \lib\db::get($query);
			$user_details       = \lib\utility\filter::meta_decode($user_details, "/user\_notification/");
			$user_details_id    = array_column($user_details, 'id');
			$user_details       = array_combine($user_details_id, $user_details);
			$this->user_details = $user_details;
			return $user_details;
		}
	}


	/**
	 * Gets the mobile.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 *
	 * @return     <type>  The mobile.
	 */
	public function get_mobile($_user_id)
	{
		if(isset($this->user_details[$_user_id]['mobile']))
		{
			if($mobile = \lib\utility\filter::mobile($this->user_details[$_user_id]['mobile']))
			{
				return $mobile;
			}
		}
		return null;
	}


	/**
	 * Gets the email.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 *
	 * @return     <type>  The email.
	 */
	public function get_email($_user_id)
	{
		if(isset($this->user_details[$_user_id]['email']) && $this->user_details[$_user_id]['email'])
		{
			return $this->user_details[$_user_id]['email'];
		}

		if(isset($this->user_details[$_user_id]['googlemail']) && $this->user_details[$_user_id]['googlemail'])
		{
			return $this->user_details[$_user_id]['googlemail'];
		}

		if(isset($this->user_details[$_user_id]['facebookmail']) && $this->user_details[$_user_id]['facebookmail'])
		{
			return $this->user_details[$_user_id]['facebookmail'];
		}

		if(isset($this->user_details[$_user_id]['twittermail']) && $this->user_details[$_user_id]['twittermail'])
		{
			return $this->user_details[$_user_id]['twittermail'];
		}

		return null;
	}


	/**
	 * Gets the chat identifier.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 *
	 * @return     <type>  The chat identifier.
	 */
	public function get_chat_id($_user_id)
	{
		if(isset($this->user_details[$_user_id]['chatid']))
		{
			return $this->user_details[$_user_id]['chatid'];
		}
		return null;
	}


	/**
	 * Determines if block.
	 *
	 * @param      <type>   $_user_id  The user identifier
	 * @param      <type>   $_cat      The cat
	 *
	 * @return     boolean  True if block, False otherwise.
	 */
	public function is_block($_user_id, $_cat)
	{
		$cat_list = \lib\option::config('notification', 'cat');
		$cat_keys = array_keys($cat_list);
		$cat_list = array_column($cat_list, 'title');
		$cat_list = array_combine($cat_keys, $cat_list);

		if(array_key_exists($_cat, $cat_list))
		{
			$_cat = $cat_list[$_cat];
		}

		if(isset($this->user_details[$_user_id]['notification']))
		{
			if(array_key_exists($_cat, $this->user_details[$_user_id]['notification']))
			{
				if(!$this->user_details[$_user_id]['notification'][$_cat])
				{
					return true;
				}
			}

		}
		return false;
	}
}
?>