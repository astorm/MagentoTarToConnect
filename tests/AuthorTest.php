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
        $xml_gui        = simplexml_load_file($results['connect_xml']);
        $xml_package    = simplexml_load_file($results['extracted'] . '/package.xml');

        $names_package = array();
        foreach($xml_package->authors->children() as $author)
        {            
            $names_package[] = (string) $author->name;
        }

        foreach($xml_gui->authors->name->children() as $author)
        {            
            $names_gui[] = (string) $author;
        }

        $this->assertEquals($names_package, $names_gui);
    }
}