<?php

class RedirectController extends Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $aid = $this->request->get('aid', null, '');
        $ad = AdItems::findFirst(array(
            'aid = :aid:',
            'bind' => array(
                'aid' => $aid
            )
        ));
        if (! $ad) {
            $ad = AdItems::findFirst();
        }
        return $this->response->redirect($ad->getUrl());
    }
}