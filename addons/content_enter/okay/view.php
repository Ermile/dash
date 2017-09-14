<?php
namespace addons\content_enter\okay;


class view extends \addons\content_enter\main\view
{
	public function config()
	{
		// read parent config to fill the mobile input and other thing
		parent::config();

		$this->data->page['title']   = T_('Horray!');
		$this->data->page['special'] = true;
		$this->data->page['desc']    = T_('Live and learn');


		$this->data->redirect_url = $this->url('base');
		if(self::get_enter_session('redirect_url'))
		{
			$this->data->redirect_url = self::get_enter_session('redirect_url');
		}
	}
}
?>