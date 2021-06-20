<?php
namespace dash\utility\pay\api\payir;


class bank
{

    public static $payment_response = [];


    public static function pay($_args = [])
    {

        $default_args =
        [
            'api'             => array_key_exists('api', $_args) ? $_args['api'] : null,
            'amount'          => array_key_exists('amount', $_args) ? $_args['amount'] : null,
            'redirect'        => array_key_exists('redirect', $_args) ? $_args['redirect'] : null,
            'factorNumber'    => array_key_exists('factorNumber', $_args) ? $_args['factorNumber'] : null,
            'mobile'          => array_key_exists('mobile', $_args) ? $_args['mobile'] : null,
            'description'     => array_key_exists('description', $_args) ? $_args['description'] : null,
            'validCardNumber' => array_key_exists('validCardNumber', $_args) ? $_args['validCardNumber'] : null,
        ];

        $default_args = http_build_query($default_args);

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, 'https://pay.ir/pg/send');
        curl_setopt($handle, CURLOPT_POSTFIELDS, $default_args);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($handle);
        curl_close($handle);
        $result = json_decode($result, true);

        self::$payment_response = $result;

        if(isset($result['status']) && intval($result['status']) === 1 && isset($result['token']))
        {
            return $result['token'];
        }

        if(array_key_exists('status', $result))
        {
            \dash\notif::error(self::msg($result['status'], 'send'));
        }

        return false;

    }


        /**
     * { function_description }
     *
     * @param      array  $_args  The arguments
     */
    public static function verify($_args = [])
    {
        $default_args =
        [
            'api'   => array_key_exists('api', $_args) ? $_args['api'] : null,
            'token' => array_key_exists('token', $_args) ? $_args['token'] : null,
        ];

        $default_args = http_build_query($default_args);

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, 'https://pay.ir/pg/verify');
        curl_setopt($handle, CURLOPT_POSTFIELDS, $default_args);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($handle);
        curl_close($handle);

        $result = json_decode($result, true);

        self::$payment_response = $result;

        if(isset($result['status']) && intval($result['status']) === 1)
        {
            return $result;
        }

        if(array_key_exists('status', $result))
        {
            \dash\notif::error(self::msg($result['status'], 'verify'));
        }

        return false;
    }


    /**
     * set msg
     *
     * @param      <type>  $_status  The status
     */
    public static function msg($_status, $_type = 'send')
    {
        $msg = null;
        if($_type === 'send')
        {
            switch ($_status)
            {
                case '-1':
                case -1:
                    $msg = "ارسال api الزامی می باشد";
                    break;

                case '-2':
                case -2:
                    $msg = "ارسال مبلغ تراکنش الزامی می باشد";
                    break;

                case '-3':
                case -3:
                    $msg = "مبلغ تراکنش باید به صورت عددی باشد";
                    break;

                case '-4':
                case -4:
                    $msg = "مبلغ تراکنش نباید کمتر از 1000 باشد";
                    break;

                case '-5':
                case -5:
                    $msg = "ارسال redirect الزامی می باشد";
                    break;

                case '-6':
                case -6:
                    $msg = "درگاه پرداختی با api ارسالی یافت نشد و یا غیر فعال می باشد";
                    break;

                case '-7':
                case -7:
                    $msg = "فروشنده غیر فعال می باشد";
                    break;

                case '-8':
                case -8:
                    $msg = "آدرس بازگشتی با آدرس درگاه پرداخت ثبت شده همخوانی ندارد";
                    break;

                case '0':
                case 0:
                    $msg = "در حال حاضر درگاه پرداخت قطع می باشد. به زودی مشکل برطرف خواهد شد";
                    break;

                default:
                case 'failed':
                    $msg = "تراکنش با خطا مواجه شد";
                    break;
            }
        }
        elseif($_type === 'verify')
        {

            switch ($_status)
            {
                case '-1':
                case -1:
                    $msg = "ارسال api الزامی می باشد";
                    break;

                case '-2':
                case -2:
                    $msg = "ارسال transId الزامی می باشد";
                    break;

                case '-3':
                case -3:
                    $msg = "درگاه پرداختی با api ارسالی یافت نشد و یا غیر فعال می باشد";
                    break;

                case '-4':
                case -4:
                    $msg = "فروشنده غیر فعال می باشد";
                    break;

                case '-5':
                case -5:
                default:
                    $msg = "تراکنش با خطا مواجه شده است";
                    break;
            }
        }

        return $msg;
    }
}
?>