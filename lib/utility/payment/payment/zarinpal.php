<?php
namespace lib\utility\payment\payment;
use \lib\debug;

class zarinpal
{

    public static $user_id  = null;
    public static $log_data = null;
    public static $payment_response = [];
    /**
     * pay price
     *
     * @param      array  $_args  The arguments
     */
    public static function pay($_args = [])
    {
        $log_meta =
        [
            'data' => self::$log_data,
            'meta' =>
            [
                'args' => func_get_args()
            ],
        ];

        // if soap is not exist return false
        if(!class_exists("soapclient"))
        {
            \lib\db\logs::set('payment:zarinpal:soapclient:not:install', self::$user_id);
            debug::error(T_("Can not connect to zarinpal gateway. Install it!"));
            return false;
        }

        try
        {
            $client = @new \soapclient('https://de.zarinpal.com/pg/services/WebGate/wsdl');

            $result                 = $client->PaymentRequest($_args);
            self::$payment_response = $result;
            $msg                    = self::msg($result->Status);

            $log_meta['meta']['soapclient'] = $result;
            $log_meta['meta']['msg']        = $msg;

            if ($result->Status == 100)
            {
                \lib\db\logs::set('payment:zarinpal:redirect', self::$user_id, $log_meta);

                $url = "https://www.zarinpal.com/pg/StartPay/" . $result->Authority;
                return $url;
            }
            else
            {
                \lib\db\logs::set('payment:zarinpal:error', self::$user_id, $log_meta);
                debug::error($msg);
                return false;
            }
        }
        catch (SoapFault $e)
        {
            \lib\db\logs::set('payment:zarinpal:error:load:web:services', self::$user_id, $log_meta);
            debug::error(T_("Error in load web services"));
            return false;
        }
    }


    /**
     * { function_description }
     *
     * @param      array  $_args  The arguments
     */
    public static function verify($_args = [])
    {

        $log_meta =
        [
            'data' => self::$log_data,
            'meta' =>
            [
                'args' => func_get_args()
            ],
        ];

        try
        {
            $client = @new \soapclient('https://de.zarinpal.com/pg/services/WebGate/wsdl');

            $result                         = $client->PaymentVerification($_args);
            self::$payment_response         = $result;
            $msg                            = self::msg($result->Status);
            $log_meta['meta']['soapclient'] = $result;

            if($result->Status == 100)
            {
                return true;
            }
            elseif($result->Status == 101)
            {
                return true;
            }
            else
            {
                \lib\db\logs::set('payment:zarinpal:verify:error', self::$user_id, $log_meta);
                debug::error($msg);
                return false;
            }
        }
        catch (SoapFault $e)
        {
            \lib\db\logs::set('payment:zarinpal:verify:error:load:web:services', self::$user_id, $log_meta);
            return debug::error(T_("Error in load web services"));
        }
    }


    /**
     * payment msg
     *
     * @param      <type>  $_status  The status
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    private static function msg($_status)
    {
        $msg = null;
        switch ($_status)
        {
            case -1:   $msg = T_("The submited inforamation are incomplete."); break;
            case -2:   $msg = T_("IP or merchant code of the host is incorrect"); break;
            case -3:   $msg = T_("Due to Shaparak's limitations, It's impossible to pay the specified amount"); break;
            case -4:   $msg = T_("The host level is below silver level"); break;
            case -11:  $msg = T_("Nothing found for the specified request"); break;
            case -12:  $msg = T_("It's impossible to edit the request"); break;
            case -21:  $msg = T_("No financial operation found for this transaction"); break;
            case -22:  $msg = T_("Transaction faild"); break;
            case -33:  $msg = T_("The specified transaction amount does not match with the payed amount"); break;
            case -34:  $msg = T_("Highest amount of transaction is passed as a result of number or amount"); break;
            case -40:  $msg = T_("Access unavilable to method"); break;
            case -41:  $msg = T_("Invalid information is sent for AdditionalData"); break;
            case -42:  $msg = T_("Valid lifespan of ID must be between 30 minutes to 45 days"); break;
            case -54:  $msg = T_("The request is archived"); break;
            case 100:  $msg = T_("Operation was successfully done"); break;
            case 101:  $msg = T_("Payment operation was successfull and PatmentVerification of the transaction has already done"); break;
            default:   $msg = T("The error code is :code", ['code' => $result->Status]); break;
        }
        return $msg;
    }

}
?>