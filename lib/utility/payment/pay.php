<?php
namespace lib\utility\payment;
use \lib\debug;
use \lib\option;
use \lib\utility;
use \lib\db\logs;

class pay
{

    /**
     * default callback url
     *
     * @var        string
     */
    public static $default_callback_url = 'enter/payment/verify';

    public static $user_id = null;

    public static $log_data = null;

    /**
     * Gets the callbck url.
     * for example for parsian payment redirect
     * http://tejarak.com/fa/enter/payment/verify/parsian
     *
     * @param      <type>  $_payment  The payment
     */
    private static function get_callbck_url($_payment)
    {
        $host = Protocol."://" . \lib\router::get_root_domain();
        $lang = \lib\define::get_current_language_string();
        $callback_url =  $host;
        $callback_url .= $lang;
        $callback_url .= '/'. self::$default_callback_url;
        $callback_url .= '/'. $_payment;
        return $callback_url;
    }

    use pay\zarinpal;
    use pay\parsian;
}
?>