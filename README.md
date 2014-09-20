MagentoTarToConnect
===================

A small shell script to automatically package tar archives into Magento's Connect 2.0 format. 

### Description

Under the hood Magento Connect 2.0 packages (Magento Connect 2.0 was introduced around the time of Magento CE 1.5) are actually tar'ed and gziped files with a specially formatted package manifest.  Well, they're almost `tar` and `gzip` files.  Magento implemented their own archiving and unarchiving code in PHP, and this code occasionally has problems with tar archives created via standard OS tools. 

This shell script will take a standard tar archive, untar it, build the Connect `package.xml` manifest, and then re-tar and gzip the files **using Magento's code** (included in the `vendor` library here, but you can substitute your own).  This decreases the likelihood your package will be incompatible with Magento Connect. 

## Usage

The syntax for using this script is as following

    $ ./magento-tar-to-connect.php example-config.php
    
Where `example-config.php` is a PHP file which returns a set of configuration key/value pairs.  These key/value pairs provide the script with the location of an archive file, the output location, as well as the bare minimum Magento Connect fields needed to create a valid extension.  An example file might look something like this

    <?php
    return array(
    
    //The base_dir and archive_file path are combined to point to your tar archive
    //The basic idea is a separate process builds the tar file, then this finds it
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
    'summary'                => 'Provides navigation shortcuts for the admin console\'s navigation and global search',
    'description'            => 'This extension provides Magento admin console users with an "application launcher". This application launcher provides instant access to the admin console\'s navigation, every system configuration search section, as well as the Magento global search.  The Pulse Storm launcher is a free, open source, must have extension for anyone working with Magento. ',
    'notes'                  => 'Typo fixes, properly aborts ajax requests.',
    
    //Magento Connect author information. If author_email is foo@example.com, script will
    //prompt you for the correct name.  Should match your http://www.magentocommerce.com/
    //login email address
    'author_name'            => 'Alan Storm',
    'author_user'            => 'alanstorm',
    'author_email'           => 'foo@example.com',
    
    //PHP min/max fields for Connect.  I don't know if anyone uses these, but you should
    //probably check that they're accurate
    'php_min'                => '5.2.0',
    'php_max'                => '6.0.0'
    );

## Building a `phar` with Phing

The project also includes a `phing` build.xml file.  You can use this to create an executable `phar` of the script.  If you're not familiar, a `phar` is sort of like a stand alone PHP executable. You can [read more here](http://php.net/phar).

    $ phing create_phar
    
    
