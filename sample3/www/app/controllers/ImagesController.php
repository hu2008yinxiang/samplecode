<?php

/**
 * @author 继续
 * @RoutePrefix("/dimages")
 */
class ImagesController extends \Phalcon\Mvc\Controller
{

    /**
     * @Route("/f/{filename}")
     */
    public function readAction($filename)
    {
        $filename = DATA_PATH . '/images/' . $filename;
        if (! is_file($filename)) {
            $this->dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'route404'
            ));
            return;
        }
        $response = $this->response;
        $contentType = 'image/' . pathinfo($filename, PATHINFO_EXTENSION);
        $response->setContentType($contentType);
        $response->setContent(file_get_contents($filename));
        $response->setExpires(new DateTime('1 week'));
        return $response;
    }
}