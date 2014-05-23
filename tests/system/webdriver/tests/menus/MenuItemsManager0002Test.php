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

/**
 * This class tests the Article: Front End and Add/Edit Screens.
 *
 * @package    Joomla.Test
 * @subpackage Webdriver
 * @since      3.2
 */
class MenuItemsManager0002Test extends JoomlaWebdriverTestCase{

    /**
	 * The page class being tested.
	 *
	 * @var ArticleManagerPage
	 * @var $categoryManagerPage
	 * @var $menuItemsManagerPage
	 * @since 3.2
	 */
	 public function setUp()
	{
		$cfg = new SeleniumConfig();
		parent::setUp();
		$this->driver->get($cfg->host . $cfg->path);
		$cpPage = $this->doAdminLogin();
	}

	/**
	 * Logout and close test.
	 *
	 * @since 3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}
	
}
