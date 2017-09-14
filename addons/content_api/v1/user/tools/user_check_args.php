<?php
namespace addons\content_api\v1\user\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait user_check_args
{
	public function user_check_args($_args, &$args, $_log_meta, $_type = 'insert')
	{
		$log_meta = $_log_meta;

		// get firstname
		$displayname = utility::request("displayname");
		$displayname = trim($displayname);
		if($displayname && mb_strlen($displayname) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:displayname:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You can set the displayname less than 50 character"), 'displayname', 'arguments');
			return false;
		}

		// get firstname
		$firstname = utility::request("firstname");
		$firstname = trim($firstname);
		if($firstname && mb_strlen($firstname) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:firstname:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You can set the firstname less than 50 character"), 'firstname', 'arguments');
			return false;
		}

		// get lastname
		$lastname = utility::request("lastname");
		$lastname = trim($lastname);
		if($lastname && mb_strlen($lastname) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:lastname:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You can set the lastname less than 50 character"), 'lastname', 'arguments');
			return false;
		}

		// get postion
		$postion     = utility::request('postion');
		if($postion && mb_strlen($postion) > 100)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:postion:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You can set the postion less than 100 character"), 'postion', 'arguments');
			return false;
		}

		// get the code
		$personnelcode = utility::request('personnel_code');
		$personnelcode = trim($personnelcode);
		if($personnelcode && mb_strlen($personnelcode) > 9)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:code:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You can set the personnel_code less than 9 character "), 'personnel_code', 'arguments');
			return false;
		}


		// get file code
		$file_code = utility::request('file');
		$file_id   = null;
		$file_url  = null;
		if($file_code)
		{
			$file_id = \lib\utility\shortURL::decode($file_code);
			if($file_id)
			{
				$logo_record = \lib\db\posts::is_attachment($file_id);
				if(!$logo_record)
				{
					$file_id = null;
				}
				elseif(isset($logo_record['meta']['url']))
				{
					$file_url = $logo_record['meta']['url'];
				}
			}
			else
			{
				$file_id = null;
			}
		}

		// get status
		$status = utility::request('status');
		if($status && mb_strlen($status) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:status:invalid', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("Invalid parameter status"), 'status', 'arguments');
			return false;
		}

		if(!$status && $_type === 'insert')
		{
			$status = 'awaiting';
		}

		$nationalcode = utility::request('nationalcode');
		if($nationalcode && mb_strlen($nationalcode) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:nationalcode:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the national code less than 50 character"), 'nationalcode', 'arguments');
			return false;
		}

		$father = utility::request('father');
		if($father && mb_strlen($father) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:father:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the father name less than 50 character"), 'father', 'arguments');
			return false;
		}

		$birthday      = utility::request('birthday');
		if($birthday && mb_strlen($birthday) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:teacher:birthday:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the birthday name less than 50 character"), 'birthday', 'arguments');
			return false;
		}

		$gender        = utility::request('gender');
		if($gender && !in_array($gender, ['male', 'female']))
		{
			if($_args['save_log']) logs::set('addon:api:teacher:gender:invalid', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("Invalid gender field"), 'gender', 'arguments');
			return false;
		}

		$type  = utility::request('type');
		if($type && !in_array($type, ['teacher','student','manager','deputy','janitor','organizer','sponsor']))
		{
			if($_args['save_log']) logs::set('addon:api:teacher:type:max:length', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("Invalid type of teacher"), 'type', 'arguments');
			return false;
		}

		$marital                = utility::request('marital');
		if($marital && !in_array($marital, ['single', 'married']))
		{
			if($_args['save_log']) logs::set('addon:api:user:marital:invalid', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("Invalid marital field"), 'marital', 'arguments');
			return false;
		}

		$child                  = utility::request('child');
		if($child && mb_strlen($child) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:child:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the child less than 50 character"), 'child', 'arguments');
			return false;
		}

		$brithcity              = utility::request('brithcity');
		if($brithcity && mb_strlen($brithcity) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:brithcity:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the brithcity less than 50 character"), 'brithcity', 'arguments');
			return false;
		}

		$shfrom                 = utility::request('shfrom');
		if($shfrom && mb_strlen($shfrom) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:shfrom:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the shfrom less than 50 character"), 'shfrom', 'arguments');
			return false;
		}

		$shcode                 = utility::request('shcode');
		if($shcode && mb_strlen($shcode) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:shcode:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the shcode less than 50 character"), 'shcode', 'arguments');
			return false;
		}

		$education              = utility::request('education');
		if($education && mb_strlen($education) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:education:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the education less than 50 character"), 'education', 'arguments');
			return false;
		}

		$job       = utility::request('job');
		if($job && mb_strlen($job) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:job:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the job less than 50 character"), 'job', 'arguments');
			return false;
		}

		$passportcode          = utility::request('passportcode');
		if($passportcode && mb_strlen($passportcode) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:passportcode:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the passportcode less than 50 character"), 'passportcode', 'arguments');
			return false;
		}

		$passportexpire        = utility::request('passportexpire');
		if($passportexpire && mb_strlen($passportexpire) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:passportexpire:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the passportexpire less than 50 character"), 'passportexpire', 'arguments');
			return false;
		}

		$paymentaccountnumber = utility::request('paymentaccountnumber');
		if($paymentaccountnumber && mb_strlen($paymentaccountnumber) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:paymentaccountnumber:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the paymentaccountnumber less than 50 character"), 'paymentaccountnumber', 'arguments');
			return false;
		}

		$shaba                  = utility::request('shaba');
		if($shaba && mb_strlen($shaba) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:shaba:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the shaba less than 50 character"), 'shaba', 'arguments');
			return false;
		}

		$cardnumber = utility::request('cardnumber');
		if($cardnumber && mb_strlen($cardnumber) > 50)
		{
			if($_args['save_log']) logs::set('addon:api:user:cardnumber:max:lenght', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("You must set the cardnumber less than 50 character"), 'cardnumber', 'arguments');
			return false;
		}

		$args['marital']              = $marital;
		$args['gender']               = $gender;
		$args['status']               = $status;
		$args['type']                 = $type;
		$args['fileid']               = $file_id;
		$args['fileurl']              = $file_url;
		$args['childcount']           = trim($child);
		$args['brithplace']           = trim($brithcity);
		$args['shfrom']               = trim($shfrom);
		$args['shcode']               = trim($shcode);
		$args['education']            = trim($education);
		$args['job']                  = trim($job);
		$args['passportcode']         = trim($passportcode);
		$args['passportexpire']       = trim($passportexpire);
		$args['paymentaccountnumber'] = trim($paymentaccountnumber);
		$args['cardnumber']           = trim($cardnumber);
		$args['shaba']                = trim($shaba);
		$args['nationalcode']         = trim($nationalcode);
		$args['father']               = trim($father);
		$args['birthday']             = trim($birthday);
		$args['postion']              = trim($postion);
		$args['personnelcode']        = trim($personnelcode);
		$args['name']                 = trim($firstname);
		$args['lastname']             = trim($lastname);

		if($displayname)
		{
			$args['displayname']    = trim($displayname);
		}
		elseif($firstname || $lastname)
		{
			$args['displayname']    = trim($firstname. ' '. $lastname);
		}

	}



	/**
	 * check args and make where
	 *
	 * @param      <type>  $_args      The arguments
	 * @param      <type>  $where      The where
	 * @param      <type>  $_log_meta  The log meta
	 */
	public function user_make_where($_args, &$where, $_log_meta)
	{
		$type = utility::request('type');
		if($type && is_string($type) || is_numeric($type))
		{
			$where['type'] = $type;
		}

		if(!$type && utility::isset_request('type'))
		{
			$where['type'] = null;
		}
	}
}
?>