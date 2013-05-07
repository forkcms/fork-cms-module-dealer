<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the dealer-widget: 1 specific map / address
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class FrontendDealerWidgetDealer extends FrontendBaseWidget
{
	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();
		$this->loadTemplate();
		$this->parse();
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		// show message
		$this->tpl->assign('widgetDealerItem', FrontendDealerModel::getDealer((int) $this->data['id']));

		// hide form
		$this->tpl->assign('widgetDealerSettings', FrontendModel::getModuleSettings('location'));
	}
}
