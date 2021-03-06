<?php

require_once('common/language/BaseLanguage.class.php');
Mock::generate('BaseLanguage');

require_once('common/reference/ReferenceManager.class.php');

require_once('common/dao/ReferenceDao.class.php');
Mock::generate('ReferenceDao');
require_once('common/dao/CrossReferenceDao.class.php');
Mock::generate('CrossReferenceDao');
require_once('common/dao/include/DataAccessResult.class.php');
Mock::generate('DataAccessResult');

/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 *
 *
 * Tests the class ReferenceManager
 */
class ReferenceManagerTest extends TuleapTestCase {
    private $rm;
    private $user_manager;

    public function setUp() {
        parent::setUp();
        EventManager::setInstance(mock('EventManager'));
        ProjectManager::setInstance(mock('ProjectManager'));
        $this->user_manager = mock('UserManager');
        UserManager::setInstance($this->user_manager);
        $this->rm = partial_mock('ReferenceManager', array(
            '_getReferenceDao',
            '_getCrossReferenceDao',
            'loadReservedKeywords',
            'getGroupIdFromArtifactIdForCallbackFunction',
            'getGroupIdFromArtifactId'
        ));
        $this->rm->__construct();
    }
    
    public function tearDown() {
        EventManager::clearInstance();
        ProjectManager::clearInstance();
        UserManager::clearInstance();
        parent::tearDown();
    }
    
    function testSingleton() {
        $this->assertReference(ReferenceManager::instance(), ReferenceManager::instance());
        $this->assertIsA(ReferenceManager::instance(), 'ReferenceManager');
    }

    function testExtractReference() {
        $dao = new MockReferenceDao($this);
        $dar = new MockDataAccessResult($this);

        $dao->setReturnReference('searchActiveByGroupID', $dar, array('100'));
        $dar->setReturnValueAt(0, 'getRow', array(
            'id' => 1,
            'keyword' => 'art',
            'description' => 'reference_art_desc_key',
            'link' => '/tracker/?func=detail&aid=$1&group_id=$group_id',
            'scope' => 'S',
            'service_short_name' => 'tracker',
            'nature' => 'artifact',
            'id' => 1,
            'reference_id' => 1,
            'group_id' => 100,
            'is_active' => 1,
        ));
        $dar->setReturnValueAt(1, 'getRow', false);

        $dar2 = new MockDataAccessResult($this);
        $dao->setReturnReference('searchActiveByGroupID', $dar2, array('1'));
        $dar2->setReturnValueAt(0, 'getRow', array(
            'id' => 1,
            'keyword' => 'art',
            'description' => 'reference_art_desc_key',
            'link' => '/tracker/?func=detail&aid=$1&group_id=$group_id',
            'scope' => 'S',
            'service_short_name' => 'tracker',
            'nature' => 'artifact',
            'id' => 1,
            'reference_id' => 1,
            'group_id' => 1,
            'is_active' => 1,
        ));
        $dar2->setReturnValueAt(1, 'getRow', false);
        //The Reference manager

        $this->rm->setReturnReference('_getReferenceDao', $dao);
        $this->rm->setReturnValueAt(0, 'getGroupIdFromArtifactIdForCallbackFunction', '100');
        $this->rm->setReturnValueAt(1, 'getGroupIdFromArtifactIdForCallbackFunction', '1');
        $this->rm->setReturnValueAt(2, 'getGroupIdFromArtifactIdForCallbackFunction', '100');

        $this->assertTrue(count($this->rm->extractReferences("art #123", 0)) == 1, "Art is a shared keyword for all projects");
        $this->assertTrue(count($this->rm->extractReferences("arto #123", 0)) == 0, "Should not extract a reference on unknown keyword");
        $this->assertTrue(count($this->rm->extractReferences("art #1:123", 0)) == 1, "Art is a reference for project num 1");
        $this->assertTrue(count($this->rm->extractReferences("art #100:123", 0)) == 1, "Art is a reference for project named codendi");
    }

    function testExtractRegexp() {
        $dao = new MockReferenceDao($this);
        //The Reference manager
        $this->rm->setReturnReference('_getReferenceDao', $dao);
        $this->assertFalse(count($this->rm->_extractAllMatches("art 123")) == 1, "No sharp sign");
        $this->assertFalse(count($this->rm->_extractAllMatches("art#123")) == 1, "No space");
        $this->assertFalse(count($this->rm->_extractAllMatches("art #")) == 1, "No reference");

        $this->assertTrue(count($this->rm->_extractAllMatches("art #123")) == 1, "simple reference");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #abc")) == 1, "No number");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #abc:123")) == 1, "groupName:ObjID");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #123:123")) == 1, "groupID:ObjID");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #abc:abc")) == 1, "groupName:ObjName");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #123:abc")) == 1, "groupID:ObjName");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #123:abc is a reference to art #123 and rev #codendi:123 as well as file #123:release1", 0)) == 4, "Multiple extracts");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #123-rev #123", 0)) == 2, "Multiple extracts with '-'");
        $this->assertTrue(count($this->rm->_extractAllMatches("art #123:wikipage/2", 0)) == 1, "Wikipage revision number");

        # Projectname with - and _ See SR #1178
        $matches = $this->rm->_extractAllMatches("art #abc-def:ghi");
        $this->assertEqual($matches[0]['project_name'], 'abc-def:');
        $this->assertEqual($matches[0]['value'], 'ghi');
        $matches = $this->rm->_extractAllMatches("art #abc-de_f:ghi");
        $this->assertEqual($matches[0]['project_name'], 'abc-de_f:');
        $this->assertEqual($matches[0]['value'], 'ghi');

        # SR #2353 - Reference to wiki page name with "&" does not work
        $matches = $this->rm->_extractAllMatches('wiki #project:page/subpage&amp;toto&tutu & co');
        $this->assertEqual($matches[0]['key'], 'wiki');
        $this->assertEqual($matches[0]['project_name'], 'project:');
        $this->assertEqual($matches[0]['value'], 'page/subpage&amp;toto&tutu');
    }

    function test_updateProjectReferenceShortName() {
        $ref_dao = new MockReferenceDao($this);
        $cross_dao = new MockCrossReferenceDao($this);

        $this->rm->setReturnReference('_getReferenceDao', $ref_dao);
        $this->rm->setReturnReference('_getCrossReferenceDao', $cross_dao);

        $group_id = 101;
        $from = 'bug';
        $to = 'task';
        $ref_dao->expect('updateProjectReferenceShortName', array($group_id, $from, $to));
        $cross_dao->expect('updateTargetKeyword', array($from, $to, $group_id));
        $cross_dao->expect('updateSourceKeyword', array($from, $to, $group_id));

        $this->rm->updateProjectReferenceShortName($group_id, $from, $to);
    }

    public function testInsertReferencesConvertsToUTF8ForRightPHPVersion() {
        if ((version_compare(PHP_VERSION, '5.3.0') >= 0)) {
            $html = 'g&=+}éàùœ';
            $encoded = htmlentities($html, ENT_IGNORE, "UTF-8");
            $decoded = html_entity_decode($encoded, ENT_IGNORE, "ISO-8859-15" );

            $pre_encoding = mb_detect_encoding($decoded, 'UTF-8,ISO-8859-15');
            $this->assertEqual($pre_encoding, 'ISO-8859-15');

            $this->rm->insertReferences($decoded, 45);

            $post_encoding = mb_detect_encoding($decoded, 'UTF-8,ISO-8859-15');
            $this->assertEqual($post_encoding, 'UTF-8');
        }
    }

     public function testInsertReferencesDoesNotConvertToUTF8ForRightPHPVersion() {
        if (! (version_compare(PHP_VERSION, '5.3.0') >= 0)) {
            $html = 'g&=+}éàùœ';
            $encoded = htmlentities($html, ENT_NOQUOTES, "UTF-8");
            $decoded = html_entity_decode($encoded, ENT_NOQUOTES, "ISO-8859-15" );

            $pre_encoding =  mb_detect_encoding($decoded, 'UTF-8,ISO-8859-15');
            $this->assertEqual($pre_encoding, 'ISO-8859-15');

            $this->rm->insertReferences($decoded, 45);

            $post_encoding = mb_detect_encoding($decoded, 'UTF-8,ISO-8859-15');
            $this->assertEqual($post_encoding, 'ISO-8859-15');
        }
    }

    public function itInsertsLinkForMentionAtTheBeginningOfTheString() {
        stub($this->user_manager)->getUserByUserName('username')->returns(mock('PFUser'));

        $html = '@username';
        $this->rm->insertReferences($html, 0);
        $this->assertEqual($html, '<a href="/users/username">@username</a>');
    }

    public function itDoesNotInsertsLinkForUserThatDoNotExist() {
        stub($this->user_manager)->getUserByUserName('username')->returns(null);

        $html = '@username';
        $this->rm->insertReferences($html, 0);
        $this->assertEqual($html, '@username');
    }

    public function itInsertsLinkForMentionAtTheMiddleOfTheString() {
        stub($this->user_manager)->getUserByUserName('username')->returns(mock('PFUser'));

        $html = '/cc @username';
        $this->rm->insertReferences($html, 0);
        $this->assertEqual($html, '/cc <a href="/users/username">@username</a>');
    }

    public function itDoesNotBreakEmailAddress() {
        $html = 'toto@userna.me';
        $this->rm->insertReferences($html, 0);
        $this->assertEqual($html, 'toto@userna.me');
    }
}