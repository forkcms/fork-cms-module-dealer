<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Display brands overview
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerBrands extends BackendBaseActionIndex
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
		$this->datagrid = new BackendDataGridDB(BackendDealerModel::QRY_BROWSE_BRANDS, array(BackendLanguage::getWorkingLanguage()));

		// hide the 'id' column
		$this->datagrid->setColumnHidden('id');
		$this->datagrid->setColumnHidden('name');
		$this->datagrid->setColumnHidden('image');

		// build html for showing brand image in datagrid
		$html = '<div class="dataGridAvatar">' . "\n";
		$html .= '	<div class="avatar av24">' . "\n";
		$html .= '			<img src="' . FRONTEND_FILES_URL . '/dealer/brands/32x32/' . $this->datagrid->getColumn('image')->getValue() . '" width="24" height="24"  />' . "\n";
		$html .= '	</div>';
		$html .= '	<p>' . $this->datagrid->getColumn('name')->getValue() . '</a>' . "\n";
		$html .= '</div>';

		$this->datagrid->addColumn('brand', 'Brand', $html);

		// linkify the name column
		$this->datagrid->setColumnURL('brand', BackendModel::createURLForAction('edit_brands') . '&amp;id=[id]');

		// create the "edit" button for each row
		$this->datagrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_brands') . '&amp;id=[id]', BackendLanguage::getLabel('Edit'));
	}

	/**
	 * Parse the data grid.
	 */
	protected function parse()
	{
		$this->tpl->assign('datagrid', $this->datagrid->getNumResults() ? $this->datagrid->getContent() : false);
	}
}
