<?php
class MagentoTarToConnectBaseTest extends PHPUnit_Framework_TestCase
{
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
    
    public function testRunTarToConnect()
    {
        $fixture = $this->_getFixturePath('first.tar');
        require_once realpath(__DIR__) . '/../magento-tar-to-connect.php';
        $this->assertTrue(false);
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
}