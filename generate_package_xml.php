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
		      'description' => 'Image_Text enables you to deal more comfortable with texts inside GD 2 based images.
		                    Create text boxes inside your images, rotate them and let the class align your text
		                    inside it in horizontal and vertical directions. Image_Text can although determine
		                    the best font-size for a given text box.',
		      'version' => '0.4',
	          'packagedirectory' => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state' => 'alpha',
              'filelistgenerator' => 'cvs',
              'notes' => "Image_Text experienced extensive debugging and fixing, as well as small adjustments. The standard value
                          for line_spacing changed to 0.5 but this does not matter for the standard output, since the calculation
                          has changed.
              
                          The behavior of setColor() and setColors() has changed a bit to allow a new color format which defines
                          RGB values through an array keyed with 0, 1 and 2 plus optionally 3 as the alpha value.
              
                          This release should work quite fine, since it has been extensively debugged. But since I plan to make some
                          more improvements I can not promise a 100% stable API. The API should be fixed for about 95% now.",
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
