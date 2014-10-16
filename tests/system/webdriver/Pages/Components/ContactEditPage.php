<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end menu items manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class ContactEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='contact-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_contact&view=contact&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array('details', 'misc', 'publishing', 'attrib-jmetadata');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
			array('label' => 'Name', 'id' => 'jform_name', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Linked User', 'id' => 'jform_user_id', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Image', 'id' => 'jform_image', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Position', 'id' => 'jform_con_position', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Email', 'id' => 'jform_email_to', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Address', 'id' => 'jform_address', 'type' => 'textarea', 'tab' => 'details'),
			array('label' => 'City or Suburb', 'id' => 'jform_suburb', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'State or Province', 'id' => 'jform_state', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Postal / ZIP Code', 'id' => 'jform_postcode', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Country', 'id' => 'jform_country', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Telephone', 'id' => 'jform_telephone', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Mobile', 'id' => 'jform_mobile', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Fax', 'id' => 'jform_fax', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Website', 'id' => 'jform_webpage', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'First Sort Field', 'id' => 'jform_sortname1', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Second Sort Field', 'id' => 'jform_sortname2', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Third Sort Field', 'id' => 'jform_sortname3', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Tags', 'id' => 'jform_tags', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Featured', 'id' => 'jform_featured', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Version Note', 'id' => 'jform_version_note', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Miscellaneous Information', 'id' => 'jform_misc', 'type' => 'textarea', 'tab' => 'misc'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created date', 'id' => 'jform_created', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by', 'id' => 'jform_created_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created By Alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified Date', 'id' => 'jform_modified', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified by', 'id' => 'jform_modified_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Revision', 'id' => 'jform_version', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Hits', 'id' => 'jform_hits', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'publishing'),
			);

}

