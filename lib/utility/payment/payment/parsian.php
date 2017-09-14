<?php
namespace lib\utility\payment\payment;
use \lib\debug;

class parsian
{

	/**
     * auto save logs
     *
     * @var        boolean
     */
    public static $save_log = false;
    // to save log for this user
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
            if(self::$save_log)
            {
                \lib\db\logs::set('payment:parsian:soapclient:not:install', self::$user_id, $log_meta);
            }
            debug::error(T_("Can not connect to parsian gateway. Install it!"));
            return false;
        }

        try
        {
            $soap_meta =
            [
                'soap_version' => 'SOAP_1_1',
                'cache_wsdl'   => WSDL_CACHE_NONE ,
                'encoding'     => 'UTF-8',
            ];

            $client = new \SoapClient('https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL',$soap_meta);

            $result = $client->SalePaymentRequest(["requestData" => $_args]);

            self::$payment_response = $result;

            $status = $result->SalePaymentRequestResult->Status;
            $token  = $result->SalePaymentRequestResult->Token;
            $msg    = self::msg($status);

            if ($status === 0 && $token > 0)
            {
                \lib\db\logs::set('payment:parsian:redirect', self::$user_id, $log_meta);
                $url = "https://pec.shaparak.ir/NewIPG/?Token=" . $token;
                return $url;
            }
            else
            {
                \lib\db\logs::set('payment:parsian:error', self::$user_id, $log_meta);
                debug::error($msg);
                return false;
            }
        }
        catch (SoapFault $e)
        {
            \lib\db\logs::set('payment:parsian:error:load:web:services', self::$user_id, $log_meta);
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
            $soap_meta =
            [
                'soap_version' => 'SOAP_1_1',
                'cache_wsdl'   => WSDL_CACHE_NONE ,
                'encoding'     => 'UTF-8',
            ];

            // ClientConfirmResponseData ConfirmPaymentResult(ClientConfirmRequestData data)
            $client = new \SoapClient('https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL', $soap_meta);

            $result = $client->ConfirmPayment(["requestData" => $_args]);

            $Status = $result->ConfirmPaymentResult->Status;

            $RRN = isset($result->ConfirmPaymentResult->RRN) ? $result->ConfirmPaymentResult->RRN : null;

            $CardNumberMasked = isset($result->ConfirmPaymentResult->CardNumberMasked) ? $result->ConfirmPaymentResult->CardNumberMasked : null;

            $log_meta['meta']['client']           = $client;
            $log_meta['meta']['result']           = $result;
            $log_meta['meta']['Status']           = $Status;
            $log_meta['meta']['RRN']              = $RRN;
            $log_meta['meta']['CardNumberMasked'] = $CardNumberMasked;

            if($Status === 0)
            {
                \lib\db\logs::set('payment:parsian:transaction:ok', self::$user_id, $log_meta);
                return true;
            }
            else
            {
                \lib\db\logs::set('payment:parsian:error:verify', self::$user_id, $log_meta);
                debug::error(self::msg($Status));
                return false;
            }
        }
        catch(Exception $e)
        {
            \lib\db\logs::set('payment:parsian:error:load:web:services:verify', self::$user_id, $log_meta);
            debug::error(T_("Error in load web services"));
            return false;
        }
    }


    /**
     * reverse transactions
     *
     * @param      <type>  $_args  The arguments
     */
    public static function reverse($_args)
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
            $soap_meta =
            [
                'soap_version' => 'SOAP_1_1',
                'cache_wsdl'   => WSDL_CACHE_NONE ,
                'encoding'     => 'UTF-8',
            ];

            $client = new \SoapClient('https://pec.shaparak.ir/NewIPGServices/Reverse/ReversalService.asmx?WSDL', $soap_meta);

            $result = $client->ReversalRequest(["requestData" => $_args]);

            // ClientReversalResponseData ReversalRequest(ClientReversalRequestData data)
            $Status = $result->ReversalRequestResult->Status;

            if($Status === 0)
            {
                \lib\db\logs::set('payment:parsian:transaction:ok', self::$user_id, $log_meta);
                return true;
            }
            else
            {
                \lib\db\logs::set('payment:parsian:error:verify', self::$user_id, $log_meta);
                debug::error(self::msg($Status));
                return false;
            }
        }
        catch(Exception $e)
        {
            \lib\db\logs::set('payment:parsian:error:load:web:services:verify', self::$user_id, $log_meta);
            debug::error(T_("Error in load web services"));
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
        $msg = null;
        $T_msg =
        [
            '-32768' => ['en' => 'UnkownError', 'fa' => 'خطاي ناشناخته رخ داده است',],
            '-1552'  => ['en' => 'PaymentRequestIsNotEligibleToReversal', 'fa' => 'برگشت تراکنش مجاز نمی باشد',],
            '-1551'  => ['en' => 'PaymentRequestIsAlreadyReversed', 'fa' => 'برگشت تراکنش قب ًلا انجام شده است',],
            '-1550'  => ['en' => 'PaymentRequestStatusIsNotReversalable', 'fa' => 'برگشت تراکنش در وضعیت جاري امکان پذیر نمی باشد',],
            '-1549'  => ['en' => 'MaxAllowedTimeToReversalHasExceeded', 'fa' => 'زمان مجاز براي درخواست برگشت تراکنش به اتمام رسیده است',],
            '-1548'  => ['en' => 'BillPaymentRequestServiceFailed', 'fa' => 'فراخوانی سرویس درخواست پرداخت قبض ناموفق بود',],
            '-1540'  => ['en' => 'InvalidConfirmRequestService', 'fa' => 'تایید تراکنش ناموفق می باشد',],
            '-1536'  => ['en' => 'TopupChargeServiceTopupChargeRequestFailed', 'fa' => 'فراخوانی سرویس درخواست شارژ تاپ آپ ناموفق بود',],
            '-1533'  => ['en' => 'PaymentIsAlreadyConfirmed', 'fa' => 'تراکنش قبلاً تایید شده است',],
            '-1532'  => ['en' => 'MerchantHasConfirmedPaymentRequest', 'fa' => 'تراکنش از سوي پذیرنده تایید شد',],
            '-1531'  => ['en' => 'CannotConfirmNonSuccessfulPayment', 'fa' => 'تایید تراکنش ناموفق امکان پذیر نمی باشد',],
            '-1530'  => ['en' => 'MerchantConfirmPaymentRequestAccessVaiolated', 'fa' => 'پذیرنده مجاز به تایید این تراکنش نمی باشد',],
            '-1528'  => ['en' => 'ConfirmPaymentRequestInfoNotFound', 'fa' => 'اطلاعات پرداخت یافت نشد',],
            '-1527'  => ['en' => 'CallSalePaymentRequestServiceFailed', 'fa' => 'انجام عملیات درخواست پرداخت تراکنش خرید ناموفق بود',],
            '-1507'  => ['en' => 'ReversalCompleted', 'fa' => 'تراکنش برگشت به سوئیچ ارسال شد',],
            '-1505'  => ['en' => 'PaymentConfirmRequested', 'fa' => 'تایید تراکنش توسط پذیرنده انجام شد',],
            '-132'   => ['en' => 'InvalidMinimumPaymentAmount', 'fa' => 'مبلغ تراکنش کمتر از حداقل مجاز می باشد',],
            '-131'   => ['en' => 'InvalidToken', 'fa' => 'توکن نامعتبر می باشد',],
            '-130'   => ['en' => 'TokenIsExpired', 'fa' => 'زمان توکن منقضی شده است',],
            '-128'   => ['en' => 'InvalidIpAddressFormat', 'fa' => 'قالب آدرس IP معتبر نمی باشد',],
            '-127'   => ['en' => 'InvalidMerchantIp', 'fa' => 'آدرس اینترنتی معتبر نمی باشد',],
            '-126'   => ['en' => 'InvalidMerchantPin', 'fa' => 'کد شناسایی پذیرنده معتبر نمی باشد',],
            '-121'   => ['en' => 'InvalidStringIsNumeric', 'fa' => 'رشته داده شده بطور کامل عددي نمی باشد',],
            '-120'   => ['en' => 'InvalidLength', 'fa' => 'طول داده ورودي معتبر نمی باشد',],
            '-119'   => ['en' => 'InvalidOrganizationId', 'fa' => 'سازمان نامعتبر می باشد',],
            '-118'   => ['en' => 'ValueIsNotNumeric', 'fa' => 'مقدار ارسال شده عدد نمی باشد',],
            '-117'   => ['en' => 'LenghtIsLessOfMinimum', 'fa' => 'طول رشته کم تر از حد مجاز می باشد',],
            '-116'   => ['en' => 'LenghtIsMoreOfMaximum', 'fa' => 'طول رشته بیش از حد مجاز می باشد',],
            '-115'   => ['en' => 'InvalidPayId', 'fa' => 'شناسه پرداخت نامعتبر می باشد',],
            '-114'   => ['en' => 'InvalidBillId', 'fa' => 'شناسه قبض نامعتبر می باشد',],
            '-113'   => ['en' => 'ValueIsNull', 'fa' => 'پارامتر ورودي خالی می باشد',],
            '-112'   => ['en' => 'OrderIdDuplicated', 'fa' => 'شماره سفارش تکراري است',],
            '-111'   => ['en' => 'InvalidMerchantMaxTransAmount', 'fa' => 'مبلغ تراکنش بیش از حد مجاز پذیرنده می باشد',],
            '-108'   => ['en' => 'ReverseIsNotEnabled', 'fa' => 'قابلیت برگشت تراکنش براي پذیرنده غیر فعال می باشد',],
            '-107'   => ['en' => 'AdviceIsNotEnabled', 'fa' => 'قابلیت ارسال تاییده تراکنش براي پذیرنده غیر فعال می باشد',],
            '-106'   => ['en' => 'ChargeIsNotEnabled', 'fa' => 'قابلیت شارژ براي پذیرنده غیر فعال می باشد',],
            '-105'   => ['en' => 'TopupIsNotEnabled', 'fa' => 'قابلیت تاپ آپ براي پذیرنده غیر فعال می باشد',],
            '-104'   => ['en' => 'BillIsNotEnabled', 'fa' => 'قابلیت پرداخت قبض براي پذیرنده غیر فعال می باشد',],
            '-103'   => ['en' => 'SaleIsNotEnabled', 'fa' => 'قابلیت خرید براي پذیرنده غیر فعال می باشد',],
            '-102'   => ['en' => 'ReverseSuccessful', 'fa' => 'تراکنش با موفقیت برگشت داده شد',],
            '-101'   => ['en' => 'MerchantAuthenticationFailed', 'fa' => 'پذیرنده اهراز هویت نشد',],
            '-100'   => ['en' => 'MerchantIsNotActive', 'fa' => 'پذیرنده غیرفعال می باشد',],
            '-1'     => ['en' => 'Server Error', 'fa' => 'سرور خطاي',],
            '0'      => ['en' => 'Successful', 'fa' => 'عملیات موفق می باشد',],
            '1'      => ['en' => 'Refer To Card Issuer Decline', 'fa' => 'صادرکننده ي کارت  از انجام تراکنش صرف نظر کرد',],
            '2'      => ['en' => 'Refer To Card Issuer Special Conditions', 'fa' => 'باموفقیت قبلا تراکنش این تاییدیه عملیات صورت پذیرفته است',],
            '3'      => ['en' => 'Invalid Merchant', 'fa' => 'پذیرنده ي فروشگاهی نامعتبر می باشد',],
            '5'      => ['en' => 'Do Not Honour', 'fa' => 'از انجام تراکنش صرف نظر شد',],
            '6'      => ['en' => 'Error', 'fa' => 'بروز خطایی ناشناخته',],
            '8'      => ['en' => 'Honour With Identification', 'fa' => 'باتشخیص هویت دارنده ي کارت، تراکنش موفق می باشد',],
            '9'      => ['en' => 'Request Inprogress', 'fa' => 'درخواست رسیده در حال پی گیري و انجام است',],
            '10'     => ['en' => 'Approved For Partial Amount', 'fa' => 'تراکنش با مبلغی پایین تر از مبلغ درخواستی )کمبود حساب مشتري ( پذیرفته شده است',],
            '12'     => ['en' => 'Invalid Transaction', 'fa' => 'تراکنش نامعتبر است',],
            '13'     => ['en' => 'Invalid Amount', 'fa' => 'مبلغ تراکنش نادرست است',],
            '14'     => ['en' => 'Invalid Card Number', 'fa' => 'شماره کارت ارسالی نامعتبر است (وجود ندارد)',],
            '15'     => ['en' => 'No Such Issuer', 'fa' => 'صادرکننده ي کارت نامعتبراست (وجود ندارد)',],
            '17'     => ['en' => 'Customer Cancellation', 'fa' => 'مشتري درخواست کننده حذف شده است',],
            '20'     => ['en' => 'Invalid Response', 'fa' => 'خطای پاسخ',],
            '21'     => ['en' => 'No Action Taken', 'fa' => 'در صورتی که پاسخ به در خواست ترمینال نیازمند هیچ پاسخ خاص یا عملکردي نباشیم این پیام را خواهیم داشت',],
            '22'     => ['en' => 'Suspected Malfunction', 'fa' => 'تراکنش مشکوك به بد عمل کردن ( کارت ، ترمینال ، دارنده کارت ) بوده است ',],
            '30'     => ['en' => 'Format Error', 'fa' => 'قالب پیام داراي اشکال است',],
            '31'     => ['en' => 'Bank Not Supported By Switch', 'fa' => 'پذیرنده توسط سوئی پشتیبانی نمی شود',],
            '32'     => ['en' => 'Completed Partially',  'fa' => 'تراکنش به صورت غیر قطعی کامل شده است',],
            '33'     => ['en' => 'Expired Card Pick Up', 'fa' => 'تاریخ انقضاي کارت سپري شده است',],
            '38'     => ['en' => 'Allowable PIN Tries Exceeded Pic Up', 'fa' => 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است. کارت توسط دستگاه ضبط شود',],
            '39'     => ['en' => 'No Credit Acount', 'fa' => 'کارت حساب اعتباري ندارد',],
            '40'     => ['en' => 'Requested Function is not supported', 'fa' => 'عملیات درخواستی پشتیبانی نمی گردد',],
            '41'     => ['en' => 'Card Lost', 'fa' => 'کارت مفقودي می باشد',],
            '43'     => ['en' => 'Stolen Card', 'fa' => 'کارت مسروقه می باشد',],
            '45'     => ['en' => 'Bill Can not Be Payed', 'fa' => 'قبض قابل پرداخت نمی باشد',],
            '51'     => ['en' => 'No Sufficient Funds', 'fa' => 'موجودي کافی نمی باشد',],
            '54'     => ['en' => 'Expired Account', 'fa' => 'تاریخ انقضاي کارت سپري شده است',],
            '55'     => ['en' => 'Incorrect PIN', 'fa' => 'رمز کارت نا معتبر است',],
            '56'     => ['en' => 'No Card Record', 'fa' => 'انجام تراکنش توسط دارنده کارت مجاز نمی باشد',],
            '57'     => ['en' => 'Transaction Not Permitted To CardHolder', 'fa' => 'کنش مربوطه توسط کارت مجاز نمی باشد',],
            '58'     => ['en' => 'Transaction Not Permittend To Terminal', 'fa' => 'انجام تراکنش مربوطه توسط پایانه ي انجام دهنده مجاز نمی باشد',],
            '59'     => ['en' => 'Suspected Fraud-Decline', 'fa' => 'کارت مظنون به تقلب است',],
            '61'     => ['en' => 'Exceeds Withdrawal Amount Limit', 'fa' => 'مبلغ تراکنش بیش از حد مجاز می باشد',],
            '62'     => ['en' => 'Restricted Card-Decline', 'fa' => 'کارت محدود شده است',],
            '63'     => ['en' => 'Security Violation', 'fa' => 'تمهیدات امنیتی نقض گردیده است',],
            '65'     => ['en' => 'Exceeds Withdrawal Frequency Limit ', 'fa' => 'تعداد درخواست تراکنش بیش از حد مجاز می باشد',],
            '68'     => ['en' => 'Response Received Too Late', 'fa' => 'پاسخ لازم براي تکمیل یا انجام تراکنش خیلی دیر رسیده است',],
            '69'     => ['en' => 'Allowabe Number Of PIN Tries Exceeded', 'fa' => 'تعداد دفعات تکرار رمز از حد مجاز گذشته است',],
            '75'     => ['en' => 'PIN Reties Exceeds-Slm', 'fa' => 'تعداد دفعات ورود رمزغلط بیش از حدمجاز است',],
            '78'     => ['en' => 'Deactivated Card-Slm', 'fa' => 'کارت فعال نیست',],
            '79'     => ['en' => 'Invalid Amount-Slm', 'fa' => 'حساب متصل به کارت نا معتبر است یا داراي اشکال است',],
            '80'     => ['en' => 'Transaction Denied-Slm', 'fa' => 'درخواست تراکنش رد شده است',],
            '81'     => ['en' => 'Cancelled Card-Slm', 'fa' => 'کارت پذیرفته نشد',],
            '83'     => ['en' => 'Host Refuse-Slm', 'fa' => 'سرویس دهنده سوئیچ کارت را نپذیرفته است',],
            '84'     => ['en' => 'Issuer Down-Slm', 'fa' => 'در تراکنشهایی که انجام آن مستلزم ارتباط با صادر کننده است در صورت فعال نبودن صادر کننده این پیام در پاسخ ارسال خواهد شد',],
            '91'     => ['en' => 'Issuer Or Switch Is  Inoperative', 'fa' => 'سیستم صدور مجوز انجام تراکنش موقتا غیر فعال است و یا زمان تعیین شده براي صدو مجوز به پایان رسیده است',],
            '92'     => ['en' => 'Financial Inst Or Intermediate Net Facility Not Found for Routing', 'fa' => 'مقصد تراکنش پیدا نشد',],
            '93'     => ['en' => 'Tranaction Cannot Be Completed', 'fa' => 'امکان تکمیل تراکنش وجود ندارد',],
        ];

        if(isset($T_msg[$_status]))
        {
            if(\lib\define::get_language() === 'fa')
            {
                if(isset($T_msg[$_status]['fa']))
                {
                    return $T_msg[$_status]['fa'];
                }
            }

            if(isset($T_msg[$_status]['en']))
            {
                return $T_msg[$_status]['en'];
            }

            return T_("Unkown payment error");

        }
        else
        {
            return T_("Unkown payment error");
        }
        return $msg;
    }

}
?>