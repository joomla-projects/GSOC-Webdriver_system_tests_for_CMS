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
	
	 /**
	 * @test
	 *
	*/
	public function addMenuItem_FrontEndCheck_MenuAdded()
	{
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		
		//create a menu item
		$salt=rand();
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber("Test Menu Item"), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem();
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		//check in the front end.
		$cfg = new SeleniumConfig();
		$homePageUrl = 'index.php';
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->itemExist("Test Menu Item"),"Menu should be present in the front end");
		

		//delete the menu item
		$cpPage = $this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete('Test Menu Item');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber('Test Menu Item'), 'Test menu should not be present');

	}

	/**
	 * @test
	 */
	public function  addMenu_SingleArticle_MenuAdded()
	{
		//adding test category.
		$cfg = new SeleniumConfig();
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		$salt = rand();
		$categoryName = 'category_ABC'.$salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		//adding article of the test category
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);

		$articleName = 'article_ABC'.$salt;
		$category = $categoryName;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName, $category);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		//create menu 

		$MenuItemManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemManager);
		
		$title = 'Menu_Item_testing'.$salt;
		$menuType = 'Single Article';
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('article' => $articleName));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		//check in the front end.
		$cfg = new SeleniumConfig();
		$homePageUrl = 'index.php';
		$d=$this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		if($this->itemExist($title));
		{	
			$d->findElement(By::xPath("//a[contains(text(),'" .  $title . "')]"))->click();
			$arrayTitles = $this->siteHomePage->getArticleTitles();
			$this->assertTrue(in_array($articleName, $arrayTitles), 'Article Must be present');
		}	


		//delete test articles, category and menu.
		$cpPage = $this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test article should not be present');
		
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		
		$this->driver->get($cfg->host . $cfg->path . $MenuItemManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');

	}

}
