<?php
class MagentoTarToConnectBaseTest extends PHPUnit_Framework_TestCase
{
    const EXAMPLE_CONFIG                = 'example-config.php';
    const PATH_BUILD                    = '/tests/fixtures/build';
    const PATH_BUILD_ARTIFACTS_SUFFIX   = '-artifacts';
    
    public function setup()
    {
        require_once realpath(__DIR__) . '/../magento-tar-to-connect.php';    

        //create a build-artifacts folder if it doesn't exist
        $path_build_artifacts = $this->_getBaseBuildArtifactsPath();
        if(!file_exists($path_build_artifacts))
        {
            mkdir($path_build_artifacts);
        }

        $path_build = $this->_getBaseBuildPath();
        //if there's a build folder, move it to artifacts
        if(file_exists($path_build))
        {
            rename($path_build, $path_build_artifacts . '/' . uniqid());
        }
        
        //create a build folder
        mkdir($path_build);
    }
    
    public function testSetup()
    {
        $this->assertTrue(true);
    }
    
    public function testGetFixturePath()
    {
        $fixture = $this->_getFixturePath('first.txt');
        $this->assertTrue(file_exists($fixture));
    }
    
    public function testGetTarFixture()
    {
        $fixture = $this->_getFixturePath('first.tar');
        $path    = $this->_untarIntoTemp($fixture);
        
        // echo "\n",$path . '/first.txt',"\n";
        $this->assertTrue(file_exists($path . '/first.txt'));
    }
    
    public function testCanReadExampleConfig()
    {
        return $this->assertTrue(
            file_exists($this->_getBaseRespoitoryPath() . '/' . self::EXAMPLE_CONFIG)
        );
    }
    
    public function testCopyFixtureToBuild()
    {
        $fixture = $this->_getFixturePath('first.tar');
        $this->_copyFixtureToBuild($fixture);

        $this->assertTrue(file_exists(
            $this->_getBaseBuildPath() . '/first.tar'
        ));
    }
    
    public function testRunTarToConnect()
    {
        $results = $this->_buildExtensionFromFixture('first.tar');
        $this->assertTrue(file_exists($results['extension']));
    }
    
    public function testRunTarToConnectNamedNonDefault()
    {
        $fixture = 'first.tar';
        $name    = 'Pulsestorm_Unittestdifferent';
        $config  = $this->_getBaseExtensionConfig($fixture);
        $config['extension_name'] = $name;

        $results = $this->_buildExtensionFromFixture('first.tar', $config);
        $this->assertTrue((boolean)strpos($results['extension'], $name));
        $this->assertTrue(file_exists($results['extension']));        
    }
    
    public function testRunTarToConnectAndTmpExtraction()
    {
        $results = $this->_buildExtensionFromFixture('first.tar');
        
        $this->assertTrue(file_exists($results['extracted'] . '/package.xml'));
        $this->assertTrue(file_exists($results['extracted'] . '/first.txt'));
    }
    
    public function testBetter404()
    {
        $results = $this->_buildExtensionFromFixture('Pulsestorm_Better404.tar');
        $this->assertTrue(file_exists($results['extension']));
    }
    
    protected function _getBaseExtensionConfig($fixture, $extension_name='Pulsestorm_Unittest', $extension_version='1.0.0', 
        $author_email='testing@example.com')
    {
        $base_repo_path = $this->_getBaseRespoitoryPath();
        $config  = include $base_repo_path . '/' . self::EXAMPLE_CONFIG;
        
        $config['base_dir']             = $this->_getBaseBuildPath();
        $config['archive_files']        = $fixture;
        $config['extension_name']       = $extension_name;
        $config['extension_version']    = $extension_version;
        $config['path_output']          = $this->_getBaseBuildPath();
        $config['author_email']         = $author_email;
        $config['skip_version_compare'] = true;
        return $config;
    }
    
    protected function _buildExtensionFromFixture($fixture,$config=false)
    {
        $path_fixture = $this->_getFixturePath($fixture);
        $this->_copyFixtureToBuild($path_fixture);
        
        if(!$config)
        {
            $config = $this->_getBaseExtensionConfig($fixture);
        }

        Pulsestorm_MagentoTarToConnect::buildExtensionFromConfig($config);
        
        $extension_path = $this->_getBaseBuildPath() . '/' . 
            $config['extension_name']       . '-' . 
            $config['extension_version']    . '.tgz';
        
        $untared = $this->_untarIntoTemp($extension_path);
        
        $results = array();
        $results['extension'] = $extension_path;
        $results['extracted'] = $untared;
        
        return $results;
        
    }
    
    protected function _getBaseRespoitoryPath()
    {
        return realpath((__DIR__ . '/../'));
    }
    
    protected function _untarIntoTemp($path)
    {
        $original_dir = getcwd();
        
        //create a temp file, turn it into a directory
        $dir = tempnam('/tmp','mt2c');;
        unlink($dir);
        mkdir($dir);        
        chdir($dir);

        $tar = new Archive_Tar($path);
        $tar->extract('.');
        chdir($original_dir);
        return $dir;
    }
    
    protected function _getFixturePath($name)
    {
        return realpath(__DIR__) . '/fixtures/' . $name;
    }
    
    protected function _copyFixtureToBuild($path)
    {
        $path_new = dirname($path) . '/build/' . basename($path);
        copy($path, $path_new);
        return $path_new;
    }
        
    protected function _getBaseBuildArtifactsPath()
    {
        return $this->_getBaseBuildPath() . self::PATH_BUILD_ARTIFACTS_SUFFIX;
    }
    protected function _getBaseBuildPath()
    {
        return $this->_getBaseRespoitoryPath() . self::PATH_BUILD;
    }    
}