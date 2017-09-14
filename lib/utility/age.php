<?php
namespace lib\utility;

class age
{

	/**
	 * get birthday and return age
	 *
	 * @param      <type>  $_brithday  The brithday
	 */
    public static function get_age($_brithday)
    {
        if($_brithday == null)
        {
            return null;
        }

        $brith_year  = date("Y", strtotime($_brithday));
        $brith_month = date("m", strtotime($_brithday));
        $brith_day   = date("d", strtotime($_brithday));
        // to convert the jalali date to gregorian date
        if(intval($brith_year) > 1300 && intval($brith_year) < 1400)
        {
            list($brith_year, $brith_month, $brith_day) = \lib\utility\jdate::toGregorian($brith_year, $brith_month, $brith_day);
            if($brith_month < 10)
            {
                $brith_month = "0". $brith_month;
            }
            if($brith_day < 10)
            {
                $brith_day = "0". $brith_day;
            }
        }
        // get date diff
        $date1 = new \DateTime($brith_year. $brith_month. $brith_day);
        $date2 = new \DateTime("now");
        $age   = $date1->diff($date2);
        $age   = $age->y;
		return $age;
    }


    /**
     * Gets the range.
     *
     * @param      integer  $_age   The age
     *
     * @return     string   The range.
     */
    public static function get_range($_age)
    {
        $range = null;
        $_age = intval($_age);

        switch ($_age) {
            case $_age <= 13 :
                $range = '-13';
                break;

            case $_age >= 14 && $_age <= 17 :
                $range = '14-17';
                break;

            case $_age >= 18 && $_age <= 24 :
                $range = '18-24';
                break;

            case $_age >= 25 && $_age <= 30 :
                $range = '25-30';
                break;

            case $_age >= 31 && $_age < 44 :
                $range = '31-44';
                break;

            case $_age >= 45 && $_age <= 59 :
                $range = '45-59';
                break;

            case $_age >= 60:
                $range = '60+';
                break;
        }
        return $range;
    }


    /**
     * Gets the range title.
     *
     * @param      integer  $_age   The age
     *
     * @return     string   The range title.
     */
    public static function get_range_title($_age)
    {
        $range_title = null;
        $_age = intval($_age);

        switch ($_age) {
            case $_age <= 10 :
                $range_title = 'baby';
                break;

            case $_age >= 11 && $_age <= 17 :
                $range_title = 'teenager';
                break;

            case $_age >= 18 && $_age <= 34 :
                $range_title = 'young';
                break;

            case $_age >= 35:
                $range_title = 'adult';
                break;

        }
        return $range_title;
    }
}
?>