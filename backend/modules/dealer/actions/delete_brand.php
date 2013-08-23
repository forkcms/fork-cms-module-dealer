<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Delete a brand.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerDeleteBrand extends BackendBaseActionDelete
{
	/**
	 * Execute the current action.
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendDealerModel::existsBrand($this->id))
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get the current brand
			$this->record = BackendDealerModel::getBrand($this->id);

			// delete it
			BackendDealerModel::deleteBrand($this->id);

			// trigger event
			BackendModel::triggerEvent($this->getModule(), 'after_delete_brand', array('item' => $this->record));

			// redirect back to the index
			$this->redirect(BackendModel::createURLForAction('brands') . '&report=deleted&var=' . urlencode($this->record['name']));
		}

		// no dealer found
		else $this->redirect(BackendModel::createURLForAction('brands') . '&error=non-existing');
	}
}
