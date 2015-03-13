<?php

class CrashRecordTask extends \Phalcon\CLI\Task {

	public function mainAction() {
	}

	public function afterUploadAction($param = array()) {
		$record = \CrashRecords::findFirst ( $param [0] );
		$redump = isset ( $param [1] ) && $param [1];
		if (! $record)
			return;
		$record->proc = 'GENERATING_PLEASE_WAIT';
		$record->save ();
		$base_file = '/libgame.so';
		$so_dir = \Storage::getDir ( '/so/' . $record->project . '/' . $record->versionCode, true );
		$so_file = $so_dir . $base_file;
		$symbols_dir = \Storage::getDir ( '/symbols', true );
		if (! file_exists ( $so_file )) {
			// 文件不存在 去SVN进行更新
			// $svn = $this->getDI()->get('svn');
			DumpProcessor::downloadSo ( $record->project, $record->versionCode );
			if (! file_exists ( $so_file )) {
				$record->proc = 'MISSING_SO_FILE';
				$record->save ();
				echo '生成堆栈失败' . $record->proc . PHP_EOL;
				return;
			}
		}
		
		$tmp_dir = \Storage::getDir ( '/tmp', true );
		
		$sym_mark = $so_file . '.mark';
		$info = array ();
		if (! file_exists ( $sym_mark )) {
			$ret = \DumpProcessor::genSymbol ( $so_file, $symbols_dir, $tmp_dir, true, $info );
			if ($ret) {
				$record->proc = $info ['error'];
				$record->save ();
				echo '生成堆栈失败' . $record->proc . PHP_EOL;
				return;
			}
		}
		
		if (! file_exists ( $sym_mark )) {
			$record->proc = 'MISSING_SYM_MARK';
			$record->save ();
			echo '生成堆栈失败 ' . $record->proc . PHP_EOL;
			return;
		}
		
		$module_info = preg_split ( '[\s]', file_get_contents ( $sym_mark ) );
		$module_info ['platform'] = $module_info [1];
		$module_info ['architecture'] = $module_info [2];
		$module_info ['hash'] = $module_info [3];
		$module_info ['module'] = $module_info [4];
		
		$sym_dir = \Storage::getDir ( '/symbols/' . $module_info ['module'] . '/' . $module_info ['hash'], true );
		
		$sym_file = $sym_dir . '/' . $module_info ['module'] . '.sym';
		
		// no sym file, error
		if (! file_exists ( $sym_file )) {
			// 重新生成
			$this->genSymbolAction ( array (
					$record->project,
					$record->versionCode
			) );
			if (! file_exists ( $sym_file )) {
				$record->proc = 'MISSING_SYM_FILE';
				$record->save ();
				echo '生成堆栈失败 ' . $record->proc . PHP_EOL;
				return;
			}
		}
		$dump_dir = \Storage::getDir ( '/dump/' . $record->project . '/' . $record->versionCode, true );
		$dump_file = $dump_dir . '/' . $record->file_name;
		
		$ext = pathinfo ( $dump_file, PATHINFO_EXTENSION );
		$dump_name = pathinfo ( $dump_file, PATHINFO_FILENAME );
		$zip_file = $dump_dir . '/' . $dump_name . '.gz';
		$tmp_file = $dump_dir . '/' . $dump_name;
		// 重命名
		if ($ext == 'zip') {
			// 压缩的文件
			if (copy ( $dump_file, $zip_file )) {
				exec ( 'gzip -d ' . $zip_file );
				if (! file_exists ( $dump_dir . '/' . $dump_name )) {
					$record->proc = 'ERROR_UNCOMPRESSING';
					$record->save ();
					@unlink ( $zip_file );
					return;
				}
				// @unlink($zip_file);
				// 复制
				$zip_file = $dump_dir . '/' . $dump_name . '.dmp';
				if (copy ( $tmp_file, $zip_file )) {
					@unlink ( $dump_file );
					$dump_file = $zip_file;
					@unlink ( $tmp_file );
					$record->file_name = $dump_name . '.dmp';
					$record->save ();
				} else {
					$record->proc = 'ERROR_WHEN_MOVE_DMP_FILE';
					$record->save ();
					@unlink ( $tmp_file );
					return;
				}
			} else {
				$record->proc = 'ERROR_WHEN_MOVE_ZIP_FILE';
				$record->save ();
				return;
			}
		}
		
		$stack_dir = \Storage::getDir ( '/stack/' . $record->project . '/' . $record->versionCode, true );
		
		$stack_mark = $dump_file . '.mark';
		$info = array ();
		if (! file_exists ( $stack_mark ) || $redump) {
			$ret = \DumpProcessor::genStack ( $symbols_dir, $dump_file, $stack_dir, $tmp_dir, TRUE, $info );
			if ($ret) {
				$record->proc = $info ['error'];
				$record->save ();
				echo '生成堆栈失败 ' . $record->proc . PHP_EOL;
				return;
			}
		} else {
			$md5 = file_get_contents ( $stack_mark );
			$stack_file = $stack_dir . '/' . $md5 . '.stack';
			if (! file_exists ( $stack_file )) {
				$record->proc = 'MISSING_STACK_FILE';
				$record->save ();
				echo '生成堆栈失败 ' . $record->proc . PHP_EOL;
				return;
			}
			$info ['md5'] = $md5;
			$info ['stack_file'] = $stack_file;
		}
		$record->proc = $info ['md5'];
		$record->save ();
		echo '生成堆栈成功 ' . $info ['stack_file'] . PHP_EOL;
	}

	public function genSymbolAction($params = array()) {
		$project = $params [0];
		$versionCode = $params [1];
		$so_dir = \Storage::getDir ( '/so/' . $project . '/' . $versionCode, true );
		$so_file = $so_dir . '/libgame.so';
		$tmp_dir = \Storage::getDir ( '/tmp', TRUE );
		$symbols_dir = \Storage::getDir ( '/symbols', TRUE );
		$info = array ();
		$info ['sym_file'] = '[待定]';
		$ret = \DumpProcessor::genSymbol ( $so_file, $symbols_dir, $tmp_dir, FALSE, $info );
		if ($ret) {
			echo '无法生成符号文件：' . $info ['error'] . PHP_EOL;
		} else {
			echo '成功为' . $so_file . '生成符号文件' . $info ['sym_file'] . PHP_EOL;
		}
	}

	public function backupDumpAction(array $params = array()) {
		$project = isset ( $params [0] ) ? $params [0] : false;
		if ($project === false) {
			// $this->modelsManager->createBuilder()->from('CrashRecords')->columns('project')->distinct('distinct')->
			
			$builder = $this->modelsManager->createBuilder ()
				->from ( 'CrashRecords' )
				->groupBy ( 'project' )
				->columns ( 'project' );
			$query = $builder->getQuery ();
			
			$results = $query->execute ();
			
			foreach ( $results as $result ) {
				$this->backupDumpAction ( array (
						$result->project
				) );
			}
			return;
		}
		$dateTime = new DateTime ();
		$dateTime->setTime ( 17, 00, 00 );
		$dateTime->sub ( new DateInterval ( 'P1D' ) );
		// echo $dateTime->format('Y-m-d H:i:s');
		$builder = $this->modelsManager->createBuilder ();
		$builder->from ( 'CrashRecords' )
			->where ( 'time_stamp < :time_stamp:' )
			->andWhere ( 'project = :project:' )
			->andWhere ( 'archieve is null' );
		$query = $builder->getQuery ();
		$results = $query->execute ( array (
				'project' => $project,
				'time_stamp' => $dateTime->format ( 'Y-m-d H:i:s' )
		) );
		$archieve_name = $dateTime->format ( 'Y-m-d' );
		$tmpdir = "/tmp/archieve/" . $archieve_name . '.' . $project;
		$desc_file = \Storage::getFile ( $tmpdir . '/' . $project . '/desc.txt' );
		@file_put_contents ( $desc_file, '' );
		$src_files = array ();
		$count = 0;
		foreach ( $results as $result ) {
			$file_dir = "/$result->project/$result->versionCode";
			$file_file = "$file_dir/$result->file_name";
			\Storage::getDir ( $tmpdir . $file_dir, true );
			$src_file = \Storage::getFile ( "/dump/" . $file_file );
			$dst_file = \Storage::getFile ( $tmpdir . $file_file );
			// 记录拷贝的文件
			$src_files [] = $src_file;
			$src_files [] = $src_file . '.mark';
			// 复制文件
			$count += @copy ( $src_file, $dst_file );
			$count += @copy ( $src_file . '.mark', $dst_file . '.mark' );
			// 更新状态
			$result->archieve = $archieve_name;
			$result->save ();
			// 更新相同DMP状态
			$same_dumps = CrashRecords::find ( array (
					array (
							'project' => $project,
							'versionCode' => $result->versionCode,
							'file_name' => $result->file_name
					)
			) );
			
			foreach ( $same_dumps as $dump ) {
				if (isset ( $dump->archieve ) && ! empty ( $dump->archieve ))
					continue;
				$dump->archieve = $archieve_name;
				$dump->save ();
				// 记录摘要
				file_put_contents ( $desc_file, json_encode ( $dump->toArray () ) . PHP_EOL, FILE_APPEND );
			}
			// 记录摘要
			//file_put_contents ( $desc_file, json_encode ( $result->toArray () ) . PHP_EOL, FILE_APPEND );
		}
		//
		if ($count < 1)
			return;
			// 删除源文件
		$cmd = "rm -f " . implode ( ' ', $src_files );
		exec ( $cmd );
		// 删除空目录
		$dump_dir = \Storage::getDir ( '/dump' );
		foreach ( $src_files as $file ) {
			$dir = dirname ( $file );
			while ( strpos ( $dir, $dump_dir ) !== false && is_dir ( $dir ) ) {
				@rmdir ( $dir );
				$dir = dirname ( $dir );
			}
		}
		// echo $cwd = getcwd ();
		chdir ( dirname ( $desc_file ) );
		
		$arc_dir = \Storage::getDir ( '/archieve', true );
		$arc_file = $arc_dir . '/' . $archieve_name . '.' . $project . '.tar.gz';
		
		// 备份旧的achievefile
		\Utils::backUp ( $arc_file );
		// 打包
		$cmd = 'tar -acf ' . $arc_file . ' * ';
		exec ( $cmd );
		
		chdir ( APPLICATION_PATH );
		// unlink(\Storage::getDir($tmpdir));
		// 删除临时目录
		exec ( 'rm -rf ' . \Storage::getDir ( $tmpdir ) );
	}
}
