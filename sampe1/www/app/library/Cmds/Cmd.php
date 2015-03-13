<?php
namespace Cmds;

/**
 * 指令封装
 *
 * @author 继续
 *        
 */
class Cmd extends \Phalcon\Mvc\User\Component
{

    /**
     * 命令名称，会在execute的返回值中作为默认的cmd值
     *
     * @var string
     */
    const CMD_NAME = 'Cmd';

    /**
     * Cmd的参数
     *
     * @var array
     */
    protected $data;

    /**
     * 当前用户id
     *
     * @var string
     */
    protected $account_id;

    /**
     * 命令返回值
     *
     * @var array
     */
    protected $ret = null;

    /**
     * Cmd的默认参数
     *
     * @return array:用于填充Cmd默认参数，会与构造函数中的data进行合并
     */
    public static function defaultData()
    {
        return array();
    }

    /**
     * 构造函数
     *
     * @param array $data            
     * @param string $account_id            
     */
    public function __construct(array $data, $account_id)
    {
        $data = array_merge(static::defaultData(), $data);
        $this->data = $data;
        $this->account_id = $account_id;
        //$this->me = \UserAccounts::findFirstByAccountId($account_id);
        $this->ret = array(
            'stop' => false,
            'replace' => false,
            'result' => array(
                'cmd' => static::CMD_NAME,
                'errno' => \Errors::OK
            )
        );
    }

    public function check()
    {
        return true;
    }

    /**
     * 真正处理Cmd执行逻辑的地方
     *
     * @return void
     */
    protected function do_execute()
    {}

    /**
     * 执行Cmd并返回执行结果，内部调用 \Cmds\Cmd::do_execute()填充返回值
     *
     * @see \Cmds\Cmd::do_execute()
     *
     * @return multitype:boolean multitype:string number
     */
    public function execute()
    {
        $this->do_execute();
        $this->ret['result']['errno'] = isset($this->ret['result']['errno']) ? $this->ret['result']['errno'] : \Errors::OK;
        $trans = \Errors::translate($this->ret['result']['errno']);
        $this->ret['result']['error'] = $trans;
        // $this->afterExecute();
        ksort($this->ret);
        return $this->ret;
    }

    public function &getReturn()
    {
        return $this->ret;
    }
    
    public function getMe(){
        static $me = null;
        if(is_null($me)){
            $me = \UserAccounts::findFirstByAccountId($this->account_id);
        }
        return $me;
    }
}