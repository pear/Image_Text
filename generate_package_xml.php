<?php

	$make = false;
	require_once('PEAR/PackageFileManager.php');

	$pkg = new PEAR_PackageFileManager;

	// directory that PEAR CVS is located in
	$cvsdir  = '/cvs/pear/';
	$packagedir = $cvsdir . 'Image_Text/';
	$category = 'Image';	
	
	$e = $pkg->setOptions(
		array('baseinstalldir' => '',
		      'summary' => 'Image_Text - Advanced text maipulations in images',
		      'description' => 's the functionality and features are currently fixed and the API is frozen, I decided to 
                          roll the first beta release. Thsi should give a) feedback and b) stability due to (hopefully)
                          more test.',
		      'version' => '0.5.0',
	          'packagedirectory' => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state' => 'beta',
              'filelistgenerator' => 'cvs',
              'notes' => "First beta release. The API is fixed (except possible feature additions) by now. Please test this
                         release extensively to improve stability.",
			  'package' => 'Image_Text',
			  'dir_roles' => array(
			  		'example' => 'doc'),
		      'ignore' => array('package.xml',
		                        'doc*', 
		                        'generate_package_xml.php',
		                        '*.tgz'),
	));
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
	
	$e = $pkg->addMaintainer('toby', 'lead', 'Tobias Schlitt', 'toby@php.net');
	
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
		
	$e = $pkg->addDependency('gd', '2', 'has', 'ext');
	
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
