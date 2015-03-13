<?php
namespace View\Engine
{

    require 'phar://' . __DIR__ . '/PHPTAL-1.3.0.phar/PHPTAL.php';
    require __DIR__ . '/PHPTALS.php';

    class PHPTAL extends \Phalcon\Mvc\View\Engine
    {

        protected $_phptal = null;

        public function __construct($view, $di = null)
        {
            parent::__construct($view, $di);
            $this->_phptal = new \PHPTAL();
            $this->initialPHPTAL();
        }

        protected function initialPHPTAL()
        {
            $this->_phptal->set('di', $this->di);
            $this->_phptal->set('view', $this->_view);
            $this->_phptal->setForceReparse(true);
        }

        public function render($path, $params, $mustClean = null)
        {
            if (! isset($params['content'])) {
                $params['content'] = $this->_view->getContent();
            }
            $phptal = $this->_phptal;
            $phptal->setTemplate($path);
            if (is_array($params)) {
                array_walk($params, function (&$item, $key, &$target)
                {
                    $target->set($key, $item);
                }, $phptal);
            }
            $content = $phptal->execute();
            if ($mustClean) {
                $this->_view->setContent($content);
            } else {
                echo $content;
            }
        }
    }
}
