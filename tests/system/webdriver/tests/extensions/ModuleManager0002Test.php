<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;

/**
 * This class tests the  Module: Add / Edit  on front end.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
 
class ModuleManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var ModuleManagerPage
	 */
	protected $moduleManagerPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();

		/* @var $cpPage ControlPanelPage */

	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}
}
