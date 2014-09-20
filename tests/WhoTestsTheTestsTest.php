<?php
class WhoTestsTheTestsTest extends MagentoTarToConnectBaseTest
{
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
        $this->assertTrue(file_exists($results['extension']));
        $this->assertTrue(file_exists($results['connect_xml']));
    }
}