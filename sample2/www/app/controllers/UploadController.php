<?php
use Phalcon\Mvc\Application;

class UploadController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $config = $this->di->get('config');
        // $this->view->disable ();
        if ($this->request->isPost()) {
            $param_keys = array(
                'project',
                'versionCode',
                'model',
                'androidSdkInt',
                'versionName',
                'manufacturer',
                'brand',
                'status'
            );
            $param = array();
            foreach ($param_keys as $key) {
                $param[$key] = trim($this->request->get($key, NULL, ''));
            }
            if (empty($param['project']) || empty($param['versionCode'])) {
                $this->flashSession->error('project和versionCode不能为空！');
                return;
            }
            $config = $this->di->get('config');
            $blocked_pkgs = explode(';', $config->black_list->pkg);
            $blocked_versions = explode(';', $config->black_list->version);
            if (in_array($param['project'], $blocked_pkgs) || in_array($param['versionCode'], $blocked_versions)) {
                $this->response->setStatusCode(404, 'Not Found!');
		            $this->view->disable();
                return;
                //return $this->response;
            }
            if (! $this->request->hasFiles()) {
                $this->flashSession->error('没有上传文件！');
                return;
            }
            $param['time_stamp'] = date('Y-m-d H:i:s');
            $files = $this->request->getUploadedFiles(true);
            foreach ($files as $file) {
                $param['file_name'] = $file->getName();
                $dump_dir = \Storage::getDir('/dump/' . $param['project'] . '/' . $param['versionCode'], true);
                $file_path = $dump_dir . '/' . $param['file_name'];
                $file->moveTo($file_path);
                $param['proc'] = "UN_PROCESSED";
                $record = new \CrashRecords();
                if ($record->save($param)) {
                    // $this->flash->success($message)
                    $this->flashSession->success($param['file_name'] . '上传成功！');
                    // $param = $record->toArray ();
                    // $param ['file_path'] = $file_path;
                    // $param_str = json_encode ( $param );
                    $cmd = 'php ' . APPLICATION_PATH . '/cli.php ' . 'Crash_Record afterUpload ' . $record->record_id;
                    \Utils::execbg($cmd);
                    $this->flashSession->success($file_path . ' 已在后台进行分析 ' . \Phalcon\Tag::linkTo(array(
                        array(
                            'for' => 'ViewCrash::showByRecordID',
                            'record_id' => $record->record_id
                        ),
                        '请稍等几秒点此查看结果'
                    )) . '。');
                } else {
                    $this->flashSession->error($record['file_name'] . '保存记录失败');
                }
            }
            //return $this->response->redirect('/upload/index');
            $this->view->disable();
            echo '1';
            return;
        }
        $this->view->enable();
    }

    public function soAction()
    {
        if ($this->request->isPost()) {
            $project = $this->request->get('project');
            $versionCode = $this->request->get('versionCode');
            $hasFile = $this->request->hasFiles();
            if (empty($project) || empty($versionCode) || ! $hasFile) {
                $this->flashSession->error('表单项不完整！');
                return;
            }
            $files = $this->request->getUploadedFiles(true);
            if (empty($files)) {
                $this->flashSession->error('文件上传失败，文件不完整！');
                return;
            }
            $file = $files[0];
            $size = $file->getSize();
            $so_dir = \Storage::getDir('/so/' . $project . '/' . $versionCode, true);
            $so_file = $so_dir . '/libgame.so';
            $result = true;
            // 备份旧SO
            if (file_exists($so_file)) {
                $index = 0;
                while (true) {
                    $so_bak = $so_file . '.' . $index;
                    if (! file_exists($so_bak)) {
                        $result = rename($so_file, $so_bak) && $result;
                        break;
                    }
                    $index ++;
                }
            }
            $result = $file->moveTo($so_file) && $result;
            if (! $result) {
                $this->flashSession->error('保存上传文件失败，可能是有文件正在后台进行解析！请稍后重试。');
                return;
            }
            $this->flashSession->success('上传完成！文件大小 [' . $size . ']');
            $cmd = 'php ' . APPLICATION_PATH . '/app/cli.php Crash_Record genSymbol ' . $project . ' ' . $versionCode;
            \Utils::execbg($cmd);
        }
    }
}
