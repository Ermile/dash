<?php
namespace addons\includes\cls\form;

class options extends \lib\form
{
	public function __construct($function=null)
	{
		if ($function && method_exists($this, $function))
		{
			$this->$function();
		}
		else
		{
			// if(DEBUG)
			// 	var_dump('Please pass correct function name as parameter');
			return;
		}
	}

	/**
	 * Ermile CMS General Settings
	 * @return [type] [description]
	 */
	private function general()
	{
		$this->title = $this->make('text')->name('site-title')
			->label(T_('Site'). ' '. T_('title'))
			->maxlength(20)
			->pl(T_('Site title'))
			->desc(T_("For multilanguage sites enter title in English and translate it"));

		$this->desc = $this->make('text')->name('site-desc')
			->label(T_('Site'). ' '.T_('Description'))
			->maxlength(160)
			->pl(T_('Site Description'))
			->desc(T_("Explain site and porpose of it in a few words"));

		$this->email = $this->make('email')->name('site-email')
			->label(T_('Email'))
			->maxlength(50)
			->pl(T_('Email'))
			// ->desc(T_("Explain site and porpose of it in a few words"))
			;

		$this->tabColor = $this->make('text')->name('site-tabColor')
			->label(T_('Site'). ' '.T_('Tab Color'))
			->maxlength(160)
			->pl(T_('Site Tab Color in Mobile'))
			// ->desc(T_("Explain site and porpose of it in a few words"));
			;

		// $this->url = $this->make('url')->name('site-url')
		// 	->label(T_('Site'). ' '.T_('main URL'))
		// 	->maxlength(50)
		// 	->pl(T_('Site main Address (URL)'))
		// 	->desc(T_("Explain site and porpose of it in a few words"));
	}


	/**
	 * Ermile CMS General Settings
	 * @return [type] [description]
	 */
	private function config()
	{
		$this->config_coming = $this->make('checkbox')
			->name('config-coming')
			->class('checkbox')
			->label(T_('Enable coming soon'));

		$this->config_debug = $this->make('checkbox')
			->name('config-debug')
			->class('checkbox')
			->label(T_('debug mode status'));

		$this->config_saveAsCookie = $this->make('checkbox')
			->name('config-saveAsCookie')
			->class('checkbox')
			->label(T_('Save as cookie'));

		$this->config_logVisitors = $this->make('checkbox')
			->name('config-logVisitors')
			->class('checkbox')
			->label(T_('Log visitors'));

		$this->config_useMainAccount = $this->make('checkbox')
			->name('config-useMainAccount')
			->class('checkbox')
			->label(T_('Use main account'));

		$this->config_mainAccount = $this->make('text')
			->attr('data-parent', 'config-useMainAccount')
			->class('en')
			->name('config-mainAccount')
			->label(T_('Main account'))
			->maxlength(30);

		$this->config_defaultLang = $this->make('radio')
			->name('config-defaultLang')
			->label(T_('Default Language'))
			->pl(T_('Default Language'));


		$this->config_seperator3 = $this->make('seperator')
			->label(T_('URL Settings'));

		$this->config_fakeSub = $this->make('checkbox')
			->name('config-fakeSub')
			->class('checkbox')
			->label(T_('Use fake subdomain'));

		$this->config_https = $this->make('checkbox')
			->name('config-https')
			->class('checkbox')
			->label(T_('Support https'));

		$this->config_shortURL = $this->make('checkbox')
			->name('config-shortURL')
			->class('checkbox')
			->label(T_('allow short URL'));

		$this->config_forceShortURL = $this->make('checkbox')
			->name('config-forceShortURL')
			->attr('data-parent', 'config-shortURL')
			->class('checkbox')
			->label(T_('force using short URL'));


		$this->config_seperator1 = $this->make('seperator')
			->label(T_('Feature status'));

		$this->config_sms = $this->make('checkbox')
			->name('config-sms')
			->class('checkbox')
			->label(T_('Use SMS service'));

		$this->config_social = $this->make('checkbox')
			->name('config-social')
			->class('checkbox')
			->label(T_('Use social networks'));

		$this->config_account = $this->make('checkbox')
			->name('config-account')
			->class('checkbox')
			->label(T_('Use account'));


		$this->config_seperator2 = $this->make('seperator')
			->label(T_('Multi domain'));

		$this->config_multiDomain = $this->make('checkbox')
			->name('config-multiDomain')
			->class('checkbox')
			->label(T_('Use multi domain'));

		$this->config_defaultTld = $this->make('radio')
			->attr('data-parent', 'config-multiDomain')
			// ->attr('data-init', 'hide')
			->name('config-defaultTld')
			->label(T_('Default Tld'))
			->pl(T_('Default Tld'));

		$this->config_domainSame = $this->make('checkbox')
			->attr('data-parent', 'config-multiDomain')
			->attr('data-disable', true)
			->name('config-domainSame')
			->class('checkbox')
			->label(T_('domain name is different?'));

		$this->config_domainName = $this->make('text')
			->attr('data-parent', 'config-domainSame')
			->attr('data-init', 'hide')
			// ->attr('data-reverse', 'true')
			->name('config-domainName')
			->label(T_('Main domain name'))
			->attr('data-before','http[s]://')
			->attr('data-after','.com')
			->class('en')
			->maxlength(50);

		$this->config_redirectToMain = $this->make('checkbox')
			->attr('data-parent', 'config-multiDomain')
			->name('config-redirectToMain')
			->class('checkbox')
			->label(T_('Redirect to main doamin'));

		$this->config_mainSite = $this->make('text')
			->attr('data-parent', 'config-multiDomain')
			->name('config-mainSite')
			->attr('disabled', 'disabled')
			->label(T_('Redirect to main doamin'))
			->attr('data-before','http[s]://')
			->class('en')
			->maxlength(50);

	}


	/**
	 * Ermile Social Network Settings
	 * @return [type] [description]
	 */
	private function social()
	{
		$this->twitter = $this->make('text')
			->name('twitter')
			->label(T_('Twitter'))
			->attr('data-before','twitter.com/')
			->class('en')
			->maxlength(30);

		$this->facebook = $this->make('text')
			->name('facebook')
			->label(T_('Facebook'))
			->attr('data-before','facebook.com/')
			->class('en')
			->maxlength(60);

		$this->googleplus = $this->make('text')
			->name('googleplus')
			->label(T_('Google Plus'))
			->attr('data-before','plus.google.com/')
			->class('en')
			->maxlength(60);

		$this->github = $this->make('text')
			->name('github')
			->label(T_('Github'))
			->attr('data-before','github.com/')
			->class('en')
			->maxlength(60);

		$this->linkedin = $this->make('text')
			->name('linkedin')
			->label(T_('Linkedin'))
			->attr('data-before','linkedin.com/in/')
			->class('en')
			->maxlength(60);

		$this->telegram = $this->make('text')
			->name('telegram')
			->label(T_('Telegram'))
			->attr('data-before','telegram.me/')
			->class('en')
			->maxlength(60);

		$this->aparat = $this->make('text')
			->name('aparat')
			->label(T_('Aparat'))
			->attr('data-before','aparat.com/')
			->class('en')
			->maxlength(60);
	}


	/**
	 * Create twitter elements
	 * @return [type] [description]
	 */
	private function twitter()
	{
		$this->twitter_status = $this->make('checkbox')
			->name('twitter-status')
			->class('checkbox')
			->label(T_('Status of twitter sharing'));

		$this->twitter_ConsumerKey = $this->make('text')
			->attr('data-parent', 'twitter-status')
			->name('twitter-ConsumerKey')
			->label(T_('Twitter'). ' '. T_('ConsumerKey'))
			->class('en')
			->maxlength(20);

		$this->twitter_ConsumerSecret = $this->make('text')
			->attr('data-parent', 'twitter-status')
			->name('twitter-ConsumerSecret')
			->label(T_('Twitter'). ' '. T_('ConsumerSecret'))
			->class('en')
			->maxlength(60);

		$this->twitter_AccessToken = $this->make('text')
			->attr('data-parent', 'twitter-status')
			->name('twitter-AccessToken')
			->label(T_('Twitter'). ' '. T_('AccessToken'))
			->class('en')
			->maxlength(60);

		$this->twitter_AccessTokenSecret = $this->make('text')
			->attr('data-parent', 'twitter-status')
			->name('twitter-AccessTokenSecret')
			->label(T_('Twitter'). ' '. T_('AccessTokenSecret'))
			->class('en')
			->maxlength(60);
	}


	/**
	 * Create facebook elements
	 * @return [type] [description]
	 */
	private function facebook()
	{
		$this->fb_status = $this->make('checkbox')
			->name('fb-status')
			->class('checkbox')
			->label(T_('Status of facebook sharing'));

		$this->fb_app_id = $this->make('number')
			->attr('data-parent', 'fb-status')
			->name('fb-app_id')
			->label(T_('Facebook'). ' '. T_('app_id'))
			->class('en')
			->maxlength(20);

		$this->fb_app_secret = $this->make('text')
			->attr('data-parent', 'fb-status')
			->name('fb-app_secret')
			->label(T_('Facebook'). ' '. T_('app_secret'))
			->class('en')
			->maxlength(40);

		$this->fb_redirect_url = $this->make('url')
			->attr('data-parent', 'fb-status')
			->name('fb-redirect_url')
			->label(T_('Facebook'). ' '. T_('redirect_url'))
			->class('en')
			->maxlength(90);

		$this->fb_required_scope = $this->make('text')
			->attr('data-parent', 'fb-status')
			->name('fb-required_scope')
			->label(T_('Facebook'). ' '. T_('required_scope'))
			->class('en')
			->maxlength(60);

		$this->fb_page_id = $this->make('text')
			->attr('data-parent', 'fb-status')
			->name('fb-page_id')
			->label(T_('Facebook'). ' '. T_('page_id'))
			->class('en')
			->maxlength(20);

		$this->fb_access_token = $this->make('text')
			->attr('data-parent', 'fb-status')
			->name('fb-access_token')
			->label(T_('Facebook'). ' '. T_('access_token'))
			->class('en')
			->maxlength(300);

		$this->fb_client_token = $this->make('text')
			->attr('data-parent', 'fb-status')
			->name('fb-client_token')
			->label(T_('Facebook'). ' '. T_('client_token'))
			->class('en')
			->maxlength(60);
	}


	/**
	 * Create telegram elements
	 * @return [type] [description]
	 */
	private function telegram()
	{
		$this->tg_status = $this->make('checkbox')
			->name('tg-status')
			->class('checkbox')
			->label(T_('Status of telegram sharing'));

		$this->tg_key = $this->make('text')
			->attr('data-parent', 'tg-status')
			->name('tg-key')
			->label(T_('Telegram'). ' '. T_('Key'))
			->class('en')
			->maxlength(200);

		$this->tg_bot = $this->make('text')
			->attr('data-parent', 'tg-status')
			->name('tg-bot')
			->label(T_('Telegram'). ' '. T_('bot name'))
			->class('en')
			->maxlength(50);

		$this->tg_hookFolder = $this->make('text')
			->attr('data-parent', 'tg-status')
			->name('tg-hookFolder')
			->label(T_('Telegram'). ' '. T_('hook folder'))
			->class('en')
			->maxlength(50);

		$this->tg_hook = $this->make('text')
			->attr('data-parent', 'tg-status')
			->name('tg-hook')
			->label(T_('Telegram'). ' '. T_('hook url'))
			->class('en')
			->attr('disabled', 'disabled')
			->maxlength(200);

		$this->tg_debug = $this->make('checkbox')
			->name('tg-debug')
			->class('checkbox')
			->label(T_('Debug mode'));

		$this->tg_channel = $this->make('text')
			->attr('data-parent', 'tg-status')
			->name('tg-channel')
			->label(T_('Telegram'). ' '. T_('channel'))
			->class('en')
			->maxlength(200);

		$this->tg_botan = $this->make('text')
			->attr('data-parent', 'tg-status')
			->name('tg-botan')
			->label(T_('Telegram'). ' '.T_('botan'). ' '. T_('Key'))
			->class('en')
			->maxlength(200);
	}


	/**
	 * Create sms elements
	 * @return [type] [description]
	 */
	private function sms()
	{
		$this->sms_debug = $this->make('checkbox')
			->name('sms-debug')
			->class('checkbox')
			->label(T_('Simulate SMS (Debugging)'));

		$this->sms_seperator1 = $this->make('seperator')
			->label(T_('SMS api detail'));

		$this->sms_name = $this->make('radio')
			->name('sms-name')
			->label(T_('SMS service'))
			->pl(T_('SMS service'));

		$this->sms_apikey = $this->make('text')
			->name('sms-apikey')
			->label(T_('SMS'). ' '. T_('apikey'))
			->class('en')
			->maxlength(100);

		$this->sms_line1 = $this->make('number')
			->name('sms-line1')
			->label(T_('SMS'). ' '. T_('line number'). ' 1')
			->maxlength(20);

		$this->sms_line2 = $this->make('number')
			->name('sms-line2')
			->label(T_('SMS'). ' '. T_('line number'). ' 2')
			->maxlength(20);

		$this->sms_iran = $this->make('checkbox')
			->name('sms-iran')
			->class('checkbox')
			->label(T_('Regional restriction'));

		$this->sms_seperator2 = $this->make('seperator')
			->label(T_('Message detail'));

		$this->sms_header = $this->make('text')
			->name('sms-header')
			->label(T_('Message header'))
			->maxlength(20);

		$this->sms_footer = $this->make('text')
			->name('sms-footer')
			->label(T_('Message footer'))
			->maxlength(20);

		$this->sms_one = $this->make('checkbox')
			->name('sms-one')
			->class('checkbox')
			->label(T_('Force one message'));

		$this->sms_seperator3 = $this->make('seperator')
			->label(T_('Send message in custom situation'));

		$this->sms_signup = $this->make('checkbox')
			->name('sms-signup')
			->class('checkbox')
			->label(T_('Send message for'). ' '. T_('signup'));

		$this->sms_verification = $this->make('checkbox')
			->name('sms-verification')
			->class('checkbox')
			->label(T_('Send message for'). ' '. T_('verification'));

		$this->sms_recovery = $this->make('checkbox')
			->name('sms-recovery')
			->class('checkbox')
			->label(T_('Send message for'). ' '. T_('recovery'));

		$this->sms_changepass = $this->make('checkbox')
			->name('sms-changepass')
			->class('checkbox')
			->label(T_('Send message for'). ' '. T_('changepass'));

		$this->sms_verification = $this->make('checkbox')
			->name('sms-verification')
			->class('checkbox')
			->label(T_('Send message for'). ' '. T_('verification'));
	}


	/**
	 * Create account elements
	 * @return [type] [description]
	 */
	private function account()
	{
		$this->account_redirect = $this->make('radio')
			->name('account-redirect')
			->label(T_('After login redirect to'));


		$this->account_seperator2 = $this->make('seperator')
			->label(T_('Increase account security'));

		$this->account_passphrase = $this->make('checkbox')
			->name('account-passphrase')
			->class('checkbox')
			->label(T_('Access with pass phrase'));

		$this->account_passkey = $this->make('text')
			->attr('data-parent', 'account-passphrase' )
			->name('account-passkey')
			->label(T_('Pass phrase key'))
			->class('en')
			->maxlength(20);

		$this->account_passvalue = $this->make('text')
			->attr('data-parent', 'account-passphrase' )
			->name('account-passvalue')
			->label(T_('Pass phrase value'))
			->class('en')
			->maxlength(20);

		$this->account_seperator3 = $this->make('seperator')
			->label(T_('Status of account service'));

		$this->account_register = $this->make('checkbox')
			->name('account-register')
			->class('checkbox')
			->label(T_('Allow registration'));

		$this->account_default = $this->make('radio')
			->attr('data-parent', 'account-register' )
			->name('account-default')
			->label(T_('Default permission'))
			->pl(T_('Default permission'));

		$this->account_recovery = $this->make('checkbox')
			->name('account-recovery')
			->class('checkbox')
			// ->attr('data-relation', 'account-passphrase' )
			->label(T_('Allow recovery account'));
	}
}
?>