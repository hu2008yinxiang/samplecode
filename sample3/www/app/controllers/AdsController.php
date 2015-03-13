<?php

class AdsController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $pageIndex = $this->request->get('p', null, 1);
        if ($this->request->isPost()) {
            $action = $this->request->get('action');
            $aid = $this->request->get('aid');
            switch ($action) {
                case 'delete':
                    $adItem = AdItems::findFirst(array(
                        'aid = :aid:',
                        'bind' => array(
                            'aid' => $aid
                        )
                    ));
                    if ($adItem) {
                        $adItem->delete();
                    }
                    break;
            }
            return $this->response->redirect('/ads?p=' . $pageIndex);
        }
        $qb = $this->modelsManager->createBuilder()
            ->from('AdItems')
            ->orderBy('aid DESC');
        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder(array(
            'builder' => $qb,
            'limit' => 5,
            'page' => $pageIndex
        ));
        $page = $paginator->getPaginate();
        $this->view->setVar('page', $page);
    }

    public function addAction()
    {
        if ($this->request->isPost()) {
            $params = $this->request->get();
            foreach ($params as $k => $v) {
                $this->view->setVar($k, $v);
            }
            $files = $this->request->getUploadedFiles(true);
            $image = null;
            foreach ($files as $k => $file) {
                if ($file->getKey() == 'image') {
                    $image = $file;
                    break;
                }
            }
            if (empty($image)) {
                $this->flash->error('No file uploaded or the uploading is failed! [no file]');
                return;
            }
            $ext = $image->getExtension();
            if (! $ext) {
                $ext = pathinfo($image->getName(), PATHINFO_EXTENSION);
            }
            $filename = md5_file($image->getTempName());
            $exts = array(
                'gif',
                'jpg',
                'jpeg',
                'png',
                'bmp'
            );
            if (! $filename || ! in_array($ext, $exts)) {
                $this->flash->error('No file uploaded or the uploading is failed! [not match]' . $ext);
                return;
            }
            $filename .= '.' . $ext;
            $dir = DATA_PATH . '/images';
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $ret = $image->moveTo($dir . '/' . $filename);
            // $image->moveTo(DATA_PATH.'/images/')
            if (! $ret) {
                $this->flash->error('No file uploaded or the uploading is failed! [move to]');
                return;
            }
            $adItem = new AdItems();
            $adItem->image = $filename;
            $adItem->url = $params['url'];
            $adItem->type = $params['type'];
            $adItem->package = $params['package'];
            $adItem->interval = 2;
            $ret = $adItem->save();
            if (! $ret) {
                $this->flash->error('Save faild!');
                foreach ($adItem->getMessages() as $msg) {
                    $this->flash->warning($msg->getMessage());
                }
                @unlink($dir . '/' . $filename); // 删除文件
                return;
            }
            $this->flash->success('Save success!');
        }
    }
}