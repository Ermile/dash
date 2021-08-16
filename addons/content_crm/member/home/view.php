<?php
namespace content_crm\member\home;

class view
{

	public static function config()
	{
		\dash\data::page_title(T_("List of users"));
		\dash\data::page_desc(T_('Some detail about your users!'));
		\dash\data::page_desc(T_('Check list of members and search or filter in them to find your user.'));
		\dash\data::page_desc(\dash\data::page_desc(). ' '. T_('Also add or edit specefic user.'));
		\dash\data::page_pictogram('users');

		$allNeedSearch = \dash\request::get();
		unset($allNeedSearch['page']);
		unset($allNeedSearch['q']);
		\dash\data::allNeedSearch($allNeedSearch);

		$args =
		[
			'sort'  => \dash\request::get('sort'),
			'order' => \dash\request::get('order'),
		];

		$search_string            = \dash\request::get('q');
		if($search_string)
		{
			\dash\data::page_title(\dash\data::page_title(). ' | '. T_('Search for :search', ['search' => $search_string]));
		}

		\dash\data::badge_link(\dash\url::this(). '/add');
		\dash\data::badge_text(T_('Add new user'));

		$exportGet = \dash\request::get();
		$exportGet['export'] = 1;

		\dash\data::badge2_link(\dash\url::this().'?'. http_build_query($exportGet));
		\dash\data::badge2_text(T_('Export'));


		if(!$args['order'])
		{
			$args['order'] = 'desc';
		}

		if($args['sort'])
		{
			$args['sort'] = 'users.'. $args['sort'];
		}

		if(\dash\request::get('status'))
		{
			$args['users.status'] = \dash\request::get('status');
		}

		if(!isset($args['users.status']))
		{
			$args['users.status'] = 'active';
		}

		if(isset($args['users.status']) && $args['users.status'] === 'all')
		{
			unset($args['users.status']);
		}

		if(\dash\request::get('findusername'))
		{
			$args['users.username'] = \dash\request::get('findusername');
		}

		if(\dash\request::get('findmobile') || \dash\request::get('findmobile') == "0")
		{
			$args['mobile'] = \dash\request::get('findmobile');
		}


		if(\dash\request::get('findemail'))
		{
			$args['email'] = \dash\request::get('findemail');
		}

		if(\dash\request::get('chatid') && \dash\request::get('chatid') !== 'non')
		{
			$args['join_user_telegram'] = true;
			unset($args['users.status']);
		}

		if(\dash\request::get('android_uniquecode') && \dash\request::get('android_uniquecode') !== 'non')
		{
			$args['join_user_android'] = true;
			unset($args['users.status']);
		}


		if(\dash\request::get('permission'))
		{
			$args['permission'] = \dash\request::get('permission');
		}


		self::advance_filter($args);

		$sortLink = \dash\app\sort::make_sortLink(\dash\app\user::$sort_field, \dash\url::this());

		if(\dash\permission::supervisor() && in_array(\dash\request::get('duplicate'), ['mobile', 'email', 'username']))
		{
			$args['check_duplicate'] = \dash\request::get('duplicate');
		}


		if(\dash\request::get('export'))
		{
			$args['pagination'] = false;
			$args['pagenation'] = false;
			$args['public_show_field'] = 'users.mobile';
		}

		$dataTable = \dash\app\user::list(\dash\request::get('q'), $args);

		if(!is_array($dataTable))
		{
			$dataTable = [];
		}

		if(\dash\request::get('export'))
		{
			\dash\utility\export::csv(['name' => 'export_member', 'data' => $dataTable,]);
		}

		if($dataTable && is_array($dataTable))
		{
			\dash\app\user_telegram::dataTableList($dataTable);
			\dash\app\user_android::dataTableList($dataTable);
		}

		\dash\data::sortLink($sortLink);
		\dash\data::dataTable($dataTable);


		$check_empty_datatable = $args;
		unset($check_empty_datatable['sort']);
		unset($check_empty_datatable['order']);

		// set dataFilter
		$dataFilter = \dash\app\sort::createFilterMsg($search_string, $check_empty_datatable);
		\dash\data::dataFilter($dataFilter);

	}




	private static function advance_filter(&$args)
	{
		$allow_filter =
		[
			'username',
			'avatar',
			'displayname',
			'mobile',
			'email',
			'password',
			'twostep',
			'permission',
			'language',

		];

		foreach ($allow_filter as $key => $value)
		{
			if(\dash\request::get($value) === 'yes')
			{
				$args[$value] = [" IS ", " NOT NULL "];
			}
			elseif(\dash\request::get($value) === 'no')
			{
				$args[$value] = [" IS ", " NULL "];
			}
		}
	}
}
?>