#!/usr/bin/php
<?php

    $make = 1;
	require_once('PEAR/PackageFileManager.php');

	$pkg = new PEAR_PackageFileManager;

	// directory that PEAR CVS is located in
	$cvsdir  = '/cvs/pear/';
	$packagedir = $cvsdir . 'Image_Text/';
	
	// Filemanager settings
	$category = 'Image';
	$package = 'Image_Text';
	
	$version = '0.5.2beta2';
	$state = 'beta';
	
	$summary = 'Image_Text - Advanced text maipulations in images.';
	$description = <<<EOT
Image_Text provides a comfortable interface to text manipulations in GD
images. Beside common Freetype2 functionality it offers to handle texts
in a graphic- or office-tool like way. For example it allows alignment of
texts inside a text box, rotation (around the top left corner of a text
box or it's center point) and the automatic measurizement of the optimal
font size for a given text box.
EOT;

	$notes = <<<EOT
 * Fixed bug 2265: Init a heigth without a canvas.
EOT;
	
	$e = $pkg->setOptions(
		array('simpleoutput'      => true,
		      'baseinstalldir'    => '',
		      'summary'           => $summary,
		      'description'       => $description,
		      'version'           => $version,
	          'packagedirectory'  => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state'             => $state,
              'filelistgenerator' => 'cvs',
              'notes'             => $notes,
			  'package'           => $package,
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
	$pkg->addRole('sh', 'doc');
	
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
