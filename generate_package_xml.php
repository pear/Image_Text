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
		      'version' => '0.2',
	          'packagedirectory' => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state' => 'alpha',
              'filelistgenerator' => 'cvs',
              'notes' => "Implements the new Image_Tools package structure and fixes
              			  the following bugs:
              			  176, Antialiasing done wrong
              			  177, correction for Image_Text-constructor
              			  178, error in comment-block
              			  179, textsize and position
              			  180, Antialiasing-setting don't get through
              			  188, Typo in Image_Text.php
              			  189, Methods called as class variables",
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
