<?php

class ChargeController extends Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $keyword = $this->request->getQuery('keyword');
        if (! empty($keyword)) {
            $user = UserAccounts::findFirst(array(
                'account_id = :id: OR nickname like :key:',
                'bind' => array(
                    'id' => $keyword,
                    'key' => '%' . $keyword . '%'
                )
            ));
            $this->view->setVar('user', $user);
        }
        $this->view->setVar('keyword', $keyword);
    }

    public function goAction()
    {
        $id = $this->request->getQuery('id');
        $user = UserAccounts::findFirstByAccountId($id);
        if (! $user) {
            return $this->response->redirect('/charge');
        }
        $this->view->setVar('user', $user);
        if ($this->request->isPost() && $this->security->checkToken()) {
            $chip = intval($this->request->getPost('chip', null, 0));
            $diamond = intval($this->request->getPost('diamond', null, 0));
            $chip = max($chip, 0);
            $diamond = max($diamond, 0);
            if ($chip == 0 && $diamond == 0) {
                return $this->response->redirect('/charge');
            }
            //
            $user->chip += $chip;
            $user->diamond += $diamond;
            $user->save();
            //
            $task = DailyTasks::load($user->account_id, DailyTasks::GAMEYEPER, 0);
            $task->current += intval($chip / 5 + $diamond * 500);
            $task->save();
            //
            $extras = Extras::load($user->account_id, Extras::EXTRA_THRESHOLD, 0);
            $extras->value = ($user->chip * 2 + $diamond * 5000);
            $extras->save();
            //
            $this->flashSession->success('Charged Success.');
            return $this->response->redirect('/charge');
        }
    }
}