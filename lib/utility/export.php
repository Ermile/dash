<?php
namespace lib\utility;

class export
{

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
        $data     = isset($_args['data']) ? $_args['data'] : [];

        // disable caching
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}.{$type}");
        header("Content-Transfer-Encoding: binary");

        if (count($data) == 0 || !$data || empty($data))
        {
            echo  null;
        }

        ob_start();

        $df = fopen("php://output", 'w');

        fputcsv($df, array_keys(reset($data)));

        foreach ($data as $row)
        {
            fputcsv($df, $row);
        }

        fclose($df);
        echo ob_get_clean();

        die();
    }
}
?>