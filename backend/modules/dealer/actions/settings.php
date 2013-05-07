<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the settings-action, it will display a form to set general dealer locator / widget map settings
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerSettings extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}

	/**
	 * Loads the settings form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('settings');

		// add map info (overview map)
		$this->frm->addDropdown('zoom_level', array_combine(array_merge(array('auto'), range(3, 18)), array_merge(array(BL::lbl('Auto', $this->getModule())), range(3, 18))), BackendModel::getModuleSetting($this->URL->getModule(), 'zoom_level', 'auto'));
		$this->frm->addText('width', BackendModel::getModuleSetting($this->URL->getModule(), 'width'));
		$this->frm->addText('height', BackendModel::getModuleSetting($this->URL->getModule(), 'height'));
		$this->frm->addDropdown('map_type', array('ROADMAP' => BL::lbl('Roadmap', $this->getModule()), 'SATELLITE' => BL::lbl('Satellite', $this->getModule()), 'HYBRID' => BL::lbl('Hybrid', $this->getModule()), 'TERRAIN' => BL::lbl('Terrain', $this->getModule())), BackendModel::getModuleSetting($this->URL->getModule(), 'map_type', 'roadmap'));
		$this->frm->addDropdown('units', array('KM' => BL::lbl('Km', $this->getModule()), 'MILES' => BL::lbl('Miles', $this->getModule())), BackendModel::getModuleSetting($this->URL->getModule(), 'units', 'KM'));
		$this->frm->addText('distance', BackendModel::getModuleSetting($this->URL->getModule(), 'distance'));
		$this->frm->addText('limit', BackendModel::getModuleSetting($this->URL->getModule(), 'limit'));

		// add map info (widgets)
		$this->frm->addDropdown('zoom_level_widget', array_combine(array_merge(array('auto'), range(3, 18)), array_merge(array(BL::lbl('Auto', $this->getModule())), range(3, 18))), BackendModel::getModuleSetting($this->URL->getModule(), 'zoom_level_widget', 13));
		$this->frm->addText('width_widget', BackendModel::getModuleSetting($this->URL->getModule(), 'width_widget'));
		$this->frm->addText('height_widget', BackendModel::getModuleSetting($this->URL->getModule(), 'height_widget'));
		$this->frm->addDropdown('map_type_widget', array('ROADMAP' => BL::lbl('Roadmap', $this->getModule()), 'SATELLITE' => BL::lbl('Satellite', $this->getModule()), 'HYBRID' => BL::lbl('Hybrid', $this->getModule()), 'TERRAIN' => BL::lbl('Terrain', $this->getModule())), BackendModel::getModuleSetting($this->URL->getModule(), 'map_type_widget', 'roadmap'));
	}

	/**
	 * Validates the settings form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();
			$this->frm->getField('distance')->isDigital(BL::err('NotNumeric'));;
			$this->frm->getField('limit')->isDigital(BL::err('NotNumeric'));;
			$this->frm->getField('width')->isDigital(BL::err('NotNumeric'));;
			$this->frm->getField('height')->isDigital(BL::err('NotNumeric'));;

			if($this->frm->isCorrect())
			{
				// set our settings (overview map)
				BackendModel::setModuleSetting($this->URL->getModule(), 'zoom_level', (string) $this->frm->getField('zoom_level')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'width', (int) $this->frm->getField('width')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'height', (int) $this->frm->getField('height')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'map_type', (string) $this->frm->getField('map_type')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'distance', (string) $this->frm->getField('distance')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'units', (string) $this->frm->getField('units')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'limit', (string) $this->frm->getField('limit')->getValue());

				// set our settings (widgets)
				BackendModel::setModuleSetting($this->URL->getModule(), 'zoom_level_widget', (string) $this->frm->getField('zoom_level_widget')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'width_widget', (int) $this->frm->getField('width_widget')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'height_widget', (int) $this->frm->getField('height_widget')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'map_type_widget', (string) $this->frm->getField('map_type_widget')->getValue());

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_saved_settings');

				// redirect to the settings page
				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}
