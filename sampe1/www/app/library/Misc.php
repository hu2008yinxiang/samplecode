<?php

class Misc
{

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
        } else {
            $tmp = @gzuncompress($zipped);
            if ($tmp !== false)
                return $tmp;
        }
        return $zipped;
    }

    public static function cacheToFile($data, $file, $method = 'json')
    {
        $content = array();
        switch ($method) {
            case 'json':
                $content = array(
                    '<?php ',
                    PHP_EOL,
                    'return ',
                    'json_decode(',
                    var_export(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), true),
                    ', true);',
                    PHP_EOL
                );
                break;
            case 'compress_json':
                $content = array(
                    '<?php ',
                    PHP_EOL,
                    'return ',
                    'json_decode(gzuncompress(',
                    var_export(gzcompress(json_encode($data, JSON_UNESCAPED_UNICODE)), true),
                    '), true);',
                    PHP_EOL
                );
                break;
            case 'array':
                $content = array(
                    '<?php ',
                    PHP_EOL,
                    'return ',
                    var_export($data, true),
                    ';',
                    PHP_EOL
                );
                break;
            
            case 'array_eval':
                $content = array(
                    '<?php ',
                    PHP_EOL,
                    'return ',
                    'eval(',
                    var_export('return ' . var_export($data, true) . ';', true),
                    ');',
                    PHP_EOL
                );
                break;
            case 'compress':
                $content = array(
                    '<?php ',
                    PHP_EOL,
                    'return unserialize(gzuncompress(',
                    var_export(gzcompress(serialize($data)), true),
                    '));',
                    PHP_EOL
                );
                break;
            case 'serialize':
            default:
                $content = array(
                    '<?php ',
                    PHP_EOL,
                    'return unserialize(',
                    var_export(serialize($data), true),
                    ');',
                    PHP_EOL
                );
                break;
        }
        file_put_contents($file, $content);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file);
        }
    }

    public static function runInBackground($command)
    {
        if (stripos(php_uname(), 'Windows')) {
            pclose(popen("start /B " . $command, "r"));
            return;
        }
        exec($command . ' < /dev/null > /dev/null &');
    }

    public static function json_encode($value, $options = 256)
    {
        $ret = json_encode($value, $options | 256);
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $ret = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches)
            {
                if (function_exists('mb_convert_encoding')) {
                    return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
                } else {
                    // Slower conversion from UTF-16 to UTF-8 (BMP Only)
                    // See: http://www.cl.cam.ac.uk/~mgk25/unicode.html
                    $decimal_code = hexdec($matches[1]);
                    $character = "";
                    if ((0x7F & $decimal_code) == $decimal_code) {
                        // UTF-8 1-byte aka ASCII
                        $first_byte = 0x7F & $decimal_code;
                        $character = chr($first_byte);
                    } elseif ((0x7FF & $decimal_code) == $decimal_code) {
                        // UTF-8 2-bytes
                        $first_byte = 0xC0 | (($decimal_code >> 6) & 0x1F);
                        $second_byte = 0x80 | ($decimal_code & 0x3F);
                        $character = chr($first_byte) . chr($second_byte);
                    } elseif ((0xFFFF & $decimal_code) == $decimal_code) {
                        // UTF-8 3-bytes
                        $first_byte = 0xE0 | (($decimal_code >> 12) & 0x0F);
                        $second_byte = 0x80 | (($decimal_code >> 6) & 0x3F);
                        $third_byte = 0x80 | ($decimal_code & 0x3F);
                        $character = chr($first_byte) . chr($second_byte) . chr($third_byte);
                    }
                    return $character;
                }
            }, $ret);
        }
        return $ret;
    }

    public static function randWithWeight(array $weights, $seed = null)
    {
        mt_srand($seed);
        $weight_total = array_sum($weights);
        $min = 1;
        $max = $weight_total;
        $value = mt_rand($min, $max);
        $index = - 1;
        while($value > 0){
            ++ $index;
            $value -= $weights[$index]; // 减去权重
        }
        return $index;
    }
}