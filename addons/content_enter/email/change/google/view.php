<?php
namespace addons\content_enter\email\change\google;

class view extends \addons\content_enter\main\view
{
	public function config()
	{
		parent::config();

		$this->data->page['title']   = T_('Change google mail');
		$this->data->page['desc']    = $this->data->page['title'];

		$this->data->old_google_mail = self::get_enter_session('old_google_mail');
		$this->data->new_google_mail = self::get_enter_session('new_google_mail');
	}

}
?>