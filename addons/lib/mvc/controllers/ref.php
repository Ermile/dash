<?php
namespace lib\mvc\controllers;

trait ref
{
	/**
	 * Saves a reference.
	 *
	 */
	public function save_ref()
	{
		if(\lib\utility::get("ref") && !$this->login())
		{
			$save_log = true;
			if(isset($_SESSION['ref']))
			{
				$ref_id   = \lib\utility\shortURL::decode(\lib\utility::get("ref"));
				if($_SESSION['ref'] == \lib\utility::get("ref"))
				{
					$save_log = false;
				}
				else
				{
					\lib\db\logs::set('user:ref:different', null, ['data' => $ref_id, 'meta' => ['ref' => \lib\utility::get(), 'session' => $_SESSION]]);
					$save_log        = true;
				}
			}
			else
			{
				$save_log = true;
			}
			$_SESSION['ref'] = \lib\utility::get("ref");

			if($save_log)
			{
				$ref_id   = \lib\utility\shortURL::decode(\lib\utility::get("ref"));
				\lib\db\logs::set('user:ref:set', null, ['data' => $ref_id, 'meta' => ['ref' => \lib\utility::get()]]);
			}

		}
	}
}
?>