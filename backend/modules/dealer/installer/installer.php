<?php

/*
 * This file is part of Fork CMS.
*
* For the full copyright and license information, please view the license
* file that was distributed with this source code.
*/

/**
 * Installer for the dealer module
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class DealerInstaller extends ModuleInstaller
{
	/**
	 * Install the module.
	 */
	public function install()
	{
		// load install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'dealer' as a module
		$this->addModule('dealer', 'The dealer locator module.');

		// import locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		// module rights
		$this->setModuleRights(1, 'dealer');

		// general settings
		$this->setSetting('dealer', 'zoom_level', 'auto');
		$this->setSetting('dealer', 'width', 900);
		$this->setSetting('dealer', 'height', 500);
		$this->setSetting('dealer', 'map_type', 'ROADMAP');
		$this->setSetting('dealer', 'distance', 25);
		$this->setSetting('dealer', 'limit', 50);
		$this->setSetting('dealer', 'units', 'KM');

		// widget settings
		$this->setSetting('dealer', 'zoom_level_widget', 13);
		$this->setSetting('dealer', 'width_widget', 400);
		$this->setSetting('dealer', 'height_widget', 300);
		$this->setSetting('dealer', 'map_type_widget', 'ROADMAP');

		// action rights
		$this->setActionRights(1, 'dealer', 'add');
		$this->setActionRights(1, 'dealer', 'add_brands');
		$this->setActionRights(1, 'dealer', 'delete');
		$this->setActionRights(1, 'dealer', 'delete_brand');
		$this->setActionRights(1, 'dealer', 'edit');
		$this->setActionRights(1, 'dealer', 'edit_brands');
		$this->setActionRights(1, 'dealer', 'index');
		$this->setActionRights(1, 'dealer', 'brands');
		$this->setActionRights(1, 'dealer', 'settings');

		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$navigationDealerId = $this->setNavigation($navigationModulesId, 'Dealer');
		$this->setNavigation($navigationDealerId, 'Dealer', 'dealer/index', array('dealer/add', 'dealer/edit'));
		$this->setNavigation($navigationDealerId, 'Brands', 'dealer/brands', array('dealer/add_brands', 'dealer/edit_brands'));

		// settings navigation
		$navigationSettingsId = $this->setNavigation(null, 'Settings');
		$navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
		$this->setNavigation($navigationModulesId, 'Dealer', 'dealer/settings');

		// create directory for the original files
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/');

		// create directory + folder for the dealer avatars
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/banners/avatars/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/16x16/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/avatars/16x16/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/24x24/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/avatars/24x24/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/32x32/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/avatars/32x32/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/64x64/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/avatars/64x64/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/128x128/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/avatars/128x128/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/256x256/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/avatars/256x256/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/avatars/source/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/avatars/source/');

		// create directory + folder for the brand images
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/16x16/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/16x16/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/24x24/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/24x24/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/32x32/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/32x32/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/64x64/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/64x64/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/128x128/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/128x128/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/256x256/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/256x256/');
		if(!SpoonDirectory::exists(PATH_WWW . '/frontend/files/dealer/brands/source/')) SpoonDirectory::create(PATH_WWW . '/frontend/files/dealer/brands/source/');

		// add extra's
		$this->insertExtra('dealer', 'block', 'Dealer', null, 'a:1:{s:3:"url";s:35:"/private/nl/dealer/index?token=true";}', 'N');
	}
}
