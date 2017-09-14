<?php
/**
@ In the name Of Allah
* The base configurations of the SARSHOMAR.
*/
self::$language                               =
[
'default'                                     => 'en',
'list'                                        => ['fa','en',],
];

self::$config['default_language']             = null;
self::$config['redirect_url']                 = null;
self::$config['main_account']                 = null;
self::$config['multi_domain']                 = false;
self::$config['redirect_to_main']             = false;
self::$config['https']                        = false;
self::$config['default_tld']                  = null;
self::$config['use_main_account']             = false;
self::$config['debug']                        = false;
self::$config['coming']                       = false;
self::$config['short_url']                    = null;
self::$config['save_as_cookie']               = false;
self::$config['log_visitors']                 = false;
self::$config['passphrase']                   = null;
self::$config['passkey']                      = null;
self::$config['passvalue']                    = null;
self::$config['default']                      = null;
self::$config['redirect']                     = null;
self::$config['register']                     = false;
self::$config['recovery']                     = false;
self::$config['fake_sub']                     = null;
self::$config['force_short_url']              = null;
self::$config['sms']                          = false;
self::$config['account']                      = false;
self::$config['domain_same']                  = false;
self::$config['domain_name']                  = null;
self::$config['main_site']                    = null;
self::$config['account_status']               = false;

/**
 * list of units
 */
self::$config['units'] =
[
	1 =>
	[
		'title' => 'toman',
		'desc'  => "Toman",
	],

	2 =>
	[
		'title' => 'dollar',
		'desc'  => "$",
	],
];
// the unit id for default
self::$config['default_unit'] = 1;

/**
* get enter option
*/
// the block type is ['ip', 'session']
// block the user if need on this way
self::$config['enter']['block_type']                    = null;
// every wrong pass or code wate for ? [second]
self::$config['enter']['wait']                          = 10;
// send resend code after ? [second]
self::$config['enter']['resend_after']                  = 60 * 1;
// life time code for ? [second]
self::$config['enter']['life_time_code']                = 60 * 5;
// you can use from this option by your rating ['telegram','call', 'sms', 'email']
self::$config['enter']['resend_rate']                   = [];
// you can use from this option by your rating ['telegram','call', 'sms', 'email']
self::$config['enter']['send_rate']                     = [];
// you can use from this option by your rating ['kavenegar',...]
self::$config['enter']['sms_rate']                      = [];
// after signup user redirect to different page
self::$config['enter']['signup_redirect']               = null;
// after login redirect to what?
// default self::$config['redirect_url']
self::$config['enter']['redirect']                      = null;
// check if call mode is enable to call to user
self::$config['enter']['call']                          = false;
// get template of call for every language
self::$config['enter']['call_template']['fa']           = null;
self::$config['enter']['call_template']['en']           = null;

/**
* the social network
*/
self::$social['status']                       = false;

self::$social['list']['telegram']             = null;
self::$social['list']['facebook']             = null;
self::$social['list']['twitter']              = null;
self::$social['list']['googleplus']           = null;
self::$social['list']['github']               = null;
self::$social['list']['linkedin']             = null;
self::$social['list']['aparat']               = null;

/**
* TELEGRAM
* t.me
*/
self::$social['telegram']['status']           = false;
self::$social['telegram']['name']             = null;
self::$social['telegram']['key']              = null;
self::$social['telegram']['bot']              = null;
self::$social['telegram']['hookFolder']       = null;
self::$social['telegram']['hook']             = null;
self::$social['telegram']['debug']            = false;
self::$social['telegram']['channel']          = null;
self::$social['telegram']['botan']            = null;

/**
* FACEBOOK
*/
self::$social['facebook']['status']           = false;
self::$social['facebook']['name']             = null;
self::$social['facebook']['key']              = null;
self::$social['facebook']['app_id']           = null;
self::$social['facebook']['app_secret']       = null;
self::$social['facebook']['redirect_url']     = null;
self::$social['facebook']['required_scope']   = null;
self::$social['facebook']['page_id']          = null;
self::$social['facebook']['access_token']     = null;
self::$social['facebook']['client_token']     = null;

/**
* TWITTER
*/
self::$social['twitter']['status']            = false;
self::$social['twitter']['name']              = null;
self::$social['twitter']['key']               = null;
self::$social['twitter']['ConsumerKey']       = null;
self::$social['twitter']['ConsumerSecret']    = null;
self::$social['twitter']['AccessToken']       = null;
self::$social['twitter']['AccessTokenSecret'] = null;

/**
* GOOGLE PLUS
*/
self::$social['googleplus']['status']         = false;
self::$social['googleplus']['name']           = null;
self::$social['googleplus']['key']            = null;


/**
* GITHUB
*/
self::$social['github']['status']             = false;
self::$social['github']['name']               = null;
self::$social['github']['key']                = null;


/**
* LINKDIN
*/
self::$social['linkedin']['status']           = false;
self::$social['linkedin']['name']             = null;
self::$social['linkedin']['key']              = null;


/**
* APARAT
*/
self::$social['aparat']['status']             = false;
self::$social['aparat']['name']               = null;
self::$social['aparat']['key']                = null;


/**
* sms kavenegar config
*/
self::$sms['kavenegar']['apikey']             = null;
self::$sms['kavenegar']['debug']              = null;
self::$sms['kavenegar']['line1']              = null;
self::$sms['kavenegar']['line2']              = null;
self::$sms['kavenegar']['iran']               = null;
self::$sms['kavenegar']['header']             = null;
self::$sms['kavenegar']['footer']             = null;
self::$sms['kavenegar']['one']                = false;
self::$sms['kavenegar']['signup']             = false;
self::$sms['kavenegar']['verification']       = false;
self::$sms['kavenegar']['recovery']           = false;
self::$sms['kavenegar']['changepass']         = false;

?>