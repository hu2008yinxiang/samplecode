<?php
namespace Mail\Adapter
{

    require 'phar://' . __DIR__ . '/phpmailer.phar/PHPMailerAutoload.php';

    class PHPMailer
    {

        protected $mail = null;

        public function __construct($exceptions = false)
        {
            $this->mail = new \PHPMailer($exceptions);
        }

        public function __call($method, $arguments)
        {
            return call_user_func_array(array(
                $this->mail,
                $method
            ), $arguments);
        }

        public function __get($name)
        {
            return $this->mail->{$name};
        }

        public function __set($name, $value)
        {
            return $this->mail->{$name} = $value;
        }
    }
}