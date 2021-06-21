<?php
namespace content_su\sms;

class view
{
	public static function config()
	{


		\dash\data::page_title(T_("Send Sms to user"));
		\dash\data::page_desc(T_("Send every sms to every user by mobile"));

		\dash\data::page_pictogram('envelope-o');

		\dash\data::badge_link(\dash\url::here());
		\dash\data::badge_text(T_('Dashboard'));

		\dash\data::bodyclass('unselectable');
		\dash\data::include_adminPanel(true);
		\dash\data::include_css(false);



	}
}
?>