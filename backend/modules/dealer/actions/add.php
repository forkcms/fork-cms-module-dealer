<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Display a form to create a new dealer locator.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerAdd extends BackendBaseActionAdd
{
	public function execute()
	{
		parent::execute();

		// get data
		$this->getData();

		// load form
		$this->loadForm();

		// validate the form
		$this->validateForm();

		// parse the datagrid
		$this->parse();

		// display the page
		$this->display();
	}

	/**
	 * Get the data.
	 */
	private function getData()
	{
		$this->brands = BackendDealerModel::getAllBrands();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('add');

		// set hidden values
		$rbtHiddenValues[] = array('label' => BL::lbl('Hidden', $this->URL->getModule()), 'value' => 'Y');
		$rbtHiddenValues[] = array('label' => BL::lbl('Published'), 'value' => 'N');

		if(!empty($this->brands))
		{
			// init some vars
			$checked = array();
			$values = array();

			// get brand ids and put them in an array
			foreach($this->brands as $value)
			{
				$values[] = array('label' => $value['name'], 'value' => $value['id']);
			}
			// create chekcboxes
			$this->frm->addMultiCheckbox('type', $values, $checked);
		}

		// create elements
		$this->frm->addText('name', null, 255, 'inputText title', 'inputTextError, title');
		$this->frm->addRadiobutton('hidden', $rbtHiddenValues, 'N');
		$this->frm->addText('street');
		$this->frm->addText('number');
		$this->frm->addText('zip');
		$this->frm->addText('city');
		$this->frm->addDropdown('country', SpoonLocale::getCountries(BL::getInterfaceLanguage()), 'BE');
		$this->frm->addText('tel');
		$this->frm->addText('fax');
		$this->frm->addText('email');
		$this->frm->addText('website');
		$this->frm->addImage('avatar');

		// meta
		$this->meta = new BackendMeta($this->frm, null, 'name', true);
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
			$this->frm->getField('name')->isFilled(BL::err('NameIsRequired'));
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

			// validate meta
			$this->meta->validate();

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['meta_id'] = $this->meta->save();
				$item['name'] = $this->frm->getField('name')->getValue();
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
							list($width, $height) = split('x', $value);

							// resize avatar
							$this->frm->getField('avatar')->createThumbnail(FRONTEND_FILES_PATH . '/dealer/avatars/' . $width . 'x' . $height . '/' . $this->meta->getURL() . '.' . $this->frm->getField('avatar')->getExtension(), $width, $height, true, false, 100);
						}
					}
				}

				// geocode address
				$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($item['street'] . ' ' . $item['number'] . ', ' . $item['zip'] . ' ' . $item['city'] . ', ' . SpoonLocale::getCountry($item['country'], BL::getWorkingLanguage())) . '&sensor=false';
				$geocode = json_decode(SpoonHTTP::getContent($url));
				$item['lat'] = isset($geocode->results[0]->geometry->location->lat) ? $geocode->results[0]->geometry->location->lat : null;
				$item['lng'] = isset($geocode->results[0]->geometry->location->lng) ? $geocode->results[0]->geometry->location->lng : null;

				// insert the item
				$item['id'] = BackendDealerModel::insertDealer($item);
				BackendDealerModel::updateBrandsForDealer($item['id'], $values);

				// add search index
				BackendSearchModel::saveIndex($this->getModule(), $item['id'], array('title' => $item['name'], 'text' => $item['name']));

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_add', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') . '&report=added&var=' . urlencode($item['name']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
