<?php

	$make = true;

	echo ini_get('include_path');

	require_once('PEAR/PackageFileManager.php');

	$pkg = new PEAR_PackageFileManager;

	// directory that PEAR CVS is located in
	$cvsdir  = '/cvs/pear/';
	$packagedir = $cvsdir . 'Image_Text/';
	$category = 'Image';	
	
	$e = $pkg->setOptions(
		array('baseinstalldir' => '',
		      'version' => '0.3',
	          'packagedirectory' => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state' => 'alpha',
              'filelistgenerator' => 'cvs',
              'notes' => "Implements heavy perfomance improvements (thanks Pierre!). Following his recommendation I dropped the
                          Line class. Text tokens are now simply stored in an array. Rendering works more cleanly now in respect
                          to line spacing. The API has changed due to refactoring. Text rotation now works fine.",
			  'package' => 'Image_Text',
			  'dir_roles' => array(
			  		'doc' => 'doc',
			  		'example' => 'doc'),
		      'ignore' => array('package.xml'),
	));
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
		
	$e = $pkg->addDependency('gd', '2', 'has', 'ext');
	$e = $pkg->addDependency('Image_Tools', '0.2', 'has', 'pkg');
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
	
	// hack until they get their shit in line with docroot role
	$pkg->addRole('tpl', 'php');
	$pkg->addRole('png', 'php');
	$pkg->addRole('gif', 'php');
	$pkg->addRole('jpg', 'php');
	$pkg->addRole('css', 'php');
	$pkg->addRole('js', 'php');
	$pkg->addRole('ini', 'php');
	$pkg->addRole('inc', 'php');
	$pkg->addRole('afm', 'php');
	$pkg->addRole('pkg', 'doc');
	$pkg->addRole('cls', 'doc');
	$pkg->addRole('proc', 'doc');
	$pkg->addRole('sh', 'script');
	
	if (isset($make)) {
    	$e = $pkg->writePackageFile();
	} else {
    	$e = $pkg->debugPackageFile();
	}
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
	}
	
	if (!isset($make)) {
    	echo '<a href="' . $_SERVER['PHP_SELF'] . '?make=1">Make this file</a>';
	}
?>