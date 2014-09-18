<?php
class FinallyTest extends PHPUnit_Framework_TestCase
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
    
    protected function _untarIntoTemp($path)
    {
        $original_dir = getcwd();
        $dir = tempnam('/tmp','mt2c');;
        unlink($dir);
        mkdir($dir);        
        chdir($dir);
        $cmd = 'tar -xvf ' . $path;
        ob_start();
        `$cmd`;
        ob_end_clean();
        chdir($original_dir);
        return $dir;
    }
    
    protected function _getFixturePath($name)
    {
        return realpath(__DIR__) . '/fixtures/' . $name;
    }
}