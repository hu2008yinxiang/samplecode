<?php

final class Errors
{

    const OK = 0;

    const INVALID_SESSION = 100;

    const REGISTER_FAILED = 101;

    const UNDER_MAINTAINCE = 102;

    const UNKNOWN_CMD = 200;

    const LOGIN_FAILED = 201;

    const BIND_EXPIRED = 202;

    const BAD_NAME = 203;

    const OP_DENIED = 204;

    const EMPTY_DATA = 205;

    const USER_NOT_FOUND = 206;

    const ALREADY_ADDED = 207;

    const ALREADY_REQUESTED = 208;

    const ALREADY_REWARDED = 209;

    const UNSUPPORTED_BUYIN = 210;

    const QUOTA_LIMITED = 211;

    const ALREADY_BINDED = 212;

    const CHIP_NOT_ENOUGH = 213;

    const SEAT_FULLED = 214;

    const DIAMOND_NOT_ENOUGH = 215;

    const CLIENT_NEED_UPDATE = 216;

    const INVALID_ORDER = 217;
    
    const LOCATE_FAILED = 218;

    public static function translate($code)
    {
        static $map = null;
        
        if (is_null($map)) {
            $reflectClass = new ReflectionClass(__CLASS__);
            $map = $reflectClass->getConstants();
            $map = array_flip($map);
        }
        
        if (isset($map[$code]))
            return $map[$code];
        return 'MSG_MISSED';
    }
}