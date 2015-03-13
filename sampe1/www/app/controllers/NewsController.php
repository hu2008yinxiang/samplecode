<?php

class NewsController extends Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $action = $this->request->getQuery('action');
        if (! empty($action) && $this->security->checkToken($this->request->getQuery('key'), $this->request->getQuery('token'))) {
            switch ($action) {
                case 'del':
                    $id = $this->request->getQuery('id');
                    $this->modelsManager->createQuery('DELETE FROM News where news_id = :id:')->execute(array(
                        'id' => $id
                    ));
                    break;
            }
            return $this->response->redirect('news');
        }
        $news = News::find(array(
            'orderBy' => 'news_id DESC'
        ));
        $this->view->setVar('news', $news);
    }

    public function editAction()
    {
        $types = array(
            News::TYPE_FESTIVAL,
            News::TYPE_SPECIAL_OFFER,
            News::TYPE_CHIP_SENDER,
            News::TYPE_EXTRA_LOGIN_BONUS,
            News::TYPE_EXTRA_TASK_BONUS_ROYAL_STAIGHT_FLUSH,
            News::TYPE_EXTRA_TASK_BONUS_STAIGHT_FLUSH,
            News::TYPE_EXTRA_TASK_BONUS_4_OF_A_KIND
        );
        $id = $this->request->getQuery('id');
        $news = News::findFirstByNewsId($id);
        if (! $news) {
            return $this->response->redirect('news');
        }
        if ($this->request->isPost() && $this->security->checkToken()) {
            $news->type = $type = $this->request->getPost('type');
            $news->start_date = $start_date = $this->request->getPost('start_date');
            $news->end_date = $end_date = $this->request->getPost('end_date');
            $news->title = $title = $this->request->getPost('title');
            $news->content = $content = $this->request->getPost('content');
            if (! in_array($type, $types)) {
                $this->flashSession->error('Type is not valid!');
            }
            $start_date = date_create_from_format('Y-m-d H:i:s', $start_date);
            $end_date = date_create_from_format('Y-m-d H:i:s', $end_date);
            if ($start_date == false || $end_date == false) {
                $this->flashSession->error('Start and End is not valid!');
            }
            if ($type == News::TYPE_FESTIVAL) {
                if (empty($title)) {
                    $this->flashSession->error('Title of Festival should not be empty.');
                }
                $parsed = explode(',', $content);
                if (! is_array($parsed) || count($parsed) != 7) {
                    $this->flashSession->error('Content of Festival is not valid');
                }
            }
            
            if (! $this->flashSession->has('error')) {
                $news->save();
                return $this->response->redirect('news');
            }
        }
        $this->view->setVar('news', $news);
        
        $this->view->setVar('types', $types);
    }

    public function addAction()
    {
        $types = array(
            News::TYPE_FESTIVAL,
            News::TYPE_SPECIAL_OFFER,
            News::TYPE_CHIP_SENDER,
            News::TYPE_EXTRA_LOGIN_BONUS,
            News::TYPE_EXTRA_TASK_BONUS_ROYAL_STAIGHT_FLUSH,
            News::TYPE_EXTRA_TASK_BONUS_STAIGHT_FLUSH,
            News::TYPE_EXTRA_TASK_BONUS_4_OF_A_KIND
        );
        $news = new News();
        if ($this->request->isPost() && $this->security->checkToken()) {
            $news->type = $type = $this->request->getPost('type');
            $news->start_date = $start_date = $this->request->getPost('start_date');
            $news->end_date = $end_date = $this->request->getPost('end_date');
            $news->title = $title = $this->request->getPost('title');
            $news->content = $content = $this->request->getPost('content');
            if (! in_array($type, $types)) {
                $this->flashSession->error('Type is not valid!');
            }
            $start_date = date_create_from_format('Y-m-d H:i:s', $start_date);
            $end_date = date_create_from_format('Y-m-d H:i:s', $end_date);
            if ($start_date == false || $end_date == false) {
                $this->flashSession->error('Start and End is not valid!');
            }
            if ($type == News::TYPE_FESTIVAL) {
                if (empty($title)) {
                    $this->flashSession->error('Title of Festival should not be empty.');
                }
                $parsed = explode(',', $content);
                if (! is_array($parsed) || count($parsed) != 7) {
                    $this->flashSession->error('Content of Festival is not valid');
                }
                if (! $this->request->hasFiles(true)) {
                    $this->flashSession->error('Image of Festival is not valid.');
                }
            }
            
            if (! $this->flashSession->has('error')) {
                $news->save();
                if ($news->type == \News::TYPE_FESTIVAL) {
                    $filePath = DATA_PATH . '/news/images/' . $news->news_id . '.png';
                    $fileFolder = dirname($filePath);
                    mkdir($fileFolder, 0777, true);
                    $files = $this->request->getUploadedFiles(true);
                    $files[0]->moveTo($filePath);
                }
                return $this->response->redirect('news');
            }
        }
        
        $this->view->setVar('news', $news);
        
        $this->view->setVar('types', $types);
    }

    public function previewAction()
    {
        $result = $this->modelsManager->createQuery('SELECT news_id FROM News ORDER BY news_id DESC')->execute();
        $ids = array();
        foreach ($result as $r) {
            $ids[] = $r['news_id'];
        }
        $ids_str = implode(',', $ids);
        $this->view->setVar('ids', $ids_str);
    }
}