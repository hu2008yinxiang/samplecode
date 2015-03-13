<?php

class ViewCrashController extends \Phalcon\Mvc\Controller {

	public function indexAction() {
		$releaseCond = array (
				'conditions' => 'status = :status:',
				'bind' => array (
						'status' => 'release'
				)
		);
		
		$debugCond = array (
				'conditions' => 'status = :status:',
				'bind' => array (
						'status' => 'debug'
				)
		);
		
		$builder = $this->modelsManager->createBuilder ()
			->from ( 'CrashRecords' )
			->where ( $releaseCond ['conditions'], $releaseCond ['bind'] )
			->columns ( array (
				'project',
				'COUNT(project) AS count'
		) )
			->groupBy ( 'project' )
			->orderBy ( 'versionCode DESC' );
		$releaseCrash = $builder->getQuery ()->execute ();
		$builder = $this->modelsManager->createBuilder ()
			->from ( 'CrashRecords' )
			->where ( $debugCond ['conditions'], $debugCond ['bind'] )
			->columns ( array (
				'project',
				'COUNT(project) AS count'
		) )
			->groupBy ( 'project' )
			->orderBy ( 'versionCode DESC' );
		$debugCrash = $builder->getQuery ()->execute ();
		
		// $this->releaseCrash = $releaseCrash;
		// $this->debugCrash = $$debugCrash;
		$this->view->setVar ( 'releaseCrash', $releaseCrash );
		$this->view->setVar ( 'debugCrash', $debugCrash );
	}

	public function projectAction() {
		$project = $this->request->get ( 'project' );
		$status = $this->request->get ( 'status', NULL, 'release' );
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->where ( 'project = :project:', array (
				'project' => $project
		) )
			->andWhere ( 'status = :status:', array (
				'status' => $status
		) )
			->columns ( array (
				'project',
				'versionCode',
				'COUNT(versionCode) AS count'
		) )
			->groupBy ( 'project,versionCode' )
			->orderBy ( 'versionCode DESC' );
		$query = $builder->getQuery ();
		$result = $query->execute ();
		$this->view->setVar ( 'result', $result );
		$this->view->setVar ( 'project', $project );
		$this->view->setVar ( 'status', $status );
	}

	public function showByMD5Action() {
		$status = $this->dispatcher->getParam ( 'status' );
		$project = $this->dispatcher->getParam ( 'project' );
		$versionCode = $this->dispatcher->getParam ( 'versionCode' );
		$md5 = $this->dispatcher->getParam ( 'md5' );
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->columns ( [
				'CrashRecords.*'
		] )
			->where ( 'status = :status:' )
			->andWhere ( 'project = :project:' )
			->andWhere ( 'versionCode = :versionCode:' )
			->andWhere ( 'proc = :md5:' )
			->orderBy ( 'record_id DESC' );
		$query = $builder->getQuery ();
		$params = array (
				'status' => $status,
				'project' => $project,
				'versionCode' => $versionCode,
				'md5' => $md5
		);
		$result = $query->execute ( $params );
		$this->view->setVar ( 'records', $result );
		$this->view->setVar ( 'params', $params );
		
		$this->view->setVar ( 'view_by_md5', $this->url->get ( array (
				'for' => 'ViewCrash::showByMD5',
				'status' => $status,
				'project' => $project,
				'versionCode' => $versionCode,
				'md5' => $md5
		) ) );
		$this->view->setVar ( 'view_by_versionCode', $this->url->get ( array (
				'for' => 'ViewCrash::showByVersionCode',
				'status' => $status,
				'project' => $project,
				'versionCode' => $versionCode
		) ) );
		$this->view->setVar ( 'view_by_project', $this->url->get ( array (
				'for' => 'ViewCrash::showByProject',
				'status' => $status,
				'project' => $project
		) ) );
		$this->view->setVar ( 'view_by_status', $this->url->get ( array (
				'for' => 'ViewCrash::showByStatus',
				'status' => $status
		) ) );
		$this->view->setVar ( 'title', "/$status/$project/$versionCode/$md5" );
		$stack_file = \Storage::getFile ( '/stack/' . $project . '/' . $versionCode . '/' . $md5 . '.stack' );
		$this->view->setVar ( 'stack', $stack_file . ' 堆栈文件不存在。' );
		if (file_exists ( $stack_file ))
			$this->view->setVar ( 'stack', file_get_contents ( $stack_file ) );
		
		$this->view->pick ( 'view/by-md5' );
	}

	public function showByVersionCodeAction() {
		$status = $this->dispatcher->getParam ( 'status' );
		$project = $this->dispatcher->getParam ( 'project' );
		$versionCode = $this->dispatcher->getParam ( 'versionCode' );
		
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->where ( 'status = :status:' )
			->andWhere ( 'project = :project:' )
			->andWhere ( 'versionCode = :versionCode:' )
			->columns ( [
				'status',
				'project',
				'versionCode',
				'proc',
				'COUNT(proc) AS count'
		] )
			->groupBy ( [
				'proc'
		] )
			->orderBy ( 'count desc' );
		$query = $builder->getQuery ();
		$params = array (
				'status' => $status,
				'project' => $project,
				'versionCode' => $versionCode
		);
		$records = $query->execute ( $params );
		$this->view->setVar ( 'records', $records );
		$this->view->setVar ( 'params', $params );
		$this->view->setVar ( 'view_by_versionCode_url', $this->url->get ( array (
				'for' => 'ViewCrash::showByVersionCode',
				'status' => $status,
				'project' => $project,
				'versionCode' => $versionCode
		) ) );
		$this->view->setVar ( 'view_by_project', $this->url->get ( array (
				'for' => 'ViewCrash::showByProject',
				'status' => $status,
				'project' => $project
		) ) );
		$this->view->setVar ( 'view_by_status', $this->url->get ( array (
				'for' => 'ViewCrash::showByStatus',
				'status' => $status
		) ) );
		
		$title = "/$status/$project/$versionCode";
		$this->view->setVar ( 'title', $title );
		$this->view->pick ( 'view/by-versionCode' );
	}

	public function showByProjectAction() {
		$status = $this->dispatcher->getParam ( 'status' );
		$project = $this->dispatcher->getParam ( 'project' );
		$params = array (
				'status' => $status,
				'project' => $project
		);
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->where ( 'status = :status:' )
			->andWhere ( 'project = :project:' )
			->columns ( [
				'status',
				'project',
				'versionCode',
				'COUNT(versionCode) AS count'
		] )
			->groupBy ( [
				'versionCode'
		] )
			->orderBy ( 'count DESC' );
		$query = $builder->getQuery ();
		$records = $query->execute ( $params );
		$this->view->setVar ( 'params', $params );
		$this->view->setVar ( 'records', $records );
		$this->view->setVar ( 'title', "/$status/$project" );
		$this->view->setVar ( 'view_by_project', $this->url->get ( array (
				'for' => 'ViewCrash::showByProject',
				'status' => $status,
				'project' => $project
		) ) );
		$this->view->setVar ( 'view_by_status', $this->url->get ( array (
				'for' => 'ViewCrash::showByStatus',
				'status' => $status
		) ) );
		$this->view->pick ( 'view/by-project' );
	}

	public function showByStatusAction() {
		$status = $this->dispatcher->getParam ( 'status' );
		$title = '/' . $status;
		$params = array (
				'status' => $status
		);
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->where ( 'status = :status:' )
			->columns ( [
				'status',
				'project',
				'COUNT(project) AS count'
		] )
			->groupBy ( 'project' );
		$query = $builder->getQuery ();
		
		$records = $query->execute ( $params );
		$this->view->setVar ( 'params', $params );
		$this->view->setVar ( 'records', $records );
		$this->view->setVar ( 'view_by_status', $this->url->get ( array (
				'for' => 'ViewCrash::showByStatus',
				'status' => $status
		) ) );
		$this->view->setVar ( 'title', $title );
		$this->view->pick ( 'view/by-status' );
	}

	public function showByRecordIDAction() {
		$record_id = $this->dispatcher->getParam ( 'record_id' );
		$record = \CrashRecords::findFirst ( $record_id );
		$this->view->setVar ( 'record', $record );
		if (! $record) {
			return;
		}
		$stack_file = \Storage::getFile ( '/stack/' . $record->project . '/' . $record->versionCode . '/' . $record->proc . '.stack' );
		$stack = $stack_file . ' 堆栈文件不存在。';
		if (file_exists ( $stack_file )) {
			$stack = file_get_contents ( $stack_file );
		}
		$this->view->setVar ( 'stack', $stack );
		$this->view->pick ( 'view/by-record' );
		$this->view->setVar ( 'title', '崩溃报告' . $record_id . '的详情' );
		$this->view->setVar ( 'view_by_status', $this->url->get ( array (
				'for' => 'ViewCrash::showByStatus',
				'status' => $record->status
		) ) );
		$this->view->setVar ( 'view_by_project', $this->url->get ( array (
				'for' => 'ViewCrash::showByProject',
				'status' => $record->status,
				'project' => $record->project
		) ) );
		$this->view->setVar ( 'view_by_versionCode', $this->url->get ( array (
				'for' => 'ViewCrash::showByVersionCode',
				'status' => $record->status,
				'project' => $record->project,
				'versionCode' => $record->versionCode
		) ) );
		$this->view->setVar ( 'view_by_md5', $this->url->get ( array (
				'for' => 'ViewCrash::showByMD5',
				'status' => $record->status,
				'project' => $record->project,
				'versionCode' => $record->versionCode,
				'md5' => $record->proc
		) ) );
		$this->view->setVar ( 'view_by_record', $view_by_record = $this->url->get ( array (
				'for' => 'ViewCrash::showByRecordID',
				'record_id' => $record_id
		) ) );
		$view = $this->view;
		$view->setVar ( 'redump_url', $this->url->get ( array (
				'for' => 'ViewCrash::showByRecordID',
				'record_id' => $record_id
		), array (
				'redump' => 1
		) ) );
		$view->setVar ( 'async_redump_url', $this->url->get ( array (
				'for' => 'ViewCrash::showByRecordID',
				'record_id' => $record_id
		), array (
				'redump' => 1,
				'async' => 1
		) ) );
		
		if ($this->request->hasQuery ( 'redump' )) {
			$record->proc='GENERATING_PLEASE_WAIT';
			$record->save();
			$cmd = 'php ' . APPLICATION_PATH . '/cli.php ' . 'Crash_Record afterUpload ' . $record->record_id . ' 1';
			if ($this->request->hasQuery ( 'async' )) {
				\Utils::execbg ( $cmd );
				$this->flashSession->success ( '已在后台分析堆栈[' . $record->file_name . ']，请稍后刷新查看' );
			} else {
				$return_var = - 1;
				$output = array ();
				exec ( $cmd, $output, $return_var );
				if ($return_var) {
					$this->flashSession->warning ( '堆栈分析中产生了错误，分析结果可能不正确。' );
				} else {
					$this->flashSession->success ( '已重新分析堆栈[' . $record->file_name . ']' );
				}
			}
			return $this->response->redirect ( 'view/record/' . $record->record_id );
			// return;
		}
		if ($this->request->has ( 'force' )) {
		}
	}

	public function showAllAction() {
		$query = $this->modelsManager->createBuilder ()
			->from ( 'CrashRecords' )
			->where ( 'status = :status:' )
			->orderBy ( 'count DESC,versionCode DESC' )
			->columns ( [
				'status',
				'project',
				'versionCode',
				'COUNT(record_id) AS count'
		] )
			->groupBy ( [
				'status',
				'project',
				'versionCode'
		] )
			->getQuery ();
		$debug_records = $query->execute ( array (
				'status' => 'debug'
		) );
		$release_records = $query->execute ( array (
				'status' => 'release'
		) );
		$view = $this->view;
		$view->setVar ( 'title', '/' );
		$view->setVar ( 'debug_records', $debug_records );
		$view->setVar ( 'release_records', $release_records );
		$view->pick ( 'view/by-all' );
	}

	public function showRecentAction() {
		$view = $this->view;
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->orderBy ( 'record_id desc' )
			->limit ( 20 );
		$query = $builder->getQuery ();
		$view->setVar ( 'records', $query->execute () );
		$view->pick ( 'view/by-recent' );
	}

	public function showByStackMD5Action() {
		$stack_md5 = $this->dispatcher->getParam ( 'stack_md5' );
	}

	public function showArchieveAction() {
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->where ( 'archieve is not null' )
			->columns ( [
				'project',
				'archieve',
				'COUNT(archieve) as count'
		] )
			->groupBy ( [
				'project',
				'archieve'
		] )
			->orderBy ( 'archieve desc,count desc' );
		$query = $builder->getQuery ();
		$result = $query->execute ();
		$view = $this->view;
		$view->setVar ( 'title', '历史归档' );
		$view->setVar ( 'records', $result );
		$view->pick ( 'view/archieve' );
	}
}
