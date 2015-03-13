<?php

/**
 * 日期工具类
 * @author 继续
 *
 */
class DateUtils
{
    /**
     * 获取日期的年周值
     * @param \DateTime $date
     */
    public static function getYearWeek(\DateTime $date = null)
    {
    	if(is_null($date)){
    		$date = new \DateTime();
    	}
    	//
    	//
    	//$date->add($interval);
    	return $date->format('YW');
    }
}