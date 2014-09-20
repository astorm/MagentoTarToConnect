<?php
class ExampleText extends MagentoTarToConnectBaseTest
{
    /**
    * Parent setup has important business to do, please call
    */
    public function setup()
    {
        return parent::setup();
    }
    
    public function testExample()
    {
        //drop a tar file in the tests/fixtures folder, and 
        //you can build it's path with the following
        $results_first = $this->_buildExtensionFromFixture('first.tar');
        
        //if you don't like the default configuration we used, specify your own
        $config = $this->_getBaseExtensionConfig('Pulsestorm_Better404.tar');
        $config['extension_name'] = 'Pulsestorm_Better404';
        $results_second = $this->_buildExtensionFromFixture('Pulsestorm_Better404.tar', $config);
        
        //The results array has three keys
        $results_first['extension'];    //path to the built extension
        $results_first['connect_xml'];  //path to the built adminhtml connect XML file
        $results_first['extracted'];    //path to a tmp folder with an already extracted tgz
                                        //that is, the build extension extracted to a folder
                                        
        //With the information in the three tests above, you're free to test anything 
        //about the built extension, and with the fixtures folder being a simple drop 
        //drop in, you're free to create clear reproducable bug reports
        
        $this->assertTrue(file_exists($results_first['extracted'] . '/package.xml'));
        $this->assertTrue(file_exists($results_second['extracted'] . '/package.xml'));
    }
}