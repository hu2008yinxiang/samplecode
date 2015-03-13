<?php

class ManageController extends \Phalcon\Mvc\Controller {

	public function indexAction() {
		if ($this->request->isPost ()) {
			$action = $this->request->get ( 'action' );
			switch ($action) {
				case 'delete_so' :
					$project = $this->request->get ( 'project', NULL, '' );
					$versionCode = $this->request->get ( 'versionCode', NULL, '' );
					if (empty ( $project ) || empty ( $versionCode )) {
						$this->flashSession->error ( '项目或版本号不能为空！' );
						break;
					}
					$so_file = \Storage::getFile ( '/so/' . $project . '/' . $versionCode . '/libgame.so' );
					if ((! file_exists ( $so_file )) || @unlink ( $so_file )) {
						$this->flashSession->success ( '成功删除SO文件！' );
					} else {
						$this->flashSession->error ( '删除SO文件失败！' );
					}
					$sym_mark = $so_file . '.mark';
					if (! file_exists ( $sym_mark )) {
						break;
					}
					$module_info = preg_split ( '[\s]', file_get_contents ( $sym_mark ) );
					$module_info ['platform'] = $module_info [1];
					$module_info ['architecture'] = $module_info [2];
					$module_info ['hash'] = $module_info [3];
					$module_info ['module'] = $module_info [4];
					@unlink ( $sym_mark );
					$sym_dir = \Storage::getDir ( '/symbols/' . $module_info ['module'] . '/' . $module_info ['hash'], true );
					
					$sym_file = $sym_dir . '/' . $module_info ['module'] . '.sym';
					if ((! file_exists ( $sym_file )) || @unlink ( $sym_file )) {
						@rmdir ( $sym_dir );
						$this->flashSession->success ( '成功删除SYM文件！' );
					} else {
						$this->flashSession->error ( '删除SYM文件失败！' );
					}
					
					break;
			}
			return $this->response->redirect ( "manage/index" );
		}
	}
}