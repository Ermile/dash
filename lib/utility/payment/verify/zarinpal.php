<?php
namespace lib\utility\payment\verify;
use \lib\debug;
use \lib\option;
use \lib\utility;
use \lib\db\logs;

trait zarinpal
{
    /**
     * check payment of zarinpal
     *
     * @param      <type>   $_args  The arguments
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    public static function zarinpal($_args)
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

        if(!isset($_args['get']['Authority']) || !isset($_args['get']['Status']))
        {
            return self::turn_back();
        }

        if(!option::config('zarinpal', 'status'))
        {
            logs::set('pay:zarinpal:status:false', self::$user_id, $log_meta);
            debug::error(T_("The zarinpal payment on this service is locked"));
            return self::turn_back();
        }

        if(!option::config('zarinpal', 'MerchantID'))
        {
            logs::set('pay:zarinpal:MerchantID:not:set', self::$user_id, $log_meta);
            debug::error(T_("The zarinpal payment MerchantID not set"));
            return self::turn_back();
        }

        $zarinpal = [];
        $zarinpal['MerchantID'] = option::config('zarinpal', 'MerchantID');

        $zarinpal['Authority']  = $_args['get']['Authority'];


        if(isset($_SESSION['amount']['zarinpal'][$zarinpal['Authority']]['transaction_id']))
        {
            $transaction_id  = $_SESSION['amount']['zarinpal'][$zarinpal['Authority']]['transaction_id'];
        }
        else
        {
            logs::set('pay:zarinpal:SESSION:transaction_id:not:found', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find your transaction"));
            return self::turn_back();
        }

        $log_meta['data'] = self::$log_data = $transaction_id;

        $update =
        [
            'condition'        => 'pending',
            'payment_response' => json_encode((array) $_args, JSON_UNESCAPED_UNICODE),
        ];
        \lib\db\transactions::update($update, $transaction_id);

        logs::set('pay:zarinpal:pending:request', self::$user_id, $log_meta);

        if(isset($_SESSION['amount']['zarinpal'][$zarinpal['Authority']]['amount']))
        {
            $zarinpal['Amount']  = $_SESSION['amount']['zarinpal'][$zarinpal['Authority']]['amount'];
        }
        else
        {
            logs::set('pay:zarinpal:SESSION:amount:not:found', self::$user_id, $log_meta);
            debug::error(T_("Your session is lost! We can not find amount"));
            return self::turn_back();
        }

        if($_args['get']['Status'] == 'NOK')
        {
            $update =
            [
                'amount_end'       => $zarinpal['Amount'],
                'condition'        => 'cancel',
                'payment_response' => json_encode((array) $_args, JSON_UNESCAPED_UNICODE),
            ];
            \lib\db\transactions::update($update, $transaction_id);
            logs::set('pay:zarinpal:cancel:request', self::$user_id, $log_meta);
            return self::turn_back($transaction_id);
        }
        else
        {
            \lib\utility\payment\payment\zarinpal::$user_id = self::$user_id;
            \lib\utility\payment\payment\zarinpal::$log_data = self::$log_data;

            $is_ok = \lib\utility\payment\payment\zarinpal::verify($zarinpal);

            $payment_response = \lib\utility\payment\payment\zarinpal::$payment_response;

            $log_meta['meta']['payment_response'] = (array) $payment_response;

            $payment_response = json_encode((array) $payment_response, JSON_UNESCAPED_UNICODE);

            if($is_ok)
            {
                $update =
                [
                    'amount_end'       => $zarinpal['Amount'],
                    'condition'        => 'ok',
                    'verify'           => 1,
                    'payment_response' => $payment_response,
                ];

                \lib\db\transactions::update($update, $transaction_id);
                logs::set('pay:zarinpal:ok:request', self::$user_id, $log_meta);
                return self::turn_back($transaction_id);
            }
            else
            {
                $update =
                [
                    'amount_end'       => $zarinpal['Amount'],
                    'condition'        => 'verify_error',
                    'payment_response' => $payment_response,
                ];
                \lib\db\transactions::update($update, $transaction_id);
                logs::set('pay:zarinpal:verify_error:request', self::$user_id, $log_meta);
                return self::turn_back($transaction_id);
            }
        }
    }
}
?>