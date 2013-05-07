<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Search form with brands and on submit showing dealer locaters
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class FrontendDealerIndex extends FrontendBaseBlock
{
	/**
	 * Execute the extra.
	 */
	public function execute()
	{
		parent::execute();

		$this->getData();
		$this->loadTemplate();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
	}

	/**
	 * Get the data.
	 */
	private function getData()
	{
		$this->brands = FrontendDealerModel::getAllBrands();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new FrontendForm('searchForm');
		$this->frm->setAction($this->frm->getAction());

		// init some vars
		$values = array();

		if(!empty($this->brands))
		{
			// get brand ids and put them in an array
			foreach($this->brands as $value)
			{
				$values[] = array('label' => $value['name'], 'value' => $value['id']);
			}

			// create multi checkboxes
			$this->frm->addMultiCheckbox('type', $values);
		}

		// create elements
		$this->frm->addText('area');
		$this->frm->addDropdown('country', array('AROUND' => FL::lbl('TheClosestTo'), 'BE' => FL::lbl('InBelgium'), 'NL' => FL::lbl('InNetherlands'), 'FR' => FL::lbl('InFrance')));
	}

	/**
	 * Parse the data and compile the template.
	 */
	private function parse()
	{
		// load css style
		$this->header->addCSS('/frontend/modules/' . $this->getModule() . '/layout/css/dealer.css');

		// parse the form
		$this->frm->parse($this->tpl);
		$this->tpl->assign('dealerSettings', FrontendModel::getModuleSettings('dealer'));

		// get url parameters
		$area = $this->URL->getParameter('city');
		$country = $this->URL->getParameter('country');
		$brands = $this->URL->getParameter('brands');
		if($brands != null) $brands = explode('-', $this->URL->getParameter('brands'));

		// check if city and country isn't null
		if($this->URL->getParameter('city') != null AND $this->URL->getParameter('city') != null)
		{
			$getDealers = FrontendDealerModel::getAll($area, $brands, $country);

			// check of there are dealers
			if(count($getDealers) > 0)
			{
				// assign dealers items and area
				$this->tpl->assign('dealerArea', $area);
				$this->tpl->assign('dealerItems', $getDealers);
				$this->tpl->assign('dealerHeadingText', FL::msg('NumDealersFound'));
				$this->tpl->assign('numDealers', count($getDealers));
			}
			else
			{
				$this->tpl->assign('dealerErrorNoDealers', true);
			}
		}
	}

	/**
	 * Validate form
	 */
	private function validateForm()
	{
		// is the form submitted
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate required fields
			$this->frm->getField('area')->isFilled(FL::err('AreaIsRequired'));

			// get input values
			$area = $this->frm->getField('area')->getValue();
			$country = $this->frm->getField('country')->getValue();

			// no errors?
			if($this->frm->isCorrect())
			{
				// create array item with all brands in
				$brands = array();

				foreach($this->brands as $brand)
				{
					// if checkbox is checked save id in array values
					if(in_array($brand['id'], (array) $this->frm->getField('type')->getValue())) $brands[] = $brand['id'];
				}

				$this->redirect(FrontendNavigation::getURLForBlock('dealer', '') . '?city=' . $area . '&country=' . $country . '&brands=' . implode('-', $brands));

			}
		}
	}
}
