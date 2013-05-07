<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Edit a dealer locator.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerEdit extends BackendBaseActionEdit
{
	/**
	 * Execute the action.
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the dealer exist
		if($this->id !== null && BackendDealerModel::existsDealer($this->id))
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get data
			$this->getData();

			// load form
			$this->loadForm();

			// validate form
			$this->validateForm();

			// parse
			$this->parse();

			// display
			$this->display();
		}

		// no dealer found
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data.
	 */
	private function getData()
	{
		$this->record = BackendDealerModel::getDealer($this->id);
		$this->dealerBrands = BackendDealerModel::getDealerBrands($this->id);
		$this->brands = BackendDealerModel::getAllBrands();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');

		// set hidden values
		$rbtHiddenValues[] = array('label' => BL::lbl('Hidden'), 'value' => 'Y');
		$rbtHiddenValues[] = array('label' => BL::lbl('Published'), 'value' => 'N');

		// check if dealers has brands
		if(!empty($this->brands))
		{
			// init some vars
			$checked = array();
			$brandIds = array();

			// get brand ids and put them in an array
			foreach($this->brands as $value)
			{
				$brandIds[] = array('label' => $value['name'], 'value' => $value['id']);
			}

			foreach($this->dealerBrands as $value)
			{
				$checked[] = $value['brand_id'];
			}

			// create chekcboxes
			$this->frm->addMultiCheckbox('type', $brandIds, $checked);
		}

		$this->frm->addText('name', $this->record['name'], 255, 'inputText title', 'inputTextError, title');
		$this->frm->addRadiobutton('hidden', $rbtHiddenValues, $this->record['hidden']);
		$this->frm->addText('street', $this->record['street']);
		$this->frm->addText('number', $this->record['number']);
		$this->frm->addText('zip', $this->record['zip']);
		$this->frm->addText('city', $this->record['city']);
		$this->frm->addDropdown('country', SpoonLocale::getCountries(BL::getInterfaceLanguage()), $this->record['country']);
		$this->frm->addText('tel', $this->record['tel']);
		$this->frm->addText('fax', $this->record['fax']);
		$this->frm->addText('email', $this->record['email']);
		$this->frm->addText('website', $this->record['website']);
		$this->frm->addImage('avatar');

		// meta object
		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'name', true);

		// set callback for generating a unique URL
		$this->meta->setUrlCallback('BackendDealerModel', 'getURL', array($this->record['id']));
	}

	/**
	 * Parse the form.
	 */
	protected function parse()
	{
		parent::parse();

		// get url
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'locator');
		$url404 = BackendModel::getURL(404);

		// parse additional variables
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);

		// fetch proper slug
		$this->record['url'] = $this->meta->getURL();

		// assign the active record and additional variables
		$this->tpl->assign('item', $this->record);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('name')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('street')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('number')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('zip')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('city')->isFilled(BL::err('FieldIsRequired'));

			// validate email
			if($this->frm->getField('email')->isFilled())
			{
				$this->frm->getField('email')->isEmail(BL::err('NoValidEmail'));
			}

			// validate avatar
			if($this->frm->getField('avatar')->isFilled())
			{
				// correct extension
				if($this->frm->getField('avatar')->isAllowedExtension(array('jpg', 'jpeg', 'gif', 'png'), BL::err('JPGGIFAndPNGOnly')))
				{
					// correct mimetype?
					$this->frm->getField('avatar')->isAllowedMimeType(array('image/gif', 'image/jpg', 'image/jpeg', 'image/png'), BL::err('JPGGIFAndPNGOnly'));
				}
			}

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['name'] = $this->frm->getField('name')->getValue();
				$item['meta_id'] = $this->meta->save();
				$item['extra_id'] = $this->record['extra_id'];
				$item['street'] = $this->frm->getField('street')->getValue();
				$item['number'] = $this->frm->getField('number')->getValue();
				$item['zip'] = $this->frm->getField('zip')->getValue();
				$item['city'] = $this->frm->getField('city')->getValue();
				$item['country'] = $this->frm->getField('country')->getValue();
				$item['tel'] = $this->frm->getField('tel')->getValue();
				$item['fax'] = $this->frm->getField('fax')->getValue();
				$item['email'] = $this->frm->getField('email')->getValue();
				$item['website'] = $this->frm->getField('website')->getValue();
				$item['hidden'] = $this->frm->getField('hidden')->getValue();
				$item['language'] = BackendLanguage::getWorkingLanguage();

				// create array item with all brands in
				$values = array();
				foreach($this->brands as $value)
				{
					// if checkbox is checked save id in array values
					if(in_array($value['id'], (array) $this->frm->getField('type')->getValue())) $values[] = $value['id'];
				}

				// has the user submitted an avatar?
				if($this->frm->getField('avatar')->isFilled())
				{
					// add into items to update
					$item['avatar'] = $this->meta->getURL() . '.' . $this->frm->getField('avatar')->getExtension();

					// loop upload directory
					foreach(SpoonDirectory::getList(FRONTEND_FILES_PATH . '/dealer/avatars/') as $value)
					{
						if($value !== 'source')
						{
							// get width and height
							list($width, $height) = split('x', $value);

							// delete old avatar
							SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/avatars/' . $width . 'x' . $height . '/' . $this->record['avatar']);

							// resize avatar
							$this->frm->getField('avatar')->createThumbnail(FRONTEND_FILES_PATH . '/dealer/avatars/' . $width . 'x' . $height . '/' . $this->meta->getURL() . '.' . $this->frm->getField('avatar')->getExtension(), $width, $height, true, false, 100);
						}
						else
						{
							SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/avatars/source/' . $this->record['avatar']);
						}
					}
				}

				// geocode address
				$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($item['street'] . ' ' . $item['number'] . ', ' . $item['zip'] . ' ' . $item['city'] . ', ' . SpoonLocale::getCountry($item['country'], BL::getWorkingLanguage())) . '&sensor=false';
				$geocode = json_decode(SpoonHTTP::getContent($url));
				$item['lat'] = isset($geocode->results[0]->geometry->location->lat) ? $geocode->results[0]->geometry->location->lat : null;
				$item['lng'] = isset($geocode->results[0]->geometry->location->lng) ? $geocode->results[0]->geometry->location->lng : null;

				// update the dealer
				BackendDealerModel::updateDealer($item);
				BackendDealerModel::updateBrandsForDealer($item['id'], $values);

				// edit search index
				BackendSearchModel::saveIndex($this->getModule(), $item['id'], array('title' => $item['name'], 'text' => $item['name']));

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit', array('item' => $item));

				// everything has been saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') . '&report=edited&var=' . urlencode($item['name']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
