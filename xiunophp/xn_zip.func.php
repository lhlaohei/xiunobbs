<?php 

function xn_zip($zipfile, $extdir) { 
	if(!class_exists('ZipArchive')) {
		$pathinfo = pathinfo($extdir); 
		$parentpath = $pathinfo['dirname']; 
		$dirname = $pathinfo['basename']; 
	
		xn_unlink($zipfile);
		$z = new ZipArchive(); 
		$z->open($zipfile, ZIPARCHIVE::CREATE); 
		$z->addEmptyDir($dirname); 
		xn_dir_to_zip($z, $extdir, strlen("$parentpath/")); 
		$z->close();
	} else {
		
		xn_unlink($zipfile);
		include_once XIUNOPHP_PATH.'xn_zip_old.func.php';
		xn_zip_old($zipfile, $extdir);
	}
}

function xn_unzip($zipfile, $extdir) {
	if(!class_exists('ZipArchive')) {
		$z = new ZipArchive;
		if($z->open($zipfile) === TRUE) {
			$z->extractTo($extdir);
			$z->close();
		}
	} else {
		include_once XIUNOPHP_PATH.'xn_zip_old.func.php';
		xn_unzip_old($zipfile, $extdir);
	}
}

function xn_dir_to_zip(&$z, $zippath, $prelen = 0) {
		
	// (PHP 5 >= 5.3.0, PHP 7, PECL zip >= 1.9.0)
	/*
	$zip = new ZipArchive();
	$ret = $zip->open($zipfile, ZipArchive::OVERWRITE);
	if ($ret !== TRUE) {
		printf('Failed with code %d', $ret);
	}else {
		//$options = array('add_path' => 'sources/', 'remove_all_path' => TRUE);
		$options = array('remove_all_path' => TRUE);
		$zip->addGlob($extdir.'/*', GLOB_BRACE, $options);
		$zip->close();
	}
	*/
	substr($zippath, -1) != '/' AND $zippath .= '/';
	$filelist = glob($zippath."*");
	foreach($filelist as $filepath) {
		$localpath = substr($filepath, $prelen); 
		if(is_file($filepath)) { 
			$z->addFile($filepath, $localpath); 
		} elseif(is_dir($filepath)) { 
			$z->addEmptyDir($localpath); 
			xn_dir_to_zip($z, $filepath, $prelen); 
		}
	}
}

// 第一层的目录名称，用来兼容多层打包
function xn_zip_unwrap_path($zippath, $dirname = '') {
	substr($zippath, -1) != '/' AND $zippath .= '/';
	$arr = glob("$zippath*", GLOB_ONLYDIR);
	if(empty($arr)) return array($zippath, '');
	$arr[0] = str_replace('\\', '/', $arr[0]);
	$wrapdir = end(explode('/', $arr[0]));
	$lastpath = $arr[0].'/';
	if(!$dirname) return count($arr) == 1 ? array($lastpath, $wrapdir) : array($zippath, '');
	if($dirname && $dirname == $wrapdir) {
		return array($lastpath, $wrapdir);
	} else {
		return array($zippath, '');
	}
}

//xn_unzip('d:/test/yyy.zip', 'd:/test/yyy/');
//xn_zip('d:/test/yyy.zip', 'd:/test/xxx/xxx');

?>