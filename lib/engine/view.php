<?php
namespace dash\engine;

class view
{

	public static function variable()
	{
		// default display value
		\dash\data::display_mvc("includes/html/display-mvc.html");
		\dash\data::display_dash("includes/html/display-dash.html");
		\dash\data::display_enter("includes/html/display-enter.html");
		// add special pages to display array to use without name
		\dash\data::display_main("content/main/layout.html");
		\dash\data::display_account("content_account/home/layout.html");
		\dash\data::display_cmsMain("content_cms/layout.html");
		\dash\data::display_crmMain("content_crm/layout.html");
		\dash\data::display_payMain("content_pay/layout.html");

		\dash\data::display_pagination("includes/html/inc_pagination.html");
		\dash\data::display_pagination_np("includes/html/inc_pagination_np.html");
		\dash\data::display_hive("includes/html/inc_hive.html");
		\dash\data::display_spay("includes/html/inc_spay.html");

		\dash\data::display_commentadd("includes/html/inc_commentadd.html");
		\dash\data::display_commentlist("includes/html/inc_commentlist.html");

		// return all url detail
		\dash\data::url(\dash\url::all());

		// return all parameters and clean it
		\dash\data::requestGET(\dash\request::get());

		// ----- language variable
		\dash\data::lang_list(\dash\language::all(true));
		\dash\data::lang_current(\dash\language::current());
		\dash\data::lang_default(\dash\language::primary());

		// save all options to use in display
		\dash\data::options(\dash\option::config());

		\dash\data::page_title(null);
		\dash\data::page_seotitle(null);
		\dash\data::page_desc(null);
		\dash\data::page_special(null);

		\dash\data::bodyclass(null);

		$user_detail = \dash\user::detail();
		\dash\data::user($user_detail);
		\dash\data::login($user_detail);

		// set detail of browser
		\dash\data::browser(\dash\utility\browserDetection::browser_detection('full_assoc'));
		\dash\data::visitor('not ready!');

		$is_app = \dash\request::is_app();
		\dash\data::xAppRequest($is_app);

		if($is_app === 'android')
		{
			\dash\data::isAndroid(true);
		}

		if($is_app === 'ios')
		{
			\dash\data::isIos(true);
		}

		// define default value for global
		\dash\data::global_title(null);
		\dash\data::global_login(\dash\user::login());
		\dash\data::global_lang(\dash\language::current());
		\dash\data::global_direction(\dash\language::current('direction'));
		\dash\data::global_page(implode('_', \dash\url::dir()));
		if(!\dash\data::global_page() && \dash\url::module() === null)
		{
			\dash\data::global_page('home');
		}
		\dash\data::global_subdomain(\dash\url::subdomain());
		\dash\data::global_content(\dash\url::content());
		if(\dash\data::global_content() === null)
		{
			\dash\data::global_content('site');
		}

		if(\dash\data::user_theme())
		{
			if(\dash\data::user_theme() === 'default')
			{
				\dash\data::global_theme(null);
			}
			else
			{
				\dash\data::global_theme(\dash\data::user_theme());
			}
		}
		else
		{
			// set theme null on start
			\dash\data::global_theme(null);
			// enable night theme on su
			if(\dash\url::content() === 'su')
			{
				\dash\data::global_theme('night');
			}
			elseif(\dash\url::content() === 'account')
			{
				\dash\data::global_theme('light');
			}
		}


		// make xaccess link
		$xaccessLink = [];
		if(!\dash\option::config('no_subdomain'))
		{
			$xaccessLink['cms'] = \dash\url::kingdom(). '/cms';
		}
		else
		{
			$xaccessLink['cms'] = \dash\url::sitelang(). '/cms';
		}

		\dash\data::xaccessLink($xaccessLink);


		\dash\data::dev(\dash\option::config('dev'));

		\dash\data::site_title(T_(\dash\option::config('site', 'title')));
		\dash\data::site_desc(T_(\dash\option::config('site', 'desc')));
		\dash\data::site_slogan(T_(\dash\option::config('site', 'slogan')));
		\dash\data::site_logo(\dash\url::static(). '/images/logo.png');
		// set custom logo
		if(\dash\option::config('site', 'logo'))
		{
			\dash\data::site_logo(\dash\url::site(). \dash\option::config('site', 'logo'));
		}

		// add service detail
		\dash\data::service_title(T_('Jibres'));
		\dash\data::service_desc(T_('Integrated Ecommerce Platform Software / Quickly Start Free! Online Store Website & Mobile Online Store & Social Marketing & POS Software / SEO Optimized'));
		\dash\data::service_slogan(T_('Sell and enjoy'));
		\dash\data::service_logo(\dash\url::static(). '/siftal/images/logo/jibres.png');
		\dash\data::service_url('https://jibres.ir');

		// toggle side bar
		if(\dash\user::sidebar() === null || \dash\user::sidebar() === true)
		{
			\dash\data::userToggleSidebar(true);
		}

		// define default value for include
		\dash\data::include_siftal(true);
		\dash\data::include_css(true);
		\dash\data::include_js(true);

		self::deadbrowserDetection();
	}


	/**
	 * set title for pages depending on condition
	 */
	public static function set_title()
	{
		if($page_title = \dash\data::page_title())
		{
			// set title of locations if exist in breadcrumb
			if(\dash\data::get('breadcrumb', $page_title))
			{
				$page_title = \dash\data::get('breadcrumb', $page_title);
			}
			// replace title of page
			if(!\dash\data::page_special())
			{
				$page_title = ucwords(str_replace('-', ' ', $page_title));
			}

			// translate all title at last step
			$page_title = T_($page_title);

			\dash\data::page_title($page_title);
			// fill page title into seo title
			if(!\dash\data::page_seotitle())
			{
				\dash\data::page_seotitle($page_title);
			}

			if(\dash\data::page_special())
			{
				\dash\data::global_title(\dash\data::page_seotitle());
			}
			else
			{
				\dash\data::global_title(\dash\data::page_seotitle(). ' | '. \dash\data::site_title());
			}
		}
		else
		{
			\dash\data::global_title(\dash\data::site_title());
			// if this page does not have title use site title
			\dash\data::page_title(\dash\data::site_title());
			\dash\data::page_special(true);
		}
		\dash\data::global_title(strip_tags(\dash\data::global_title()));
		\dash\data::global_short_title(substr(\dash\data::global_title(), 0, strrpos(substr(\dash\data::global_title(), 0, 120), ' ')). '...');

		if(!\dash\data::page_desc() && \dash\data::page_desc() !== false && !\dash\url::content())
		{

			\dash\data::page_desc(\dash\data::site_desc());
		}

		self::setSocialTitle();
	}


	public static function set_cms_titles()
	{
		if(!\dash\data::get('datarow'))
		{
			if(\dash\url::module() === 'blog')
			{
				\dash\data::page_title(T_("Latest news"));
			}
			self::set_title();
			return false;
		}

		// set title
		if(\dash\data::datarow_title())
		{
			\dash\data::page_title(\dash\data::datarow_title());
		}
		// set seo title
		if(\dash\data::datarow_seotitle())
		{
			\dash\data::page_seotitle(\dash\data::datarow_seotitle());
		}

		// set desc
		if(\dash\data::datarow_excerpt())
		{
			\dash\data::page_desc(\dash\data::datarow_excerpt());
		}
		elseif(\dash\data::datarow_content())
		{
			\dash\data::page_desc(\dash\utility\excerpt::extractRelevant(\dash\data::datarow_content()));
		}
		elseif(\dash\data::datarow_desc())
		{
			\dash\data::page_desc(\dash\utility\excerpt::extractRelevant(\dash\data::datarow_desc()));
		}

		// set page cover
		$meta = \dash\data::datarow_meta();
		if(isset($meta['thumb']))
		{
			\dash\data::page_cover($meta['thumb']);
		}

		// set new title
		self::set_title();
	}

	public static function setSocialTitle()
	{
		// if allow to use social then get social network account list
		if(\dash\option::social('status'))
		{
			\dash\data::social(\dash\option::social('list'));
			\dash\data::social_status(\dash\option::social('status'));

		}

		// if we dont have page image, use site image
		if(\dash\data::page_cover())
		{
			\dash\data::page_twitterCard('summary_large_image');
		}
		elseif(\dash\data::page_video())
		{
			\dash\data::page_twitterCard('player');
		}
		else
		{

			\dash\data::page_cover(\dash\data::site_logo());
			\dash\data::page_twitterCard('summary');
		}
	}


	public static function deadbrowserDetection()
	{
		$currentBrowser = \dash\data::browser();
		$browsers =
		[
			"chrome"      => 64.0,
			"firefox"     => 60.0,
			"gecko"       => 60.0,
			"crios"       => 67.0,
			"msie"        => 11.0,
			"edge"        => 13,
			"opera"       => 50.0,
			"safari"      => 534.57,
			'applewebkit' => 600,
		];

		if (isset($browsers[$currentBrowser['browser_name']]))
		{
			if($currentBrowser['browser_name'] == 'msie')
			{
				\dash\data::youAreDead(T_("You are using Internet Explorer."). ' '. T_('Really!!!'). ' '. T_('IE is DIE!'));
			}
			elseif ($currentBrowser['browser_math_number'] < $browsers[$currentBrowser['browser_name']])
			{

				$msg = T_("You need to update your :browser to new version.", ['browser' => $currentBrowser['browser_name']. ' '. $currentBrowser['browser_math_number']]). ' '. T_('The world is changing rapidly!');

				\dash\data::youAreDead($msg);
			}
			else
			{
				// $myMsg2 = T_("Hooray! You are using the latest version");
			}
		}
		else
		{
			\dash\data::youAreDead(T_("Please use famous browser to have better experience!"));
		}
	}


	public static function lastChanges()
	{
		if(\dash\data::include_adminPanel())
		{
			\dash\data::global_siftal(true);

			$txtDesc   = '';
			if(\dash\data::user_displayname())
			{
				$txtDesc .= "<b>". \dash\data::user_displayname(). "</b><br>";
			}
			if(\dash\data::user_mobile())
			{
				$txtDesc .= \dash\utility\human::fitNumber(\dash\data::user_mobile(), 'mobile12');
			}

			$txtFooter = '';
			// $txtFooter .= '<div class="align-center txtC">';
			// $txtFooter .= ' <div>';
			// notification
			// $txtFooter .= '  <a class="btn outline lg" href="'. \dash\url::kingdom(). '/account/notification" title="'. T_("Notifications"). '">'. '<i class="sf-bell"></i>'. '</a>';
			// profile
			// $txtFooter .= '  <a class="btn outline lg" href="'. \dash\url::kingdom(). '/account/profile" title="'. T_("Profile"). '">'. '<i class="sf-user"></i>'. '</a>';
			// billing
			// $txtFooter .= '  <a class="btn outline lg" href="'. \dash\url::kingdom(). '/account/billing" title="'. T_("Billing"). '">'. '<i class="sf-credit-card"></i>'. '</a>';
			// support
			// $txtFooter .= '  <a class="btn outline lg" href="'. \dash\url::kingdom(). '/support" title="'. T_("Support center"). '">'. '<i class="sf-life-ring"></i>'. '</a>';
			// ticket
			// $txtFooter .= '  <a class="btn outline lg" href="'. \dash\url::kingdom(). '/support/ticket" title="'. T_("Tickets"). '">'. '<i class="sf-question-circle"></i>'. '</a>';
			// $txtFooter .= ' </div>';

			// $txtFooter .= '</div>';

			// add language bar
			$txtFooter .= \dash\language::langList(['html' => true, 'all' => true, 'class' => 'f']);

			\dash\data::userBadge_desc($txtDesc);
			\dash\data::userBadge_footer($txtFooter);
		}
	}

}
?>
