<?php
namespace dash\utility\pay\api\mellat;


class bank
{

    public static $payment_response = [];


    public static function pay($_args = [])
    {

        // if soap is not exist return false
        if(!class_exists("soapclient"))
        {
            \dash\log::set('payment:mellat:soapclient:not:install');
            \dash\notif::error(T_("Can not connect to mellat gateway. Install it!"));
            return false;
        }

        try
        {
            $soap_meta =
            [
                'soap_version' => 'SOAP_1_1',
                'exceptions'   => true,
            ];

            if(\dash\option::config('mellat', 'FORCE_API_URL_BANK_PAYMENT'))
            {
                $api_url = \dash\option::config('mellat', 'FORCE_API_URL_BANK_PAYMENT');
            }
            else
            {
                $api_url = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
            }

            $client = @new \SoapClient($api_url, $soap_meta);

            \dash\temp::set('mellatRawResponse', $client);

            $result = $client->__soapCall('bpPayRequest', array($_args));

            \dash\temp::set('mellatRawResponse1', $result);

            self::$payment_response = $result;

            $return = $result->return;

            $res    = explode(',', $return);

            $ResCode = $res[0];

            if ($ResCode == "0")
            {
               return $res[1];
            }
            else
            {
                \dash\notif::error(self::msg($ResCode), $ResCode);
                return false;
            }

        }
        catch (\Exception $e)
        {
            \dash\temp::set('mellatRawResponse2', $e);
            \dash\log::set('payment:mellat:error:load:web:services');
            \dash\notif::error(T_("Error in load web services"));
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

        try
        {

            $soap_meta =
            [
                'soap_version' => 'SOAP_1_1',
                'exceptions'   => true,
            ];

            if(\dash\option::config('mellat', 'FORCE_API_URL_BANK_PAYMENT'))
            {
                $api_url = \dash\option::config('mellat', 'FORCE_API_URL_BANK_PAYMENT');
            }
            else
            {
                $api_url = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
            }

            $client    = @new \SoapClient($api_url, $soap_meta);

            $result = $client->bpVerifyRequest($_args);

            self::$payment_response = $result;

            $return = $result->return;

            $res = explode(',', $return);

            $ResCode = $res[0];

            if ($ResCode == "0")
            {
               return true;
            }
            else
            {
                \dash\notif::error(self::msg($ResCode), $ResCode);
                return false;
            }
        }
        catch(\Exception $e)
        {
            \dash\log::set('payment:mellat:error:load:web:services:verify');
            \dash\notif::error(T_("Error in load web services"));
            return false;
        }
    }


    /**
     * set msg
     *
     * @param      <type>  $_status  The status
     */
    public static function msg($_status)
    {

        $msg      = [];
        $msg[0]   = T_("Transaction Approved");
        $msg[11]  = T_("Invalid Card Number");
        $msg[12]  = T_("No Sufficient Funds");
        $msg[13]  = T_("Incorrect Pin");
        $msg[14]  = T_("Allowable Number Of Pin Tries Exceeded");
        $msg[15]  = T_("Card Not Effective");
        $msg[16]  = T_("Exceeds Withdrawal Frequency Limit");
        $msg[17]  = T_("Customer Cancellation");
        $msg[18]  = T_("Expired Card");
        $msg[19]  = T_("Exceeds Withdrawal Amount Limit");
        $msg[111] = T_("No Such Issuer");
        $msg[112] = T_("Card Switch Internal Error");
        $msg[113] = T_("Issuer Or Switch Is Inoperative");
        $msg[114] = T_("Transaction Not Permitted To Card Holder");
        $msg[21]  = T_("Invalid Merchant");
        $msg[23]  = T_("Security Violation");
        $msg[24]  = T_("Invalid User Or Password");
        $msg[25]  = T_("Invalid Amount");
        $msg[31]  = T_("Invalid Response");
        $msg[32]  = T_("Format Error");
        $msg[33]  = T_("No Investment Account");
        $msg[34]  = T_("System Internal Error");
        $msg[35]  = T_("Invalid Business Date");
        $msg[41]  = T_("Duplicate Order Id");
        $msg[42]  = T_("Sale Transaction Not Found");
        $msg[43]  = T_("Duplicate Verify");
        $msg[44]  = T_("Verify Transaction Not Found");
        $msg[45]  = T_("Transaction Has Been Settled");
        $msg[46]  = T_("Transaction Has Not Been Settled");
        $msg[47]  = T_("Settle Transaction Not Found");
        $msg[48]  = T_("Transaction Has Been Reversed");
        $msg[49]  = T_("Refund Transaction Not Found");
        $msg[412] = T_("Bill Digit Incorrect");
        $msg[413] = T_("Payment Digit Incorrect");
        $msg[414] = T_("Bill Organization Not Valid");
        $msg[415] = T_("Session Timeout");
        $msg[416] = T_("Data Access Exception");
        $msg[417] = T_("Payer Id Is Invalid");
        $msg[418] = T_("Customer Not Found");
        $msg[419] = T_("Try Count Exceeded");
        $msg[421] = T_("Invalid IP");
        $msg[51]  = T_("Duplicate Transmission");
        $msg[54]  = T_("Original Transaction Not Found");
        $msg[55]  = T_("Invalid Transaction");
        $msg[61]  = T_("Error In Settle");


        if(isset($msg[$_status]))
        {
            return $msg[$_status];
        }
        else
        {
            return T_("Unkown payment error");
        }
        return $msg;
    }
}
?>