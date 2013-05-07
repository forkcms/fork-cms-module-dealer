<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Detail brand page, showing all dealers with selected brand
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class FrontendDealerBrand extends FrontendBaseBlock
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
		$this->record = FrontendDealerModel::getBrandInfo($this->URL->getParameter(1));

		// anything found?
		if(empty($this->record)) $this->redirect(FrontendNavigation::getURL(404));

		// get all dealers with brand id
		$this->dealers = FrontendDealerModel::getBrandDealers($this->record['id']);
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
		$this->tpl->assign('brandItem', $this->record);

		// check of there are dealers
		if(count($this->dealers) > 0)
		{
			// assign dealers items and area
			$this->tpl->assign('dealerItems', $this->dealers);
			$this->tpl->assign('dealerHeadingText', FL::msg('NumDealersFound'));
			$this->tpl->assign('numDealers', count($this->dealers));
		}

		$this->tpl->assign('dealerSettings', FrontendModel::getModuleSettings('dealer'));
	}
}
