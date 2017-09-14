<?php
namespace addons\content_api\v1\parent\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait get
{

	public function get_list_parent($_args = [])
	{
		$default_args =
		[
			'method' => 'get'
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);


		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'input' => utility::request(),
			]
		];

		if(!$this->user_id)
		{
			logs::set('api:parent:user_id:notfound', null, $log_meta);
			debug::error(T_("User not found"), 'user', 'permission');
			return false;
		}


		$result = [];
		$get_notify =
		[
			'user_idsender'   => $this->user_id,
			'category'        => 9,
			'status'          => 'enable',
			'related_id'      => $this->user_id,
			'related_foreign' => 'users',
			'needanswer'      => 1,
			'answer'          => null,
		];

		$notify_list = \lib\db\notifications::get($get_notify);
		if($notify_list && is_array($notify_list))
		{
			$notify_list = \lib\utility\filter::meta_decode($notify_list);
			foreach ($notify_list as $key => $value)
			{
				$temp                = [];

				$temp['msg']         = T_("Waiting to user accept this request");
				$temp['notify']      = true;

				$temp['id']          = isset($value['id'])? $value['id'] : null;

				$temp['fileurl']    = isset($value['meta']['parent_fileurl'])? $value['meta']['parent_fileurl'] : null;
				$temp['mobile']      = isset($value['meta']['parent_mobile'])? $value['meta']['parent_mobile'] : null;
				$temp['displayname'] = isset($value['meta']['parent_displayname'])? $value['meta']['parent_displayname'] : null;
				$temp['title']       = isset($value['meta']['title'])? $value['meta']['title'] : null;
				$temp['othertitle']  = isset($value['meta']['othertitle'])? $value['meta']['othertitle'] : null;

				if($temp['title'] === 'custom' && $temp['othertitle'])
				{
					$temp['title'] = $temp['othertitle'];
					unset($temp['othertitle']);
				}

				$result[] = $temp;
			}
		}

		$parent_resutl = \lib\db\userparents::load_parent(['user_id' => $this->user_id, 'status' => 'enable']);
		if(is_array($parent_resutl))
		{
			foreach ($parent_resutl as $key => $value)
			{
				array_push($result, $value);
			}
		}
		if(is_array($result))
		{
			foreach ($result as $key => $value)
			{
				if(isset($value['fileurl']))
				{
					$result[$key]['fileurl'] = self::host('file'). '/'. $value['fileurl'];
				}
			}
		}

		return $result;
	}
}
?>