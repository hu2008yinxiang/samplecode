Xhprof 官网 https://github.com/facebook/xhprof

安装
Windows 下安装
在 http://windows.php.net/downloads/pecl/releases/xhprof/ 下载合适的版本进行安装配置即可
需要下载xhprof_html xhprof_lib来在浏览器中查看分析结果
需要安装Graphviz来看调用图 http://www.graphviz.org/Download_windows.php
http://www.graphviz.org/pub/graphviz/stable/windows/‎

Linux 下安装
从 http://pecl.php.net/package/xhprof 下载或者

git clone https://github.com/facebook/xhprof.git
cd xhprof/extension
phpize
./configure
make 
make install
