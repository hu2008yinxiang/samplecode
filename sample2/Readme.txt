web下面的放到web服务器下面 symbol_gen是生成符号文件的 dump是打包和生成stack的 需要一个mysql 就一个表
web下面的files和dump2个目录下面都有lwdb 里面要配置数据库的host之类的 看看应该就懂了
symbol_gen和dump下面有一些路径要改 你看看代码应该就知道了

crontab
*/10 * * * * sh /disk1/symbol_gen/genSym.sh  2>&1 >>/disk1/symbol_gen/genSym.log
1 * * * * sh /home/ec2-user/FileManager/cron/pack_list.sh  2>&1 >/dev/null

/disk1/symbol_gen/genSym.sh: /usr/local/bin/dump_syms 这个要官网下下来编译
