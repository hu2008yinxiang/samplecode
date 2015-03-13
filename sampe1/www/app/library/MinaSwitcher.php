<?php

class MinaSwitcher extends Phalcon\Mvc\User\Component
{

    protected $configPath = null;

    protected $data = null;

    public function setDI($di)
    {
        parent::setDI($di);
        $this->data = array();
        $this->configPath = $di->get('config')->mina->configPath;
        if (is_file($this->configPath)) {
            $this->data = include $this->configPath;
        }
    }

    public function getMinaServers($onlyActive = true)
    {
        if ($onlyActive) {
            if (empty($this->data) || empty($this->data['active'])) {
                error_log('NO MINA Server found');
                $tmp = explode(':', $_SERVER['HTTP_HOST']);
                return array(
                    array(
                        'host' => $tmp[0],
                        'port' => 48090
                    )
                );
            }
            return $this->data['active'];
        }
        return $this->data;
    }

    public function setMinaServers($data)
    {
        $this->data = $data;
        \Misc::cacheToFile($data, $this->configPath);
    }

    /**
     * 切换MINA
     *
     * @param string $host            
     * @param string $port            
     * @return boolean
     */
    public function switchMina($host, $port)
    {
        // 获取当前服务的所有MINA
        $actives = empty($this->data['active']) ? array() : $this->data['active'];
        $found = false;
        foreach ($actives as &$srv) {
            if ($port == $srv['port']) {
                // TODO 以后加入主机名判断
                $found = true;
                // 找到mina 为异常退出
                $srv['port'] += ($srv['port'] % 2 ? - 1 : 1); // 调整端口号
            }
        }
        // 没找到 正常切换 不做任何事
        if ($found) { // 找到 更新服务器配置
            $this->data['active'] = $actives;
            $this->setMinaServers($this->data);
            MailTask::cliSendMail(array(
                'title' => 'MINA Switched Unexpectly.',
                'level' => 'WARN',
                'catalog' => 'MINA',
                'msg' => sprintf('<p style="font-size: 18pt;">The MINA(<strong style="color:red;">%s:%s</strong>) has reported an unexpected halt, another MINA(<strong style="color:green">%s:%s</strong>) will start. please check the MINA status.</p>', $host, $port, $host, $port + ($port % 2 ? - 1 : 1))
            ));
        }
        return $found;
    }

    /**
     * 判断指定的mina是否活跃中
     * 
     * @param string $host            
     * @param integer $port            
     * @return boolean
     */
    public function isActive($host, $port)
    {
        $actives = empty($this->data['active']) ? array() : $this->data['active'];
        $found = false;
        foreach ($actives as $srv) {
            if ($port == $srv['port']) {
                // TODO 以后加入主机名判断
                $found = true;
                // 找到mina 为异常退出
                // $srv['port'] += ($srv['port'] % 2 ? - 1 : 1); // 调整端口号
                break;
            }
        }
        return $found;
    }
}