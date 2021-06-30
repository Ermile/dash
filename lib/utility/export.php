<?php
namespace dash\utility;

class export
{
    private static $temp_file = null;


    /**
     * export data to csv file
     *
     * @param      <type>  $_args  The arguments
     * @param       $_arg['name']   [name of file]
     * @param       $_arg['type']   [type of file]
     * @param       $_arg['data']   [data to export]
     */
    public static function csv($_args)
    {

        $type     = isset($_args['type']) ? $_args['type'] : 'csv';
        $filename = isset($_args['name']) ? $_args['name'] : 'Untitled';
        $translate = false;
        if(isset($_args['T_']) && $_args['T_'])
        {
            $translate = true;
        }


        if(isset($_args['data']) && is_array($_args['data']))
        {
            $data = $_args['data'];
        }
        else
        {
            $data = [];
        }

        // disable caching
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Type: application/csv;charset=UTF-8");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}.{$type}");
        // header("Content-Transfer-Encoding: binary");

        if (count($data) == 0 || !$data || empty($data) || !is_array($data))
        {
            echo  null;
        }
        else
        {
            // BOM header UTF-8
            echo "\xEF\xBB\xBF";

            ob_start();
            $df = @fopen("php://output", 'w');
            if(is_array(reset($data)))
            {
                $keys = array_keys(reset($data));
                if($translate)
                {
                    $keys = array_map('T_', $keys);
                }

                fputcsv($df, $keys);
            }

            foreach ($data as $row)
            {
                if(!is_array($row))
                {
                    $row = [$row];
                }
                fputcsv($df, $row);
            }

            fclose($df);
            echo ob_get_clean();
        }

        \dash\code::bye();
    }


    public static function new_csv_file()
    {
        self::$temp_file = null;
    }


    public static function get_link()
    {
        $url = self::$temp_file;
        $url = str_replace(root.'public_html', \dash\url::base(), $url);
        return $url;
    }


    public static function csv_file($_args)
    {
        $type         = isset($_args['type']) ? $_args['type'] : 'csv';
        $filename     = isset($_args['name']) ? $_args['name'] : 'Untitled';

        $first_record = false;

        if(!self::$temp_file)
        {
            self::$temp_file = root. 'public_html/files';
            if(!\dash\file::exists(self::$temp_file))
            {
                \dash\file::makeDir(self::$temp_file);
            }

            self::$temp_file .= '/temp';
            if(!\dash\file::exists(self::$temp_file))
            {
                \dash\file::makeDir(self::$temp_file);
            }

            self::$temp_file.= '/'. $filename. '_'. date("Y_m_d_H_i_s"). '_'. rand(11111,99999). '.'. $type;

            // BOM header UTF-8
            \dash\file::append(self::$temp_file, "\xEF\xBB\xBF");

            $first_record = true;
        }


        if(isset($_args['data']) && is_array($_args['data']))
        {
            $data = $_args['data'];
        }
        else
        {
            $data = [];
        }


        if(count($data) == 0 || !$data || empty($data) || !is_array($data))
        {
            return self::get_link();
        }
        else
        {
            ob_start();

            $df = @fopen("php://output", 'w');

            if($first_record)
            {
                if(is_array(reset($data)))
                {
                    $keys = array_keys(reset($data));
                    fputcsv($df, $keys);
                }
            }

            foreach ($data as $row)
            {
                if(!is_array($row))
                {
                    $row = [$row];
                }
                fputcsv($df, $row);
            }

            fclose($df);

            \dash\file::append(self::$temp_file, ob_get_clean());
        }

        return self::get_link();
    }

}
?>