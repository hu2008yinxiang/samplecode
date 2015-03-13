<?php

class Misc
{

    const DT_PC = 0;

    const DT_TABLET = 1;

    const DT_PHONE = 2;

    /**
     * GZIP解压
     *
     * @param string $zipped            
     * @return string
     */
    public static function gunzip($zipped)
    {
        $offset = 0;
        if (substr($zipped, 0, 2) == "\x1f\x8b")
            $offset = 2;
        if (substr($zipped, $offset, 1) == "\x08") {
            return gzinflate(substr($zipped, $offset + 8));
        } elseif ($offset) {
            return gzuncompress($zipped);
        }
        return $zipped;
    }

    public static function deviceType($userAgent)
    {}

    public static function osType($userAgent)
    {
        return '';
    }
}