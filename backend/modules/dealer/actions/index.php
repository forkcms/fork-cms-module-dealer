<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Display dealer locater overview
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerIndex extends BackendBaseActionIndex
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// load the data grid
		$this->loadDataGrid();

		// parse the data grid
		$this->parse();

		// display the page
		$this->display();
	}

	/**
	 * Load the data grid.
	 */
	protected function loadDataGrid()
	{
		// create the data grid for the overview
		$this->datagrid = new BackendDataGridDB(BackendDealerModel::QRY_BROWSE, array(BackendLanguage::getWorkingLanguage()));

		$this->datagrid->setColumnHidden('id');
		$this->datagrid->setColumnHidden('name');
		$this->datagrid->setColumnHidden('avatar');

		// build html for showing dealer locator image in datagrid
		$html = '<div class="dataGridAvatar">' . "\n";
		$html .= '	<div class="avatar av24">' . "\n";
		$html .= '			<img src="' . FRONTEND_FILES_URL . '/dealer/avatars/32x32/' . $this->datagrid->getColumn('avatar')->getValue() . '" width="24" height="24"  />' . "\n";
		$html .= '	</div>';
		$html .= '	<p>' . $this->datagrid->getColumn('name')->getValue() . '</a>' . "\n";
		$html .= '</div>';

		$this->datagrid->addColumn('dealer', 'Dealer', $html);

		// linkify the name column
		$this->datagrid->setColumnURL('dealer', BackendModel::createURLForAction('edit') . '&amp;id=[id]');

		// create the "edit" button for each row
		$this->datagrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]', BackendLanguage::getLabel('Edit'));
	}

	/**
	 * Parse the data grid.
	 */
	protected function parse()
	{
		$this->tpl->assign('datagrid', $this->datagrid->getNumResults() ? $this->datagrid->getContent() : false);
	}
}
