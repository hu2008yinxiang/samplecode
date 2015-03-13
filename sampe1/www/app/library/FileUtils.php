<?php

class FileUtils
{

    private static $_cache;

    private static $access = 0;

    private static $hit = 0;

    public static function init()
    {
        if (! defined('APP_PATH')) {
            throw new Exception('APP_PATH not defined!');
        }
        self::$_cache = array();
    }

    public static function read_str($filename)
    {
        ++ self::$access;
        if (! isset(self::$_cache[$filename])) {
            self::$_cache[$filename] = file_get_contents($filename);
        } else {
            ++ self::$hit;
        }
        return self::$_cache[$filename];
    }

    public static function stats()
    {
        return array(
            'access' => self::$access,
            'hit' => self::$hit
        );
    }
}

FileUtils::init();