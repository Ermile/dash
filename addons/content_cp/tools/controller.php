<?php
namespace addons\content_cp\tools;

class controller extends \addons\content_cp\home\controller
{
	function _route()
	{
		// check permission to access to cp
		// if(Tld !== 'dev')
		{
			parent::_permission('cp');
		}

		// // Restrict unwanted module
		// if(!$this->cpModlueList())
		// 	\lib\error::page(T_("Not found!"));
		$exist    = false;
		$mymodule = $this->cpModule('table');
		$cpModule = $this->cpModule('raw');

		switch ($this->child())
		{
			// case 'dbtables':
			// 	parent::_permission('cp', 'tools', 'admin');

			// 	$exist    = true;
			// 	echo \lib\utility\dbTables::create();
			// 	break;

			case 'db':
				parent::_permission('cp', 'tools', 'admin');

				\lib\db::$link_open    = [];
				\lib\db::$link_default = null;
				if(\lib\utility::post('username'))
				{
					\lib\db::$db_user = \lib\utility::post("username");
					\lib\db::$db_pass = \lib\utility::post("password");
				}
				elseif(defined('admin_db_user') && defined('admin_db_pass'))
				{
					\lib\db::$db_user = constant("admin_db_user");
					\lib\db::$db_pass = constant("admin_db_pass");
				}
				elseif(defined('db_user') && defined('db_pass'))
				{
					\lib\db::$db_user = constant("db_user");
					\lib\db::$db_pass = constant("db_pass");
				}
				else
				{
					\lib\error::access(T_("Permission denide for run upgrade database"));
				}

				\lib\db::$debug_error = false;

				$result = null;
				$exist  = true;

				if(\lib\utility::post('type') == 'upgrade')
				{
					// do upgrade
					$result = \lib\db::install(true, true);
				}
				elseif(\lib\utility::post('type') == 'backup')
				{
					// do backup
					$result = \lib\db::backup(true);
				}
				elseif(\lib\utility::post('type') == 'backup_dump')
				{
					// do backup
					$result = \lib\db::backup_dump();
				}

				echo '<pre>';
				print_r($result);
				echo '</pre>';
				exit();
				break;



			case 'twitter':
				$a = \lib\utility\socialNetwork::twitter('hello! test #api');
				// var_dump($a);
				break;


			// case 'mergefiles':
			// 	$exist = true;
			// 	echo \lib\utility\tools::mergefiles('merged-project.php');
			// 	if(\lib\utility::get('type') === 'all')
			// 	{
			// 		echo \lib\utility\tools::mergefiles('merged-lib.php', core.lib);
			// 		echo \lib\utility\tools::mergefiles('merged-cp.php', addons.'content_cp/');
			// 		echo \lib\utility\tools::mergefiles('merged-account.php', addons.'content_account/');
			// 		echo \lib\utility\tools::mergefiles('merged-includes.php', addons.'includes/');
			// 	}
			// 	break;


			case null:
				$mypath = $this->url('path','_');
				if( is_file(addons.'content_cp/templates/static_'.$mypath.'.html') )
				{
					$this->display_name	= 'content_cp/templates/static_'.$mypath.'.html';
				}
				// $this->display_name	= 'content_cp/templates/static_'.$mypath.'.html';
				break;

			default:
				// $this->display_name	= 'content_cp/templates/static_tools.html';

				return;
				break;
		}
		// $this->display_name	= 'content_cp/tools/raw.html';

		// $this->get()->ALL();
		if($exist)
		{
			$this->model()->_processor(object(array("force_json" => false, "force_stop" => true)));
		}

		return;


	}
}
?>