<?php

class MonthlyOfferController extends Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $offer = $this->shopManager->getMonthlyOffer();
        if ($this->request->isPost() && $this->security->checkToken()) {
            $iapid = $this->request->getPost('iapid', null, $offer['iapid']);
            $price = $this->request->getPost('price', null, $offer['price']);
            $amount = $this->request->getPost('amount', null, $offer['amount']);
            $perday = $this->request->getPost('perday', null, $offer['perday']);
            $offer = array(
                'iapid' => $iapid,
                'price' => $price,
                'amount' => $amount,
                'perday' => $perday
            );
            Misc::cacheToFile($offer, DATA_PATH . '/monthlyOffer.php');
            return $this->response->redirect('monthly-offer');
        }
        $this->view->setVar('offer', $offer);
    }
}