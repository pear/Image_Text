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
	
	$version = '0.5.1beta';
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
 * Fixed bug #1207 supporting old versions - fix included.
 * Updated default color array regarding to bug #1203: mega bug summary / with some wishes.
 * Added more docs for "font_path" and "font_file" regarding bug #1203: mega bug summary / with some wishes.
 * Moved options docs to options array.
 * Added simple example to the top.
 * Fixed save() method accoriding to bug #1203: mega bug summary / with some wishes.
 * Added construct() method according to bug #1203 [Opn->Asn]: mega bug summary / with some wishes.
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
