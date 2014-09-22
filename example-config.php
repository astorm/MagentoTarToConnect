<?php
return array(

//The base_dir and archive_file path are combined to point to your tar archive
//The basic idea is a seperate process builds the tar file, then this finds it
'base_dir'               => '/fakehome/Documents/github/Pulsestorm/var/build',
'archive_files'          => 'Pulstorm_Example.tar',

//The Magento Connect extension name.  Must be unique on Magento Connect
//Has no relation to your code module name.  Will be the Connect extension name
'extension_name'         => 'Pulstorm_Example',

//Your extension version.  By default, if you're creating an extension from a 
//single Magento module, the tar-to-connect script will look to make sure this
//matches the module version.  You can skip this check by setting the 
//skip_version_compare value to true
'extension_version'      => '1.0.3',
'skip_version_compare'   => false,

//You can also have the package script use the version in the module you 
//are packaging with. 
'auto_detect_version'   => false,

//Where on your local system you'd like to build the files to
'path_output'            => '/fakehome/Pulsestorm/var/build-connect',

//Magento Connect license value. 
'stability'              => 'stable',

//Magento Connect license value 
'license'                => 'MIT',

//Magento Connect channel value.  This should almost always (always?) be community
'channel'                => 'community',

//Magento Connect information fields.
'summary'                => 'Provides navigation shortcuts for the admin console\'s navigation and gloal search',
'description'            => 'This extension provides Magento admin console users with an "application launcher". This application launcher provides instant access to the admin console\'s navigation, every system configuration search section, as well as the Magento global search.  The Pulse Storm launcher is a free, open source, must have extension for anyone working with Magento. ',
'notes'                  => 'Typo fixes, properly aborts ajax requests.',

//Magento Connect author information. If author_email is foo@example.com, script will
//prompt you for the correct name.  Should match your http://www.magentocommerce.com/
//login email address
'author_name'            => 'Alan Storm',
'author_user'            => 'alanstorm',
'author_email'           => 'foo@example.com',

// Optional: adds additional author nodes to package.xml
'additional_authors'     => array(
  array(
    'author_name'        => 'Mike West',
    'author_user'        => 'micwest',
    'author_email'       => 'foo2@example.com',
  ),
  array(
    'author_name'        => 'Reggie Gabriel',
    'author_user'        => 'rgabriel',
    'author_email'       => 'foo3@example.com',
  ),
),

//PHP min/max fields for Connect.  I don't know if anyone uses these, but you should
//probably check that they're accurate
'php_min'                => '5.2.0',
'php_max'                => '6.0.0',

//PHP extension dependencies. An array containing one or more of either:
//  - a single string (the name of the extension dependency); use this if the
//    extension version does not matter
//  - an associative array with 'name', 'min', and 'max' keys which correspond
//    to the extension's name and min/max required versions
//Example:
//    array('json', array('name' => 'mongo', 'min' => '1.3.0', 'max' => '1.4.0'))
'extensions'             => array()
);
