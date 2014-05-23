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
       	 */
	public function addMenuItem_FrontEndCheck_MenuAdded()
	{
		$cfg = new SeleniumConfig();
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		//create a menu item
		$this->menuItemsManagerPage=$this->getPageObject('MenuItemsManagerPage');
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber('Test Menu Item'), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem();
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Menu successfully saved') >= 0, 'Menu save should return success');
		
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
		
		$title = 'Menu Item'.$salt;
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
	
	/**
	 * @test 
	 */
	public function addMenu_CategoryBlog_MenuAdded()
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

		$articleName1 = 'article_ABC_1'.$salt;
		$articleName2 = 'article_ABC_2'.$salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName1, $categoryName);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName2, $categoryName);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		//create menu 

		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		
		$title = 'Menu Item'.$salt;
		$menuType = 'Category Blog ';
		$menuLocation = 'Main Menu';
		$metaDescription = 'Test menu item for web driver test.';
		$this->menuItemsManagerPage=$this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title, $menuType, $menuLocation, array('category' => $categoryName, 'Meta Description' => $metaDescription));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);


		//check the existence of the menu in the front end.
		$cfg = new SeleniumConfig();
		$homePageUrl = 'index.php';
		$d=$this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		if($this->itemExist($title));
		{	
			$d->findElement(By::xPath("//a[contains(text(),'" .  $title . "')]"))->click();
			$arrayTitles = $this->siteHomePage->getArticleTitles();
			$this->assertTrue($this->itemExist($articleName1));
			$this->assertTrue($this->itemExist($articleName2));
		}	



		//deleting the test items
		$cpPage = $this->doAdminLogin();
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
	 * @test 
	 */
	public function addMenu_CategoryList_MenuAdded()
	{
		//adding test category.
		$cfg = new SeleniumConfig();
		$categoryManager = 'administrator/index.php?option=com_categories&extension=com_content';
		$this->driver->get($cfg->host . $cfg->path . $categoryManager);

		$salt = rand();
		$categoryName1 = 'category_ABC1'.$salt;
		$categoryName2 = 'category_ABC2'.$salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName1), 'Test Category should not be present');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName2), 'Test Category should not be present');
		
		$this->categoryManagerPage->addCategory($categoryName1);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');

		$this->categoryManagerPage->addCategory($categoryName2);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		
		//adding menu item with category 1
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing'.$salt;
		$menuType = 'Category List';
		$menuLocation = 'Main Menu';
		$metaDescription = 'Test menu item for web driver test.';
		$this->menuItemsManagerPage=$this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->assertFalse($this->menuItemsManagerPage->getRowNumber($title), 'Test menu should not be present');
		$this->menuItemsManagerPage->addMenuItem($title,$menuType,$menuLocation, array('category' => $categoryName1));
		$message = $this->menuItemsManagerPage->getAlertMessage();
		$this->assertContains('Menu item successfully saved', $message, 'Menu save should return success', true);

		
		//adding article of the test category 1
		$articleManager = 'administrator/index.php?option=com_content';
		$this->driver->get($cfg->host . $cfg->path . $articleManager);
		$articleName1 = 'article_ABC_1'.$salt;
		$articleName2 = 'article_ABC_2'.$salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName1), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName2), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName1, $categoryName1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName2, $categoryName1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');

		//adding article of the test category 2
		$articleName3 = 'article_ABC_3'.$salt;
		$articleName4 = 'article_ABC_4'.$salt;
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName3), 'Test Article should not be present');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName4), 'Test Article should not be present');
		$this->articleManagerPage->addArticle($articleName3, $categoryName2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$this->articleManagerPage->addArticle($articleName4, $categoryName2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');



		//verifying from front end
		// we have  to verify that the correct set of articles are present in the menu
		$cfg = new SeleniumConfig();
		$homePageUrl = 'index.php';
		$d=$this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		if($this->itemExist($title));
		{	
			$d->findElement(By::xPath("//a[contains(text(),'" .  $title . "')]"))->click();
			$this->assertTrue($this->itemExist($articleName1),'Article Must be present');
			$this->assertTrue($this->itemExist($articleName2),'Article Must be present');
		}	


		//edit menu item with category 2
		$cpPage = $this->doAdminLogin();
		$MenuItemsManager = 'administrator/index.php?option=com_menus&view=items';
		$this->driver->get($cfg->host . $cfg->path . $MenuItemsManager);
		$title = 'Menu_Item_testing'.$salt;
		$menuType = 'Category List';
		$menuLocation = 'Main Menu';
		$metaDescription = 'Test menu item for web driver test.';
		$this->menuItemsManagerPage=$this->getPageObject('MenuItemsManagerPage');
		$this->menuItemsManagerPage->setFilter('Menu', $menuLocation);
		$this->menuItemsManagerPage->editMenuItem($title, array('category' => $categoryName2));

		//verify from front end
		$cfg = new SeleniumConfig();
		$homePageUrl = 'index.php';
		$d=$this->driver;
		$d->get($cfg->host . $cfg->path . $homePageUrl);
		$this->siteHomePage = $this->getPageObject('SiteContentFeaturedPage');
		if($this->itemExist($title));
		{	
			$d->findElement(By::xPath("//a[contains(text(),'" .  $title . "')]"))->click();
			$this->assertTrue($this->itemExist($articleName3),'Article Must be present');
			$this->assertTrue($this->itemExist($articleName4),'Article Must be present');
		}	


		// delete all the test items
		$cpPage = $this->doAdminLogin();
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
}
