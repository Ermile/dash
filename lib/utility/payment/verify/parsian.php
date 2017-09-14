<?php
namespace lib\utility\payment\verify;
use \lib\debug;
use \lib\option;
use \lib\utility;
use \lib\db\logs;

trait parsian
{

    /**
     * { function_description }
     *
     * @param      <type>  $_args  The arguments
     */
    public static function parsian($_args)
    {
        self::config();

        $log_meta =
        [
            'data' => self::$log_data,
            'meta' =>
            [
                'input'   => func_get_args(),
                'session' => $_SESSION,
            ]
        ];

        if(!option::config('parsian', 'status'))
        {
            logs::set('pay:parsian:status:false', self::$user_id, $log_meta);
            debug::error(T_("The parsian payment on this service is locked"));
            return self::turn_back();
        }

        if(!option::config('parsian', 'LoginAccount'))
        {
            logs::set('pay:parsian:LoginAccount:not:set', self::$user_id, $log_meta);
            debug::error(T_("The parsian payment LoginAccount not set"));
            return self::turn_back();
        }

        $Token          = isset($_REQUEST['Token'])           ? (string) $_REQUEST['Token']          : null;
        $OrderId        = isset($_REQUEST['OrderId'])         ? (string) $_REQUEST['OrderId']        : null;
        $status         = isset($_REQUEST['status'])          ? (string) $_REQUEST['status']         : null;
        $TerminalNo     = isset($_REQUEST['TerminalNo'])      ? (string) $_REQUEST['TerminalNo']     : null;
        $RRN            = isset($_REQUEST['RRN'])             ? (string) $_REQUEST['RRN']            : null;
        $TspToken       = isset($_REQUEST['TspToken'])        ? (string) $_REQUEST['TspToken']       : null;
        $HashCardNumber = isset($_REQUEST['HashCardNumber'])  ? (string) $_REQUEST['HashCardNumber'] : null;
        $Amount         = isset($_REQUEST['Amount'])          ? (string) $_REQUEST['Amount']         : null;
        $Amount         = str_replace(',', '', $Amount);
        if(!$Token)
        {
            logs::set('pay:parsian:Token:verify:not:found', self::$user_id, $log_meta);
            debug::error(T_("The parsian payment Token not set"));
            return self::turn_back();
        }

        if(isset($_SESSION['amount']['parsian'][$Token]['transaction_id']))
        {
            $transaction_id  = $_SESSION['amount']['parsian'][$Token]['transaction_id'];
        }
        else
        {
            logs::set('pay:parsian:SESSION:transaction_id:not:found', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find your transaction"));
            return self::turn_back();
        }

        $log_meta['data'] = self::$log_data = $transaction_id;

        $update =
        [
            'amount_end'       => $Amount / 10,
            'condition'        => 'pending',
            'payment_response' => json_encode((array) $_args, JSON_UNESCAPED_UNICODE),
        ];

        \lib\db\transactions::update($update, $transaction_id);
        logs::set('pay:parsian:pending:request', self::$user_id, $log_meta);

        $parsian                 = [];
        $parsian['LoginAccount'] = option::config('parsian', 'LoginAccount');
        $parsian['Token']        = $Token;

        if(isset($_SESSION['amount']['parsian'][$Token]['amount']))
        {
            $Amount_SESSION  = floatval($_SESSION['amount']['parsian'][$Token]['amount']);
        }
        else
        {
            logs::set('pay:parsian:SESSION:amount:not:found', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find amount"));
            return self::turn_back();
        }

        if($Amount_SESSION != $Amount)
        {
            logs::set('pay:parsian:Amount_SESSION:amount:is:not:equals', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find amount"));
            return self::turn_back();
        }


        if($status === '0' && intval($Token) > 0)
        {
            \lib\utility\payment\payment\parsian::$user_id = self::$user_id;
            \lib\utility\payment\payment\parsian::$log_data = self::$log_data;

            $is_ok = \lib\utility\payment\payment\parsian::verify($parsian);

            $payment_response = \lib\utility\payment\payment\parsian::$payment_response;

            $log_meta['meta']['payment_response'] = (array) $payment_response;

            $payment_response = json_encode((array) $payment_response, JSON_UNESCAPED_UNICODE);

            if($is_ok)
            {
                $update =
                [
                    'amount_end'       => $Amount_SESSION / 10,
                    'condition'        => 'ok',
                    'verify'           => 1,
                    'payment_response' => $payment_response,
                ];

                \lib\db\transactions::update($update, $transaction_id);
                logs::set('pay:parsian:ok:request', self::$user_id, $log_meta);
                return self::turn_back($transaction_id);
            }
            else
            {
                $update =
                [
                    'amount_end'       => $Amount_SESSION / 10,
                    'condition'        => 'verify_error',
                    'payment_response' => $payment_response,
                ];
                \lib\db\transactions::update($update, $transaction_id);
                logs::set('pay:parsian:verify_error:request', self::$user_id, $log_meta);
                return self::turn_back($transaction_id);
            }
        }
        else
        {
            $update =
            [
                'amount_end'       => $Amount_SESSION / 10,
                'condition'        => 'error',
                'payment_response' => json_encode((array) $_args, JSON_UNESCAPED_UNICODE),
            ];
            \lib\db\transactions::update($update, $transaction_id);
            logs::set('pay:parsian:error:request', self::$user_id, $log_meta);
            return self::turn_back($transaction_id);
        }
    }
}
?>