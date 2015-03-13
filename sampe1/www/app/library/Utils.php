<?php

class Utils
{

    /**
     * 解压
     *
     * @param string $zipped            
     * @return string
     */
    public static function gunzip($zipped)
    {
        $offset = 0;
        if (substr($zipped, 0, 2) == "\x1f\x8b") {
            $offset = 2;
            if (substr($zipped, $offset, 1) == "\x08") {
                return gzinflate(substr($zipped, $offset + 8));
            } else {
                return gzuncompress($zipped);
            }
        }
        return $zipped;
    }

    /**
     * 获取原始POST内容
     *
     * @return string
     */
    public static function get_post_content()
    {
        $content = file_get_contents('php://input');
        if (isset($_SERVER['HTTP_CONTENT_ENCODING']) && $_SERVER['HTTP_CONTENT_ENCODING'] == 'gzip') {
            $content = self::gunzip($content);
        }
        return $content;
    }

    public static function get_absolute_path($path)
    {
        $path = str_replace(array(
            '/',
            '\\',
            '//',
            '\\\\'
        ), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part)
                continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return '/' . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function array_merge_r(array $arr, array $_)
    {
        $argc = func_num_args();
        $argv = func_get_args();
        while ($argc > 2) {
            $arr1 = array_pop($argv);
            $arr2 = array_pop($argv);
            $arr3 = self::array_merge_r($arr2, $arr1);
            array_push($argv, $arr3);
            $argc -= 1;
        }
        $base_arr = $argv[0];
        $delta_arr = $argv[1];
        $ret = array();
        foreach ($base_arr as $k => $v) {
            $ret[$k] = $v;
        }
        foreach ($delta_arr as $k => $v) {
            if (is_array($v)) {
                if (isset($ret[$k]) && is_array($ret[$k])) {
                    $ret[$k] = static::array_merge_r($ret[$k], $v);
                } else {
                    $ret[$k] = $v;
                }
            } else {
                $ret[$k] = $v;
            }
        }
        return $ret;
    }

    public static function array_merge_s(array $arr, array $_)
    {
        $argc = func_num_args();
        $argv = func_get_args();
        while ($argc > 2) {
            $arr1 = array_pop($argv);
            $arr2 = array_pop($argv);
            $arr3 = self::array_merge_s($arr2, $arr1);
            array_push($argv, $arr3);
            $argc -= 1;
        }
        $base_arr = $argv[0];
        $delta_arr = $argv[1];
        $ret = array();
        foreach ($base_arr as $k => $v) {
            if (is_null($v))
                continue;
            $ret[$k] = $v;
        }
        foreach ($delta_arr as $k => $v) {
            if (is_null($v))
                continue;
            if (is_array($v)) {
                if (is_array($ret[$k])) {
                    $ret[$k] = static::array_merge_s($ret[$k], $v);
                } else {
                    $ret[$k] = $v;
                }
            } else {
                $ret[$k] = $v;
            }
        }
        return $ret;
    }

    /**
     *
     * @param string|boolean $var            
     * @return boolean
     */
    public static function bool($var)
    {
        return is_bool($var) ? $var : (is_string($var) && strtoupper($var) == 'TRUE');
    }
}