<?php

// ----------------------------------------------------------------------------------
// Class: Model_test
// ----------------------------------------------------------------------------------

/**
 * <p>This class tests functions of the Model base class.</p>
 *
 * @version  $Id: Model_tests.php 522 2007-08-14 09:22:22Z cweiske $
 * @author Richard Cyganiak (richard@cyganiak.de)
 *
 * @package unittests
 * @access	public
 */
class Model_test extends UnitTestCase {

    function testGetResModel() {
        $mem = &ModelFactory::getDefaultModel();
        $ont = $mem->getResModel();
        $this->assertIsA($ont, 'ResModel');
    }

    function testGetResModelIsBasedOnThisModel() {
        $mem = &ModelFactory::getDefaultModel();
        $ont = $mem->getResModel();
        $this->assertIdentical(0, $ont->size());
        $mem->add(new Statement(
                new Resource('http://example.org/#Fred'),
                new Resource('http://example.org/#name'),
                new Literal('Fred')));
        $this->assertIdentical(1, $ont->size());
    }

    function testGetOntModel() {
        $mem = &ModelFactory::getDefaultModel();
        $ont = $mem->getOntModel(RDFS_VOCABULARY);
        $this->assertIsA($ont, 'OntModel');
        $this->assertIdentical(array(), $ont->listClasses());
    }

    function testGetOntModelIsBasedOnThisModel() {
        $mem = &ModelFactory::getDefaultModel();
        $ont = $mem->getOntModel(RDFS_VOCABULARY);
        $this->assertIdentical(0, $ont->size());
        $mem->add(new Statement(
                new Resource('http://example.org/#Fred'),
                new Resource('http://example.org/#name'),
                new Literal('Fred')));
        $this->assertIdentical(1, $ont->size());
    }
}

?>