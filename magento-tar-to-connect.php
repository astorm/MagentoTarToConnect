<?php
// Copyright (c) 2012 - 2014 Pulse Storm LLC.
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

//in progress, use at your own risk
if (!defined('DS')) define('DS','/');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
date_default_timezone_set('America/Los_Angeles');

require_once dirname(__FILE__) . '/'. 'src/magento/downloader/lib/Mage/Archive/Helper/File.php';
require_once dirname(__FILE__) . '/'. 'src/magento/downloader/lib/Mage/Archive/Interface.php';
require_once dirname(__FILE__) . '/'. 'src/magento/downloader/lib/Mage/Archive/Abstract.php';
require_once dirname(__FILE__) . '/'. 'src/magento/downloader/lib/Mage/Archive/Tar.php';
require_once dirname(__FILE__) . '/'. 'src/magento/downloader/lib/Mage/Exception.php';

/**
* Still a lot of Magento users stuck on systems with 5.2, no no namespaces
* @todo but we're using anonymous functions below, so this won't work with
*       5.2 -- do we want this as a class, or a single file namespaced module?
*/
class Pulsestorm_MagentoTarToConnect
{
    static public $verbose=true;
    //from http://php.net/glob
    // Does not support flag GLOB_BRACE    
    static public function globRecursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);        
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, self::globRecursive($dir.'/'.basename($pattern), $flags));
        }        
        return $files;
    }
    
    static public function input($string)
    {
        self::output($string);
        self::output('] ','');
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);        
        fclose($handle);
        return $line;
    }
    
    static public function output($string, $newline="\n")
    {
        if(!self::$verbose)
        {
            return;
        }
        echo $string,$newline;
    }
    
    static public function error($string)
    {
        self::output("ERROR: " . $string);
        self::output("Execution halted at " . __FILE__ . '::' . __LINE__);
        exit;
    }
    
    
    static public function createPackageXmlAddNode($xml, $full_dir, $base_dir=false)
    {
        $parts = explode("/",str_replace($base_dir.'/','',$full_dir));        
        $single_file  = array_pop($parts);
        $node = $xml;                
        foreach($parts as $part)
        {
            $nodes = $node->xpath("dir[@name='".$part."']");
            if(count($nodes) > 0)
            {
                $node = array_pop($nodes);
            }
            else
            {
                $node = $node->addChild('dir');
                $node->addAttribute('name', $part);
            }                                    
        }    
        
        $node = $node->addChild('file');
        $node->addAttribute('name',$single_file);
        $node->addAttribute('hash',md5_file($full_dir));
    }
            
    static public function createPackageXml($files, $base_dir, $config)
    {
        $xml = simplexml_load_string('<package/>');    
        $xml->name          = $config['extension_name'];
        $xml->version       = $config['extension_version'];
        $xml->stability     = $config['stability'];
        $xml->license       = $config['license'];
        $xml->channel       = $config['channel'];
        $xml->extends       = '';
        $xml->summary       = $config['summary'];
        $xml->description   = $config['description'];
        $xml->notes         = $config['notes'];
        
        $authors            = $xml->addChild('authors');
        foreach (self::getAuthorData($config) as $oneAuthor) {
            $author         = $authors->addChild('author');
            $author->name   = $oneAuthor['author_name'];
            $author->user   = $oneAuthor['author_user'];
            $author->email  = $oneAuthor['author_email'];
        }
        
        $xml->date          = date('Y-m-d');
        $xml->time          = date('G:i:s');
        $xml->compatible    = '';
        $dependencies       = $xml->addChild('dependencies');
        $required           = $dependencies->addChild('required');
        $php                = $required->addChild('php');
        $php->min           = $config['php_min'];   //'5.2.0';
        $php->max           = $config['php_max'];   //'6.0.0';
    
        // add php extension dependencies
        if (is_array($config['extensions'])) {
            foreach ($config['extensions'] as $extinfo) {
                $extension = $required->addChild('extension');
                if (is_array($extinfo)) {
                    $extension->name = $extinfo['name'];
                    $extension->min = isset($extinfo['min']) ? $extinfo['min'] : "";
                    $extension->max = isset($extinfo['max']) ? $extinfo['max'] : "";
                } else {
                    $extension->name = $extinfo;
                    $extension->min = "";
                    $extension->max = "";
                }
            }
        }
    
        $node = $xml->addChild('contents');
        $node = $node->addChild('target');
        $node->addAttribute('name', 'mage');
        
        //     $files = $this->recursiveGlob($temp_dir);
        //     $files = array_unique($files);              
        $temp_dir = false;
        foreach($files as $file)
        {
            //$this->addFileNode($node,$temp_dir,$file);
            self::createPackageXmlAddNode($node, $file, $base_dir);
        }                
        //file_put_contents($temp_dir . '/package.xml', $xml->asXml());            
        
        return $xml->asXml();
    }
    
    static public function getTempDir()
    {
        $name = tempnam(sys_get_temp_dir(),'tmp');
        unlink($name);
        $name = $name;			
        mkdir($name,0777,true);
        return $name;
    }
    
    static public function validateConfig($config)
    {
        $keys = array('base_dir','archive_files','path_output',
        );
        foreach($keys as $key)
        {
            if(!array_key_exists($key, $config))
            {
                self::error("Config file missing key [$key]");
            }
        }
        
        if($config['author_email'] == 'foo@example.com')
        {
            $email = self::input("Email Address is configured with foo@example.com.  Enter a new address");
            if(trim($email) != '')
            {
                $config['author_email'] = trim($email);
            }
        }
        
        if(!array_key_exists('extensions', $config))
        {
            $config['extensions'] = null;
        }
        return $config;
        
        
    }
    
    static public function loadConfig($config_name=false)
    {
        if(!$config_name)
        {
            $config_name = basename(__FILE__,'php') . 'config.php';
        }
        if(!file_exists($config_name))
        {
            self::error("Could not find $config_name.  Create this file, or pass in an alternate");
        }
        $config = include $config_name;
                
        $config = self::validateConfig($config);
        return $config;
    }
    
    static public function getModuleVersion($files)
    {
        $configs = array();
        foreach($files as $file)
        {
            if(basename($file) == 'config.xml')
            {
                $configs[] = $file;
            }
        }
        
        foreach($configs as $file)
        {
            $xml = simplexml_load_file($file);
            $version_strings = $xml->xpath('//version');
            foreach($version_strings as $version)
            {
                $version = (string) $version;
                if(!empty($version)) 
                {
                    return (string)$version;
                }
            }
        }
        
        foreach($configs as $file)
        {
            $xml = simplexml_load_file($file);
            $modules = $xml->xpath('//modules');
            foreach($modules[0] as $module)
            {
                $version = (string)$module->version;
                if(!empty($version))
                {
                    return $version;
                }
            }
        }
    }
    
    static public function checkModuleVersionVsPackageVersion($files, $extension_version)
    {
        $configs = array();
        foreach($files as $file)
        {
            if(basename($file) == 'config.xml')
            {
                $configs[] = $file;
            }
        }
        
        foreach($configs as $file)
        {
            $xml = simplexml_load_file($file);
            $version_strings = $xml->xpath('//version');
            foreach($version_strings as $version)
            {
                if($version != $extension_version)
                {
                    self::error(
                        "Extension Version [$extension_version] does not match " .
                        "module version [$version] found in a config.xml file.  Add " .
                        "'skip_version_compare'   => true  to configuration to skip this check."
                    );
                }
            }
        }
    }
    
    static public function buildExtensionFromConfig($config)
    {
        ob_start();
        
        # extract and validate config values
        $base_dir           = $config['base_dir'];          //'/Users/alanstorm/Documents/github/Pulsestorm/var/build';
        if($base_dir['0'] !== '/')
        {
            $base_dir = getcwd() . '/' . $base_dir;
        }                        
        $archive_files      = $config['archive_files'];     //'Pulsestorm_Modulelist.tar';    
        $path_output        = $config['path_output'];       //'/Users/alanstorm/Desktop/working';    
        $archive_connect    = $config['extension_name'] . '-' . $config['extension_version'] . '.tgz';
        ###--------------------------------------------------
        
        # make sure the archive we're creating exists
        if(!file_exists($base_dir . '/' . $archive_files))
        {
            self::error('Can\'t find specified archive, bailing' . "\n[" . $base_dir . '/' . $archive_files.']');
            exit;
        }
        ###--------------------------------------------------
        
        # create a temporary directory, move to temporary
        $temp_dir   = self::getTempDir();        
        chdir($temp_dir);
        ###--------------------------------------------------
        
        # copy and extract archive               
        shell_exec('cp '        . $base_dir . '/' . $archive_files . ' ' . $temp_dir);
        if(preg_match('/\.zip$/', $archive_files)) {
            shell_exec('unzip -o '  . $temp_dir . '/' . $archive_files);
        } else {
            shell_exec('tar -xvf '  . $temp_dir . '/' . $archive_files);
        }
        shell_exec('rm '        . $temp_dir . '/' . $archive_files);
        ###--------------------------------------------------
        
        # get a lsit of all the files without directories
        $all        = self::globRecursive($temp_dir  . '/*');
        $dirs       = self::globRecursive($temp_dir .'/*',GLOB_ONLYDIR);
        $files      = array_diff($all, $dirs);
        ###--------------------------------------------------
        
        # now that we've extracted the files, yoink the version number from the config
        # this only works is auto_detect_version is true. Also, may not return what
        # you expect if your connect extension includes multiple Magento modules
        if(isset($config['auto_detect_version']) && $config['auto_detect_version'] == true)
        {
            $config['extension_version'] = self::getModuleVersion($files);
            $archive_connect = $config['extension_name'] . '-' . $config['extension_version'] . '.tgz';
        }
        ###--------------------------------------------------
        
        # checks that your Magento Connect extension version matches the version of your 
        # modules file.  Probably redundant if auto_detect_version is true
        if(!$config['skip_version_compare'])
        {
            self::checkModuleVersionVsPackageVersion($files, $config['extension_version']);
        }
        ###--------------------------------------------------
        
        # creates the base extension package.xml file            
        $xml        = self::createPackageXml($files,$temp_dir,$config);        
        file_put_contents($temp_dir . '/package.xml',$xml);    
        self::output($temp_dir);
        ###--------------------------------------------------
        
        # create the base output folder if it doesn't exist
        if(!is_dir($path_output))
        {
            mkdir($path_output, 0777, true);
        }
        ###--------------------------------------------------
        
        # use Magento architve to tar up the files
        $archiver = new Mage_Archive_Tar;
        $archiver->pack($temp_dir,$path_output.'/'.$archive_files,true);
        ###--------------------------------------------------
        
        # zip up the archive
        shell_exec('gzip '  . $path_output . '/' . $archive_files);
        shell_exec('mv '    . $path_output . '/' . $archive_files.'.gz '.$path_output.'/' . $archive_connect);
        ###--------------------------------------------------
        
        # Creating extension xml for connect using the extension name
        self::createExtensionXml($files, $config, $temp_dir, $path_output);        
        ###--------------------------------------------------
        
        # Report on what we did
        self::output('');
        self::output('Build Complete');
        self::output('--------------------------------------------------');
        self::output( "Built tgz in $path_output\n");
        
        self::output( 
        "Built XML for Connect Manager in" . "\n\n" .
    
        "   $path_output/var/connect " . "\n\n" .
    
        "place in `/path/to/magento/var/connect to load extension in Connect Manager");    
        
        ###--------------------------------------------------
        
        return ob_get_clean();
    }
    
    static public function main($argv)
    {
        $this_script = array_shift($argv);
        $config_file = array_shift($argv);    
        $config = self::loadConfig($config_file);
                
        self::output(
            self::buildExtensionFromConfig($config)
        );
        
    }
    /**
     * extrapolate the target module using the file absolute path
     * @param  string $filePath
     * @return string
     */
    static public function extractTarget($filePath)
    {
        foreach (self::getTargetMap() as $tMap) {
            $pattern = '#' . $tMap['path'] . '#';
            if (preg_match($pattern, $filePath)) {
                return $tMap['target'];
            }
        }
        return 'mage';
    }
    /**
     * get target map
     * @return array
     */
    static public function getTargetMap()
    {
        return array(
            array('path' => 'app/etc', 'target' => 'mageetc'),
            array('path' => 'app/code/local', 'target' => 'magelocal'),
            array('path' => 'app/code/community', 'target' => 'magecommunity'),
            array('path' => 'app/code/core', 'target' => 'magecore'),
            array('path' => 'app/design', 'target' => 'magedesign'),
            array('path' => 'lib', 'target' => 'magelib'),
            array('path' => 'app/locale', 'target' => 'magelocale'),
            array('path' => 'media/', 'target' => 'magemedia'),
            array('path' => 'skin/', 'target' => 'mageskin'),
            array('path' => 'http://', 'target' => 'mageweb'),
            array('path' => 'https://', 'target' => 'mageweb'),
            array('path' => 'Test/', 'target' => 'magetest'),
        );
    }
    static public function createExtensionXml($files, $config, $tempDir, $path_output)
    {        
        $extensionPath = $tempDir . DIRECTORY_SEPARATOR . 'var/connect/';
        if (!is_dir($extensionPath)) {
            mkdir($extensionPath, 0777, true);
        }
        $extensionFileName = $extensionPath . $config['extension_name'] . '.xml';
        file_put_contents($extensionFileName, self::buildExtensionXml($files, $config));
        
        shell_exec('cp -Rf '    . $tempDir . DIRECTORY_SEPARATOR . 'var '. $path_output);
    }
    static public function buildExtensionXml($files, $config)
    {
        $xml = simplexml_load_string('<_/>');
        $build_data = self::getBuildData($xml, $files, $config);
        
        foreach ($build_data as $key => $value) {
            if (is_array($value) && is_callable($key)) {
                call_user_func_array($key, $value);
            } else {
                self::addChildNode($xml, $key, $value);
            }
        }
    
        return $xml->asXml();
    }
    /**
     * Get an array of data to build the extension xml. The array of data will contains the key necessary 
     * to build each node and key that are actual callback functions to be called to build sub-section  of the xml.
     * @param  SimpleXMLElement $xml
     * @param  array $files
     * @param  array $config
     * @return array
     */
    static public function getBuildData(SimpleXMLElement $xml, array $files, array $config)
    {
        return array(
            'form_key' => isset($config['form_key']) ? $config['form_key'] : uniqid(),
            '_create' => isset($config['_create']) ? $config['_create'] : '',
            'name' => $config['extension_name'],
            'channel'=> $config['channel'],
            'Pulsestorm_MagentoTarToConnect::buildVersionIdsNode' => array($xml),
            'summary' => $config['summary'],
            'description' => $config['description'],
            'license' => $config['license'],
            'license_uri' => isset($config['license_uri']) ? $config['license_uri'] : '',
            'version' => $config['extension_version'],
            'stability' => $config['stability'],
            'notes' => $config['notes'],
            'Pulsestorm_MagentoTarToConnect::buildAuthorsNode' => array($xml, $config),
            'Pulsestorm_MagentoTarToConnect::buildPhpDependsNode' => array($xml, $config),
            'Pulsestorm_MagentoTarToConnect::buildContentsNode' => array($xml, $files)
        );
    }
    /**
     * Remove a passed in file absolute path and return the relative path to the Magento application file context.
     * @param  string $file
     * @return string
     */
    static public function extractRelativePath($file)
    {
        $pattern = '/app\/etc\/|app\/code\/community\/|app\/code\/local\/|app\/design\/|lib\/|app\/locale\/|skin\/|js\//';
        $relativePath = self::splitFilePath($file, $pattern);
        if ($file !== $relativePath) {
            return $relativePath;
        }
        $shellDir = 'shell';
        $relativePath = self::splitFilePath($file, '/' . $shellDir . '\//');
        return ($file !== $relativePath) ? $shellDir . DIRECTORY_SEPARATOR . $relativePath : $file;
    }
    /**
     * Split a file path using the passed in pattern and file absolute path and return
     * the relative path to the file.
     * @param  string $file
     * @param  string $pattern
     * @return string The relative path to file
     */
    static public function splitFilePath($file, $pattern)
    {
        $splitPath = preg_split($pattern, $file, -1);
        return (count($splitPath) > 1) ? $splitPath[1] : $file;
    }
    /**
     * Build 'contents' node including all its child nodes.
     * @param  SimpleXMLElement $xml
     * @param  array $files
     * @return void
     */
    static public function buildContentsNode(SimpleXMLElement $xml, array $files)
    {
        $node = self::addChildNode($xml, 'contents', '');
        $call_backs = array(
            'target' => 'Pulsestorm_MagentoTarToConnect::extractTarget', 
            'path'   => 'Pulsestorm_MagentoTarToConnect::extractRelativePath', 
            'type'   => 'file', 
            'include'=> '', 
            'ignore' => ''
        );
    
        $parent_nodes = array_reduce(array_keys($call_backs), function ($item, $key) use ($node) {
            $item[$key] = Pulsestorm_MagentoTarToConnect::addChildNode($node, $key, '');
            return $item;
        });
    
        // Adding empty node, this is a workaround for the Magento connect bug. 
        // When no empty nodes are added the first file is removed from the package extension.
        foreach ($parent_nodes as $child_key => $child_node) {
            self::addChildNode($child_node, $child_key, '');
        }
    
        foreach ($files as $file) {
            foreach ($parent_nodes as $key => $child_node) {
                $call_back = $call_backs[$key];
                $value = ($call_back === 'file') ? $call_back : (is_callable($call_back) ? call_user_func_array($call_back, array($file)) : $call_back);
                self::addChildNode($child_node, $key, $value);
            }
        }
    }
    /**
     * Add a 'depends_php_min' node and a 'depends_php_max' to the passed in SimpleXMLElement class instance object.
     * @param  SimpleXMLElement $xml
     * @param  array $config
     * @return void
     */
    static public function buildPhpDependsNode(SimpleXMLElement $xml, array $config)
    {
        $data = array('depends_php_min' => 'php_min', 'depends_php_max' => 'php_max');
        foreach ($data as $key => $cfg_key) {
            self::addChildNode($xml, $key, $config[$cfg_key]);
        }
    }
    /**
     * Get author data, which is a combination of author data and additional authors data from the configuration.
     * @param  array $config
     * @return array
     */
    static public function getAuthorData(array $config)
    {
         $authorList[0]      = array(
            'author_name'   => $config['author_name'],
            'author_user'   => $config['author_user'],
            'author_email'  => $config['author_email'],
        );
        if (array_key_exists('additional_authors', $config)) {
            $authorList = array_merge($authorList, $config['additional_authors']);
        }
        return $authorList;
    }
    /**
     * Get a specific author information by key.
     * @param  array $authorList
     * @param  string $key
     * @return array
     */
    static public function getAuthorInfoByKey(array $authorList, $key)
    {
        return array_map(function($author) use ($key) { return $author[$key]; }, $authorList);
    }
    /**
     * Build 'authors' node including all its child nodes.
     * @param  SimpleXMLElement $xml
     * @param  array $config
     * @return void
     */
    static public function buildAuthorsNode(SimpleXMLElement $xml, array $config)
    {
        $meta = array('name' => 'author_name', 'user' => 'author_user', 'email' => 'author_email');
        $authorList = self::getAuthorData($config);
        $authors = self::addChildNode($xml, 'authors', '');
        foreach ($meta as $key => $cfg_key) {
            $parentNode = self::addChildNode($authors, $key, '');
            foreach (self::getAuthorInfoByKey($authorList, $cfg_key) as $value) {
                self::addChildNode($parentNode, $key, $value);
            }
        }
    }
    /**
     * Build 'version_ids' node including all its child nodes.
     * @param  SimpleXMLElement $xml
     * @return void
     */
    static public function buildVersionIdsNode(SimpleXMLElement $xml)
    {
        $key = 'version_ids';
        $parentNode = self::addChildNode($xml, $key, '');
        foreach (array(2, 1) as $version) {
            self::addChildNode($parentNode, $key, $version);
        }
    }
    /**
     * Add child node to a passed in SimpleXMLElement class instance object.
     * @param  SimpleXMLElement $context
     * @param  string $name
     * @param  string $value
     * @return SimpleXMLElement
     */
    static public function addChildNode(SimpleXMLElement $context, $name, $value='')
    {
        $child = $context->addChild($name);
        if (trim($value)) {
            $child->{0} = $value;
        }
        return $child;
    }
}
if(isset($argv))
{
    Pulsestorm_MagentoTarToConnect::main($argv);
}
