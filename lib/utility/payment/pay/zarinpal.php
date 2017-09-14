<?php
namespace lib\utility\payment\pay;
use \lib\debug;
use \lib\option;
use \lib\utility;
use \lib\db\logs;

trait zarinpal
{

     /**
     * pay by zarinpal payment
     * check config of zarinpal
     * save transaction by conditon request
     * redirect to payment url
     *
     * @param      <type>  $_user_id  The user identifier
     * @param      <type>  $_amount   The amount
     * @param      array   $_options  The options
     */
    public static function zarinpal($_user_id, $_amount, $_options = [])
    {
        $log_meta =
        [
            'data' => self::$log_data,
            'meta' =>
            [
                'input'   => func_get_args(),
                'session' => $_SESSION,
            ]
        ];

        if(!$_user_id || !is_numeric($_user_id))
        {
            return false;
        }

        if(is_numeric($_amount) && $_amount > 0 && $_amount == round($_amount, 0))
        {
            // no problem to continue!
        }
        else
        {
            logs::set('pay:zarinpal:amount:invalid', $_user_id, $log_meta);
            debug::error(T_("Invalid amount"));
            return false;
        }

        if(!option::config('zarinpal', 'status'))
        {
            logs::set('pay:zarinpal:status:false', $_user_id, $log_meta);
            debug::error(T_("The zarinpal payment on this service is locked"));
            return false;
        }

        if(!option::config('zarinpal', 'MerchantID'))
        {
            logs::set('pay:zarinpal:MerchantID:not:set', $_user_id, $log_meta);
            debug::error(T_("The zarinpal payment MerchantID not set"));
            return false;
        }

        $zarinpal = [];
        $zarinpal['MerchantID'] = option::config('zarinpal', 'MerchantID');

        if(option::config('zarinpal', 'Description'))
        {
            $zarinpal['Description'] = option::config('zarinpal', 'Description');
        }

        if(option::config('zarinpal', 'CallbackURL'))
        {
            $zarinpal['CallbackURL'] = option::config('zarinpal', 'CallbackURL');
        }
        else
        {
            $zarinpal['CallbackURL'] = self::get_callbck_url('zarinpal');
        }

        $zarinpal['Amount'] = $_amount;

        if(isset($_options['mobile']))
        {
            $zarinpal['Mobile'] = $_options['mobile'];
        }


        if(isset($_options['email']))
        {
            $zarinpal['Email'] = $_options['email'];
        }

        $transaction_start =
        [
            'caller'         => 'payment:zarinpal',
            'title'          => T_("Pay by zarinpal payment"),
            'user_id'        => $_user_id,
            'plus'           => $_amount,
            'payment'        => 'zarinpal',
            'type'           => 'money',
            'unit'           => 'toman',
            'date'           => date("Y-m-d H:i:s"),
            'amount_request' => $_amount,
        ];

        //START TRANSACTION BY CONDITION REQUEST
        $transaction_id = \lib\utility\payment\transactions::start($transaction_start);

        $log_meta['data'] = self::$log_data = $transaction_id;

        if(!debug::$status || !$transaction_id)
        {
            return false;
        }

        if(isset($_options['turn_back']))
        {
            // save turn back url to redirect user to this url after coplete pay
            $_SESSION['turn_back'][$transaction_id] = $_options['turn_back'];
        }

        \lib\utility\payment\payment\zarinpal::$user_id  = $_user_id;
        \lib\utility\payment\payment\zarinpal::$log_data = self::$log_data;

        $redirect = \lib\utility\payment\payment\zarinpal::pay($zarinpal);

        if($redirect)
        {
            $payment_response = \lib\utility\payment\payment\zarinpal::$payment_response;
            if(isset($payment_response->Authority))
            {
                // save amount and autority in session to get when verifying
                $_SESSION['amount']['zarinpal'][$payment_response->Authority]                   = [];
                $_SESSION['amount']['zarinpal'][$payment_response->Authority]['amount']         = $_amount;
                $_SESSION['amount']['zarinpal'][$payment_response->Authority]['transaction_id'] = $transaction_id;

                $payment_response = json_encode((array) $payment_response, JSON_UNESCAPED_UNICODE);
                \lib\db\transactions::update(['condition' => 'redirect', 'payment_response' => $payment_response], $transaction_id);

                // redirect to bank
                (new \lib\redirector($redirect))->redirect();

                return true;
            }
            else
            {
                logs::set('pay:zarinpal:Authority:not:set', $_user_id, $log_meta);
                debug::error(T_("Zarinpal payment Authority not found"));
                return false;
                return false;
            }
        }
        else
        {
            return false;
        }
    }

}
?>