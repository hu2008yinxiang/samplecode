配置文件在 app/config/config.ini
数据库脚本在 app/db
cron 任务安装请执行 php app/cron/install_archieve_dump_cron.php 再次执行会卸载cron任务

302 上传地址 http://thinkgeek.vicp.net:28080/crash_log/www/index.php/upload/

上传DMP文件的请求要求
方式 HTTP POST
内容编码 multipart/form-data
参数：
	project : 必需 包名
	versionCode : 必需 版本号
	file : 必需 附加要上传的DMP文件
	status : 必需 应用状态 release或debug
	brand : 可选 手机品牌
	model : 可选 手机型号
	manufacturer : 可选 制造商
	versionName : 可选 应用版本名
	androidSdkInt : 可选 android API版本
	
	
