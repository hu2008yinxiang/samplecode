<?php

class SpecialOfferController extends MvcController
{

    public function indexAction()
    {
        // $data = $this->specialOfferManager->getSpecialOfferData();
        // $iapid = $this->form('iapid', $data['iapid']);
        // $amount = $this->form('amount', $data['amount']);
        // $price = $this->form('price', $data['price']);
        // $life_time = $this->form('life_time', $data['life_time']);
        if ($this->request->isPost() && $this->security->checkToken()) {
            $iapid = $this->request->getPost('iapid', null, array());
            $price = $this->request->getPost('price', null, array());
            $base_slot = $this->request->getPost('base_slot', null, array());
            $delta_slot = $this->request->getPost('delta_slot', null, array());
            $amount = $this->request->getPost('amount', null, array());
            $life_time = $this->request->getPost('life_time', null, array());
            $items = array();
            foreach ($iapid as $index => $id) {
                $items[] = array(
                    'iapid' => $id,
                    'price' => $price[$index],
                    'base_slot' => $base_slot[$index],
                    'delta_slot'=>$delta_slot[$index],
                    'amount' => $amount[$index],
                    'life_time' => $life_time[$index]
                );
            }
            $this->specialOfferManager->setItems($items);
            return $this->response->redirect('special-offer');
        }
        $items = $this->specialOfferManager->getItems();
        $this->view->setVar('items', $items);
    }

    public function ruleAction()
    {
        if ($this->request->isPost() && $this->security->checkToken()) {
            $content = $this->request->getPost('content', null, '');
            $tmp = 'return true;' . $content . ';';
            if (eval($tmp)) {
                // 写入规则
                file_put_contents(DATA_PATH . '/specialOfferRule.php', array(
                    $content
                ));
                return $this->response->redirect('special-offer/rule');
            } else {
                $this->flash->error('Your code has syntax error, check it again.');
            }
            // file_put_contents(DATA_PATH . '/tmp.log', $content);
        } else {
            $content = '
/**
* @param me 传入的用户
*/
return function( \UserAccounts $me, $di){
    if ($me->level >= 3){
        srand(); // reset random seed
        $d = rand(0, 99);
        if( $d < 5 ){
            return true;
        }
    }
    return false;
};';
            $ruleFile = DATA_PATH . '/specialOfferRule.php';
            if (is_file($ruleFile)) {
                $content = file_get_contents($ruleFile);
            }
        }
        $content = htmlentities($content);
        
        $this->view->setVar('content', $content);
    }

    public function helpAction()
    {}
}