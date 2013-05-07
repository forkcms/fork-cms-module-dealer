<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Edit a brand.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerEditBrands extends BackendBaseActionEdit
{
	/**
	 * Execute the action.
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the dealer locater exist
		if($this->id !== null && BackendDealerModel::existsBrand($this->id))
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

		// no brands found
		else $this->redirect(BackendModel::createURLForAction('brands') . '&error=non-existing');
	}

	/**
	 * Get the data.
	 */
	private function getData()
	{
		$this->record = BackendDealerModel::getBrand($this->id);
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

		// create elements
		$this->frm->addText('name', $this->record['name'], 255, 'inputText title', 'inputTextError, title');
		$this->frm->addImage('image');

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
		// call parent
		parent::parse();

		// get url
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'brand');
		$url404 = BackendModel::getURL(404);

		// parse additional variables
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);

		// fetch proper slug
		$this->record['url'] = $this->meta->getURL();
		// assign fields
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
				$item['id'] = $this->id;
				$item['name'] = $this->frm->getField('name')->getValue();
				$item['meta_id'] = $this->meta->save();

				// has the user submitted an image?
				if($this->frm->getField('image')->isFilled())
				{
					// add into items to update
					$item['image'] = $this->meta->getURL() . '.' . $this->frm->getField('image')->getExtension();

					// loop upload directory
					foreach(SpoonDirectory::getList(FRONTEND_FILES_PATH . '/dealer/brands/') as $value)
					{
						if($value !== 'source')
						{
							// get width and height
							list($width, $height) = split('x', $value);

							// delete old avatar
							SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/brands/' . $width . 'x' . $height . '/' . $this->record['image']);

							// resize avatar
							$this->frm->getField('image')->createThumbnail(FRONTEND_FILES_PATH . '/dealer/brands/' . $width . 'x' . $height . '/' . $this->meta->getURL() . '.' . $this->frm->getField('image')->getExtension(), $width, $height, true, false, 100);
						}
						else
						{
							SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/brands/source/' . $this->record['image']);
						}
					}
				}

				// update the dealer
				BackendDealerModel::updateBrand($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_brand', array('item' => $item));

				// everything has been saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('brands') . '&report=edited&var=' . urlencode($item['name']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
