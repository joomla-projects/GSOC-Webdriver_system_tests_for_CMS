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
 * This class tests the  Menu: Add / Edit  Front End.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (coffee) 2005 - 2014 Open Source Matters, Inc.   All rights reserved.
 * @liicense     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class MenuItemsManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since   3.0
	 *
	 * @return void	 *
	 */
	public function setUp()
	{
		parent::setUp();
		$this->doAdminLogin();
	}

	/**
	 * Do admin logout
	 *
	 * @return void
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * creates a menu item and check its existence on site page
	 *
	 * @return void
	 *
	 * @test
	 *
	 */
	public function addMenuItem_FrontEndCheck_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber('Test Menu Item'), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem();
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Menu successfully saved') >= 0, 'Menu save should return success');
		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		$this->assertTrue($this->itemExist("Test Menu Item", 'a'), "Menu should be present in the front end");

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete('Test Menu Item');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber('Test Menu Item'), 'Test menu should not be present');
	}

	/**
	 * create menu item of type single article and verifying its existence on front end
	 *
	 * @return void
	 *
	 * @test
	 *
	 */
	public function  addMenu_SingleArticle_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		$salt = rand();
		$categoryName = 'category_ABC' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);

		$articleName = 'article_ABC' . $salt;
		$category = $categoryName;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName, $category);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$MenuItemManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemManager);
		$title = 'Menu Item' . $salt;
		$menuType = 'Single Article';
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('article' => $articleName));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		if($this->itemExist($title, 'a'))
		{
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$arrayTitles = $this->siteHomePage->getArticleTitles();
			$this->assertTrue(in_array($articleName, $arrayTitles), 'Article Must be present');
		}

		$this->doAdminLogin();
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

	/**
	 * create menu item of type category blog and verifying its existence on front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenu_CategoryBlog_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		$salt = rand();
		$categoryName = 'category_ABC' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);

		$articleName1 = 'article_ABC_1' . $salt;
		$articleName2 = 'article_ABC_2' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName1, $categoryName);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName2, $categoryName);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);

		$title = 'Menu Item' . $salt;
		$menuType = 'Category Blog ';
		$menuLocation = 'Main Menu';
		$metaDescription = 'Test menu item for web driver test.';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, array('category' => $categoryName, 'Meta Description' => $metaDescription));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		if($this->itemExist($title, 'a'));
		{
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertTrue($this->itemExist($articleName1, 'a'));
			$this->assertTrue($this->itemExist($articleName2, 'a'));
		}

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage->trashAndDelete($articleName1);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName2);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test article should not be present');

		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * create menu item of type category list and verifying its existence on front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenu_CategoryList_MenuAdded()
	{
		$cfg = new SeleniumConfig;
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$salt = rand();
		$categoryName1 = 'category_ABC1' . $salt;
		$categoryName2 = 'category_ABC2' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName1);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$this->categoryManagerPage->addCategory($categoryName2);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, array('category' => $categoryName1));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$articleName1 = 'article_ABC_1' . $salt;
		$articleName2 = 'article_ABC_2' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName1, $categoryName1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName2, $categoryName1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$articleName3 = 'article_ABC_3' . $salt;
		$articleName4 = 'article_ABC_4' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName3), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName4), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName3, $categoryName2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName4, $categoryName2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		if($this->itemExist($title, 'a'))
		{
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertTrue($this->itemExist($articleName1, 'a'), 'Article Must be present');
			$this->assertTrue($this->itemExist($articleName2, 'a'), 'Article Must be present');
		}

		$this->doAdminLogin();
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->menuItemsManagerPage->editMenuItem($title, array('category' => $categoryName2));
		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		if($this->itemExist($title, 'a'))
		{
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertTrue($this->itemExist($articleName3, 'a'), 'Article Must be present');
			$this->assertTrue($this->itemExist($articleName4, 'a'), 'Article Must be present');
		}

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage->trashAndDelete($articleName1);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName2);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName3);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName3), 'Test article should not be present');
		$this->articleManagerPage->trashAndDelete($articleName4);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName4), 'Test article should not be present');

		$this->driver->get($cfg->host . $cfg->path . $categoryManager);
		$this->categoryManagerPage->trashAndDelete($categoryName1);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->categoryManagerPage->trashAndDelete($categoryName2);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}

	/**
	 * Check option functionality
	 *
	 * @return void
	 *
	 * @test
	 *
	 */
	public function addMenu_OptionFunctionalityCheck_OptionWorking()
	{
		$cfg = new SeleniumConfig;
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$salt = rand();
		$articleName = 'article_ABC' . $salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing' . $salt;
		$menuType = 'Single Article';
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage = $this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('article' => $articleName));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		if($this->itemExist($title, 'a'))
		{
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertTrue($this->itemExist($articleName, 'a'), 'Article Must be present');
		}

		$this->doAdminLogin();
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);

		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->menuItemsManagerPage->editMenuItem($title, array('Linked Title' => 'No'));

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		if($this->itemExist($title, 'a'))
		{
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertFalse($this->itemExist($articleName, 'a'));
			$this->assertTrue($this->itemExist($articleName, 'h2'));
		}

		$this->doAdminLogin();
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->menuItemsManagerPage->editMenuItem($title, array('Show Title' => 'Hide','Linked Title' => 'Use Global'));

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		if($this->itemExist($title, 'a'))
		{
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertFalse($this->itemExist($articleName, 'h2'));
			$this->assertFalse($this->itemExist($articleName, 'a'));
		}

		$this->doAdminLogin();
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->menuItemsManagerPage->editMenuItem($title, array('Show Title' => 'Use Global', 'Show Category' => 'Show'));

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		if($this->itemExist($title,'a'))
		{
			$category = 'Sample Data-Articles';
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertTrue($this->itemExist($category, 'dd[2]/span'));
		}

		$this->doAdminLogin;
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);

		$title = 'Menu_Item_testing' . $salt;
		$menuLocation = 'Main Menu';
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->menuItemsManagerPage->editMenuItem($title, array('Show Category' => 'Show', 'Link Category' => 'Yes'));

		$cfg = new SeleniumConfig;
		$homePageUrl = 'index.php';
		$d = $this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');

		if($this->itemExist($title, 'a'));
		{
			$category = 'Sample Data-Articles';
			$d->findElement(By::xPath("//a[contains(text(),'" . $title . "')]"))->click();
			$this->assertTrue($this->itemExist($category, 'dd[2]/span'));
			$this->assertTrue($this->itemExist($category, 'a'));
		}

		$this->doAdminLogin();
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Test article should not be present');

		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$this->menuItemsManagerPage->setFilter('Menu', 'Main Menu');
		$this->menuItemsManagerPage->trashAndDelete($title);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
	}
}
