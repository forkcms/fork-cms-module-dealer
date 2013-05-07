<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Detail locator page, show locator details with brands.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class FrontendDealerLocator extends FrontendBaseBlock
{
	/**
	 * Execute the extra.
	 */
	public function execute()
	{
		parent::execute();

		$this->getData();
		$this->loadTemplate();
		$this->parse();
	}

	/**
	 * Get the data.
	 */
	private function getData()
	{
		// validate incoming parameters
		if($this->URL->getParameter(1) === null) $this->redirect(FrontendNavigation::getURL(404));

		// get by URL
		$this->record = FrontendDealerModel::get($this->URL->getParameter(1));

		// anything found?
		if(empty($this->record)) $this->redirect(FrontendNavigation::getURL(404));
	}

	/**
	 * Parse the data and compile the template.
	 */
	private function parse()
	{
		// load css
		$this->header->addCSS('/frontend/modules/' . $this->getModule() . '/layout/css/dealer.css');

		// add into breadcrumb
		$this->breadcrumb->addElement($this->record['name']);

		// parse records and settings
		$this->tpl->assign('dealerItem', $this->record);
		$this->tpl->assign('dealerSettings', FrontendModel::getModuleSettings('dealer'));
	}
}
