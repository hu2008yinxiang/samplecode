web����ķŵ�web���������� symbol_gen�����ɷ����ļ��� dump�Ǵ��������stack�� ��Ҫһ��mysql ��һ����
web�����files��dump2��Ŀ¼���涼��lwdb ����Ҫ�������ݿ��host֮��� ����Ӧ�þͶ���
symbol_gen��dump������һЩ·��Ҫ�� �㿴������Ӧ�þ�֪����

crontab
*/10 * * * * sh /disk1/symbol_gen/genSym.sh  2>&1 >>/disk1/symbol_gen/genSym.log
1 * * * * sh /home/ec2-user/FileManager/cron/pack_list.sh  2>&1 >/dev/null

/disk1/symbol_gen/genSym.sh: /usr/local/bin/dump_syms ���Ҫ��������������
