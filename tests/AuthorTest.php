<?php
class AuthorTest extends MagentoTarToConnectBaseTest
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
        $results        = $this->_buildExtensionFromFixture('Pulsestorm_Better404.tar');     
        // $xml_gui        = simplexml_load_file($results['connect_xml']);
        $xml_package    = simplexml_load_file($results['extracted'] . '/package.xml');

        $names = array();
        foreach($xml_package->authors->children() as $author)
        {            
            $names[] = $author->name;
        }
        
        $this->assertTrue(in_array('Alan Storm', $names));
    }
}