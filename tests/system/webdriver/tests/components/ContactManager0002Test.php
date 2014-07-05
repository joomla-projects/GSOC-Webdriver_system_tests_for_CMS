<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Contact: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class ContactManager0002Test extends JoomlaWebdriverTestCase
{

  /**
	 * The page class being tested.
	 *
	 * @var     contactManagerPage
	 * @since   3.2
	 */
	protected $contactManagerPage = null;

	/**
	 * Login to back end and navigate to menu Contacts.
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->contactManagerPage = $cpPage->clickMenu('Contacts', 'ContactManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->contactManagerPage->getFilters();
		$expectedIds = array_values($this->contactManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$contactName = 'Test Filter' . $salt;
		$this->contactManagerPage->addContact($contactName, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$test = $this->contactManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Contact should not show');
		$test = $this->contactManagerPage->setFilter('filter_published', 'Published');
		$this->assertEquals(8, $this->contactManagerPage->getRowNumber($contactName), 'Contact should be in row 8');
		$this->contactManagerPage->trashAndDelete($contactName);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Contact should not be present');
	}

	/**
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterContacts()
	{
		$contactName_1 = 'Test Filter 1';
		$contactName_2 = 'Test Filter 2';

		$this->contactManagerPage->addContact($contactName_1, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$state = $this->contactManagerPage->getState($contactName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');


		$this->contactManagerPage->addContact($contactName_2, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$state = $this->contactManagerPage->getState($contactName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->contactManagerPage->changeContactState($contactName_2, 'unpublished');

		$test = $this->contactManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName_1), 'Contact should not show');
		$this->assertEquals(1, $this->contactManagerPage->getRowNumber($contactName_2), 'Contact should be in row 1');

		$test = $this->contactManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName_2), 'Contact should not show');
		$this->assertEquals(8, $this->contactManagerPage->getRowNumber($contactName_1), 'Contact should be in row 8');

		$this->contactManagerPage->setFilter('Select Status', 'Select Status');
		$this->contactManagerPage->trashAndDelete($contactName_1);
		$this->contactManagerPage->trashAndDelete($contactName_2);
	}
	
	/**
         * create archived contacts and then verify its existence.
         *
         * @test
         */
    public function setFilter_TestFilters_ShouldFilterTags2()
    {
        $contactName_1 = 'Test Filter 1';
        $contactName_2 = 'Test Filter 2';

        $this->contactManagerPage->addContact($contactName_1, false);
        $message = $this->contactManagerPage->getAlertMessage();
        $this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
        $state = $this->contactManagerPage->getState($contactName_1);
        $this->assertEquals('published', $state, 'Initial state should be published');
        $this->contactManagerPage->addContact($contactName_2, false);
        $message = $this->contactManagerPage->getAlertMessage();
        $this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
        $state = $this->contactManagerPage->getState($contactName_2);
        $this->assertEquals('published', $state, 'Initial state should be published');
        $this->contactManagerPage->changeContactState($contactName_2, 'Archived');

        $this->contactManagerPage->setFilter('filter_published', 'Archived');
        $this->assertFalse($this->contactManagerPage->getRowNumber($contactName_1), 'Contact should not show');
        $this->assertGreaterThanOrEqual(1, $this->contactManagerPage->getRowNumber($contactName_2), 'Test Contact should be present');;

        $this->contactManagerPage->setFilter('filter_published', 'Published');
        $this->assertFalse($this->contactManagerPage->getRowNumber($contactName_2), 'Contact should not show');
        $this->assertGreaterThanOrEqual(1, $this->contactManagerPage->getRowNumber($contactName_1), 'Test Contact should be present');
        $this->contactManagerPage->setFilter('Select Status', 'Select Status');
        $this->contactManagerPage->trashAndDelete($contactName_1);
        $this->contactManagerPage->trashAndDelete($contactName_2);
    }
    
    	/**
	 * check and verify the creation of the featured contact
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addContact_FeaturedContact_ContactAdded()
	{
		$cfg = new SeleniumConfig;
		/*adding contact of the test contact*/
		$contactManager = 'administrator/index.php?option=com_contact';
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$salt = rand();
		$contactName = 'contact_ABC' . $salt;
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName, array('Featured' => 'Yes'));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'contact save should return success');

		/*confirming if the contact is present in front end*/
		$PageUrl = 'index.php/using-joomla/extensions/components/contact-component/featured-contacts';
		$this->driver->get($cfg->host . $cfg->path . $PageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->siteHomePage->itemExist($contactName, 'a'), 'contact should be present');

		/*delete test contact*/
		$cpPage = $this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $contactManager);
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
		$this->contactManagerPage->trashAndDelete($contactName);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test article should not be present');
	}
}
