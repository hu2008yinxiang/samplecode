<?php
namespace Mail\Adapter
{

    class SysDefault
    {

        public function send($to, $subject, $content, $extraHeaders)
        {
            return mail($to, $subject, $message, $extraHeader);
        }
    }
}