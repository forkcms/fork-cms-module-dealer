<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Delete a dealer locator.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerDelete extends BackendBaseActionDelete
{
	/**
	 * Execute the current action.
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendDealerModel::existsDealer($this->id))
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get the current dealer
			$this->record = BackendDealerModel::getDealer($this->id);

			// delete it
			BackendDealerModel::deleteDealer($this->id);

			// trigger event
			BackendModel::triggerEvent($this->getModule(), 'after_delete', array('item' => $this->record));

			// redirect back to the index
			$this->redirect(BackendModel::createURLForAction('index') . '&report=deleted&var=' . urlencode($this->record['name']));
		}

		// no dealer found
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}
}
