<?php
namespace lib\mvc\models;
use \lib\debug;
use \lib\utility;

trait cp
{

	/**
		Common Operate like insert, update, delete in control panel and other place ---------------------- Start
	**/
	// *************************************************************************************** Query Creator
	// create query string automatically form getTable class field data
	// the add and edit function use this function for create query
	public function create_query($_type = null, $_id = null)
	{
		$qry_module = $this->module(SubDomain);
		$qry_table  = 'table'.ucfirst($qry_module);
		$qry        = $this->sql()->$qry_table();


		// in update type save record data and check if change set this else don't need to set
		if($_type == 'update')
		{
			$mydatarow = $this->datarow($qry_module, $_id);
			$not_change = true;
		}

		// get all fields of table and filter fields name for show in datatable
		// access from columns variable
		// check if datatable exist then get this data
		$incomplete_fields = [];
		$fields            = \lib\sql\getTable::get($qry_module);
		// var_dump(utility::post());
		// var_dump($qry_module);

		foreach ($fields as $key => $value)
		{
			// if this field can exist in query string
			if($value['query'])
			{
				$tmp_setfield = 'set'.ucfirst($key);
				$tmp_value    = utility::post($value['value']);
				if($value['value'] === 'pass')
				{
					$tmp_value = utility::post('pass', 'hash');
				}
				$tmp_value    = trim($tmp_value);

				// if user fill box and post data for this field add to query string
				if($tmp_value || $tmp_value === '0')
				{
					// in update type check for change or not
					if($_type == 'update')
					{
						// if change add to query string and set it
						if($mydatarow[$key] != $tmp_value)
						{
							$qry = $qry->$tmp_setfield($tmp_value);
							$not_change = false;
						}
					}
					else
						$qry = $qry->$tmp_setfield($tmp_value);
				}

				// else if this table contain user_id then use logined user id
				elseif($key=='user_id')
					$qry = $qry->$tmp_setfield($this->login('id'));

				// else if user must fill this field, save the name and send it as incomplete
				elseif(!$value['null'])
				{
					// $incomplete_fields[$key] = $value['value'];
					array_push($incomplete_fields, $value['value']);
				}
			}
		}

		// on cp depending on module add some variable to query
		if(SubDomain === 'cp')
		{
			switch ($this->module())
			{
				case 'tags':
					if(count($incomplete_fields) === 3)
					{
						$qry_module        = 'terms';
						$incomplete_fields = null;
						$term_url          = utility::post('slug');
						$qry = $qry->setTerm_type('tag')->setTerm_url($term_url);
					}
					break;

				case 'categories':
					if(count($incomplete_fields) === 3)
					{
						$qry_module        = 'terms';
						$incomplete_fields = null;
						$term_url          = utility::post('slug');
						$qry = $qry->setTerm_type('cat')->setTerm_url($term_url);
					}
					break;

				case 'pages':
					$qry = $qry->setPost_type('page');
					$qry_module = 'posts';
					break;

				case 'users':
					if($_type == 'insert')
					{
						// remove createdate from incomplete and fill it with current datetime
						if(($key = array_search('createdate', $incomplete_fields)) !== false)
						{
							unset($incomplete_fields[$key]);
						}
						$qry = $qry->set('datecreated', date('Y-m-d H:i:s'));
					}
					else
					{
						$incomplete_fields = null;
					}

					// add meta to save position of users and new properties
					$meta =
					[
						'position' => utility::post('position')
					];
					if($meta)
					{
						$not_change = false;
					}
					$meta = json_encode($meta, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
					$qry  = $qry->set('meta', $meta);
					break;
			}
		}

		if($incomplete_fields)
		{
			debug::error(T_("all require fields must fill"), json_encode($incomplete_fields));
			// return false;
		}

		if($_type == 'update' && $not_change)
		{
			debug::warn(T_("some fields must be change for update!"));
			return false;
		}
		// var_dump($qry);exit();
		return $qry;
	}


	// *************************************************************************************** Insert
	// call this function and add a new record!
	// this function use create_query func to create a query string then add
	public function insert($_qry = null)
	{
		// if user pass the qry use it else use our automatic creator
		// $myqry = $_qry? $_qry: null;

		if(!$_qry)
		{
			$_qry = $this->create_query(__FUNCTION__);
			// if all require fields not filled then show error and pass invalid fileds name
			if(!$_qry)
				return false;
		}

		return $this->post_commit($_qry);
	}


	// *************************************************************************************** Update
	// call this func and edit current record automatically!
	// this function use create_query func to create a query string then edit
	public function update($_qry = null, $_id = null)
	{
		// if user pass the qry use it else use our automatic creator
		// $myqry = $_qry? $_qry: null;

		if(!$_qry)
		{
			$tmp_id = $_id? $_id: $this->childparam('edit');
			// debug::true(T_("id: ").$tmp_id);

			$_qry   = $this->create_query(__FUNCTION__, $tmp_id);
			// if all require fields not filled then show error and pass invalid fileds name
			if(!$_qry)
				return false;

			$_qry   = $_qry->whereId($tmp_id);
		}

		return $this->put_commit($_qry);
	}


	// *************************************************************************************** Delete
	// call this func and delete specefic record easily!
	// if you want to delete specefic query you must pass all query except ->delete() at end
	public function delete($_qry = null, $_id = null, $_table = null)
	{
		// if user pass the qry use it else use our automatic creator
		// $myqry = $_qry? $_qry: null;

		if(!$_qry)
		{
			$tmp_table  = $_table? $_table: 'table'.ucfirst($this->module());
			$tmp_id     = $_id?    $_id:    $this->childparam('delete');
			$tmp_id     = $tmp_id? $tmp_id: \lib\utility::post('id');
			$_qry       = $this->sql()->$tmp_table()->whereId($tmp_id);
			// var_dump($_qry);
		}
		if(!$_qry->select()->num())
		{
			debug::error(T_("id does not exist!"));
			return false;
		}

		return $this->delete_commit($_qry);
	}

	/**
	 * [backup description]
	 * @return [type] [description]
	 */
	public function backup()
	{
		$this->sql()->backup();
	}
	/**
		Common Operate like insert, update, delete in control panel and other place ---------------------- End
	**/
}
?>
