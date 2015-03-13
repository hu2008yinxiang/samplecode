<?php

define('cdb_Server', '127.0.0.1');
define('cdb_Username', 'crash_log_user');
define('cdb_Password', 'crash_log_password');
define('cdb_Database', 'crash_log');
define('dbcharset', 'utf8');

// 连接到用户城市所在的数据库
function sql_connect()
{
	$GLOBALS['curConn'] = mysql_pconnect(cdb_Server, cdb_Username, cdb_Password);
	if(!$GLOBALS['curConn']) {
		throw new Exception(mysql_errno() . ": " . mysql_error());
	}
	mysql_set_charset(dbcharset, $GLOBALS['curConn']);
	mysql_select_db(cdb_Database, $GLOBALS['curConn']);
}

function sql_query($sql)
{
	if(!array_key_exists('curConn' , $GLOBALS)) {
		sql_connect();
	}

	try{
		$r = mysql_query($sql,$GLOBALS['curConn']);
	}catch (Exception $e){
		$time = date("Ymd H:i:s") ;
		$errno=mysql_errno($GLOBALS['curConn']);
		$errmsg=mysql_error($GLOBALS['curConn']);
		throw $e ;
	}

	if($r == false)
	{
		$time = date("Ymd H:i:s") ;
		$errno=mysql_errno($GLOBALS['curConn']);
		$errmsg=mysql_error($GLOBALS['curConn']);
		if($errno == 2006) // mysql has gone away， 重新连接一下
		{
			sql_connect($userid, true);

			$r = mysql_query($sql,$GLOBALS['curConn']);
			if($r == false){
				$errno=mysql_errno($GLOBALS['curConn']);
				$errmsg=mysql_error($GLOBALS['curConn']);
				$expn = new Exception("dberror -- $sql -- $errno -- $errmsg");
				throw $expn ;
			}
		}elseif ($errno == 1213){	// 被锁.
			usleep(100000) ;		// 再执行一遍(1/10秒)
			$r = mysql_query($sql,$GLOBALS['curConn']);
			if($r == false){
				$errno=mysql_errno($GLOBALS['curConn']);
				$errmsg=mysql_error($GLOBALS['curConn']);
				$expn = new Exception("dberror -- $sql -- $errno -- $errmsg");
				throw $expn ;
			}
		}else{
			$expn = new Exception("dberror -- $sql -- $errno -- $errmsg");
			throw $expn ;
		}
	}

	return $r;
}

// 无记录则返回""
function sql_fetch_one($sql)
{
	$r = sql_query($sql);

	if(mysql_num_rows($r) == 0)
	{
		// 结果为空则返回
		mysql_free_result($r);
		return "";
	}

	if ((!empty($r))&&($row = mysql_fetch_array($r, MYSQL_ASSOC))) {
		mysql_free_result($r);
		return $row;
	}

	return mysql_error();
}

function sql_fetch_one_cell($sql)
{
	$r = sql_query($sql);

	if ((!empty($r))&&($row = mysql_fetch_array($r, MYSQL_NUM))) {
		mysql_free_result($r);
		return $row[0];
	}
	else
	{
		mysql_free_result($r);
		return false;
	}
	return 0;
}

function sql_fetch_rows($sql)
{
	$r = sql_query($sql);

	$ret = array();
	$row = mysql_fetch_array($r,MYSQL_ASSOC) ;
	while($row) {
		$ret[] = $row;
		$row = mysql_fetch_array($r,MYSQL_ASSOC) ;
	}
	mysql_free_result($r);
	return $ret;
}

function sql_insert($sql)
{
	sql_query($sql);
	return sql_fetch_one_cell('select last_insert_id()');
}

function exceptionToString(Exception $e)
{
	return $e->getMessage();
}
?>
