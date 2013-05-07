<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Display a form to create a new brand.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerAddBrands extends BackendBaseActionAdd
{
	public function execute()
	{
		parent::execute();

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
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('add');

		// create elements
		$this->frm->addText('name', null, 255, 'inputText title', 'inputTextError, title');
		$this->frm->addImage('image');

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

			// validate image
			if($this->frm->getField('image')->isFilled())
			{
				// correct extension
				if($this->frm->getField('image')->isAllowedExtension(array('jpg', 'jpeg', 'gif', 'png'), BL::err('JPGGIFAndPNGOnly')))
				{
					// correct mimetype?
					$this->frm->getField('image')->isAllowedMimeType(array('image/gif', 'image/jpg', 'image/jpeg', 'image/png'), BL::err('JPGGIFAndPNGOnly'));
				}
			}

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['meta_id'] = $this->meta->save();
				$item['name'] = $this->frm->getField('name')->getValue();
				$item['language'] = BackendLanguage::getWorkingLanguage();

				// has the user submitted an image?
				if($this->frm->getField('image')->isFilled())
				{
					// add into items to update
					$item['image'] = rand(0,1000) . '-' . SpoonFilter::urlise($item['name']) . '.' . $this->frm->getField('image')->getExtension();

					// loop upload directory
					foreach(SpoonDirectory::getList(FRONTEND_FILES_PATH . '/dealer/brands/') as $value)
					{
						if($value !== 'source')
						{
							list($width, $height) = split('x', $value);

							// resize avatar
							$this->frm->getField('image')->createThumbnail(FRONTEND_FILES_PATH . '/dealer/brands/' . $width . 'x' . $height . '/' . $item['image'], $width, $height, true, false, 100);
						}
					}
				}

				// insert the item
				$item['id'] = BackendDealerModel::insertBrand($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_add_brand', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('brands') . '&report=added&var=' . urlencode($item['name']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
