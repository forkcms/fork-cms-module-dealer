<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * All model functions for the dealer locater module.
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class BackendDealerModel
{
	/**
	 * Overview of the dealer locaters.
	 *
	 * @var	string
	 */
	const QRY_BROWSE =
		'SELECT id, name, avatar, CONCAT(street, " ", number, ", ", zip, " ", city, ", ", country) AS address
	     FROM dealer
	     WHERE language = ?';

	/**
	 * Overview of the brands.
	 *
	 * @var	string
	 */
	const QRY_BROWSE_BRANDS =
		'SELECT id, name, image
		 FROM dealer_brands
		 WHERE language = ?';

	/**
	 * Delete a brand.
	 *
	 * @param int $id 		The id of the dealer locater to delete.
	 */
	public static function deleteBrand($id)
	{
		// get image file name
		$imageFilname = (string) BackendModel::getContainer()->get('database')->getVar(
			'SELECT image
			 FROM dealer_brands
			 WHERE id = ?',
			array((int) $id)
		);

		// loop upload directory
		foreach(SpoonDirectory::getList(FRONTEND_FILES_PATH . '/dealer/brands/') as $value)
		{
			if($value !== 'source')
			{
				list($width, $height ) = split('x', $value);
				// delete images
				SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/brands/' . $width . 'x' . $height . '/' . $imageFilname);
			}
		}

		// delete source images
		SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/brands/source/' . $imageFilname);

		// delete brand
		BackendModel::getContainer()->get('database')->delete('dealer_brands', 'id = ?', array((int) $id));
	}

	/**
	 * Delete a dealer.
	 *
	 * @param int $id The id of the dealer locater to delete.
	 */
	public static function deleteDealer($id)
	{
		// get db
		$db = BackendModel::getContainer()->get('database');
	
		// get avatar file name
		$imageFilname = (string) $db->getVar(
			'SELECT avatar
			 FROM dealer
			 WHERE id = ?',
			array((int) $id)
		);

		// loop upload directory
		foreach(SpoonDirectory::getList(FRONTEND_FILES_PATH . '/dealer/avatars/') as $value)
		{
			if($value !== 'source')
			{
				list($width, $height ) = preg_split('x', $value);
				// delete images
				SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/avatars/' . $width . 'x' . $height . '/' . $imageFilname);
			}
		}

		// delete source images
		SpoonFile::delete(FRONTEND_FILES_PATH . '/dealer/avatars/source/' . $imageFilname);

		// get extra_id of deleted dealer
		$extraId = (string) $db->getVar(
			'SELECT extra_id
			 FROM dealer
			 WHERE id = ?',
			array((int) $id)
		);

		// delete extra
		$db->delete('modules_extras', 'id = ? AND module = ? AND type = ? AND action = ?', array($extraId, 'dealer', 'widget', 'dealer'));

		// delete dealer
		$db->delete('dealer', 'id = ?', array((int) $id));
	}

	/**
	 * Does the brand exist?
	 *
	 * @param	int $id		The id of the brand to check for existence.
	 * @return bool
	 */
	public static function existsBrand($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT COUNT(id)
			 FROM dealer_brands
			 WHERE id = ?',
			array((int) $id)
		);
	}

	/**
	 * Does the dealer locater exist?
	 *
	 * @param int $id The id of the dealer to check for existence.
	 * @return bool
	 */
	public static function existsDealer($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT COUNT(id)
			 FROM dealer
			 WHERE id = ?',
			array((int) $id)
		);
	}

	/**
	 * Get all the brands.
	 *
	 * @return array
	 */
	public static function getAllBrands()
	{
		return (array) BackendModel::getContainer()->get('database')->getRecords(
			'SELECT *
			 FROM dealer_brands
			 WHERE language = ?',
			array(BL::getWorkingLanguage())
		);
	}

	/**
	 * Get all data for the brand with the given ID.
	 *
	 * @param int $id The id for the dealer locater to get.
	 * @return array
	 */
	public static function getBrand($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT *
			 FROM dealer_brands
			 WHERE id = ?
			 LIMIT 1',
			array((int) $id)
		);
	}

	/**
	 * Get all data for the one dealer locater with the given ID.
	 *
	 * @param int $id The id for the dealer locater to get.
	 * @return array
	 */
	public static function getDealer($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*, m.url
			 FROM dealer AS i
			 INNER JOIN meta AS m ON m.id = i.meta_id
			 WHERE i.id = ?
			 LIMIT 1',
			array((int) $id)
		);
	}

	/**
	 * Get all data for the brands with the given dealer ID.
	 *
	 * @param int $id The id of the dealer locator for getting the brands
	 * @return array
	 */
	public static function getDealerBrands($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecords(
			'SELECT brand_id
			 FROM dealer_index
			 WHERE dealer_id = ?',
			array((int) $id)
		);
	}

	/**
	 * Retrieve the unique URL for an item
	 *
	 * @param string $URL The URL to base on.
	 * @param int[optional] $id The id of the item to ignore.
	 * @return string
	 */
	public static function getURL($URL, $id = null)
	{
		$URL = (string) $URL;

		// get db
		$db = BackendModel::getContainer()->get('database');

		// new item
		if($id === null)
		{
			// get number of categories with this URL
			$number = (int) $db->getVar(
				'SELECT COUNT(i.id)
				 FROM dealer AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?',
				array(BL::getWorkingLanguage(), $URL)
			);

			// already exists
			if($number != 0)
			{
				$URL = BackendModel::addNumber($URL);
				return self::getURL($URL);
			}
		}

		// current category should be excluded
		else
		{
			// get number of items with this URL
			$number = (int) $db->getVar(
				'SELECT COUNT(i.id)
				 FROM dealer AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?',
				array(BL::getWorkingLanguage(), $URL, $id)
			);

			// already exists
			if($number != 0)
			{
				$URL = BackendModel::addNumber($URL);
				return self::getURL($URL, $id);
			}
		}

		return $URL;
	}

	/**
	 * Add a new dealer locater.
	 *
	 * @param array $item The data to insert.
	 * @return int The ID of the newly inserted dealer locater.
	 */
	public static function insertDealer(array $item)
	{
		// get db
		$db = BackendModel::getContainer()->get('database');
	
		// build extra
		$extra = array(
			'module' => 'dealer',
			'type' => 'widget',
			'label' => 'Dealer',
			'action' => 'dealer',
			'data' => null,
			'hidden' => 'N'
		);

		// insert extra
		$item['extra_id'] = $db->insert('modules_extras', $extra);
		$extra['id'] = $item['extra_id'];

		// insert and return the new id
		$item['id'] = $db->insert('dealer', $item);

		// update extra (item id is now known)
		$extra['data'] = serialize(array(
			'id' => $item['id'],
			'extra_label' => SpoonFilter::ucfirst(BL::lbl('Dealer', 'core')) . ': ' . $item['name'],
			'language' => $item['language'],
			'edit_url' => BackendModel::createURLForAction('edit') . '&id=' . $item['id'])
		);
		
		$db->update('modules_extras', $extra, 'id = ? AND module = ? AND type = ? AND action = ?', array($extra['id'], $extra['module'], $extra['type'], $extra['action']));

		return $item['id'];
	}

	/**
	 * Add a new brand.
	 *
	 * @param array $item 	The data to insert.
	 * @return int 			The ID of the newly inserted brand.
	 */
	public static function insertBrand(array $item)
	{
		return BackendModel::getContainer()->get('database')->insert('dealer_brands', $item);
	}

	/**
	 * Update an existing dealer locater.
	 *
	 * @param array $item 	The new data.
	 * @return int			The ID of the newly updated dealer locator.
	 */
	public static function updateDealer(array $item)
	{
		// build extra
		$extra = array(
			'id' => $item['extra_id'],
			'module' => 'dealer',
			'type' => 'widget',
			'label' => 'Dealer',
			'action' => 'dealer',
			'data' => serialize(array(
					'id' => $item['id'],
					'extra_label' => SpoonFilter::ucfirst(BL::lbl('Dealer', 'core')) . ': ' . $item['name'],
					'language' => $item['language'],
					'edit_url' => BackendModel::createURLForAction('edit') . '&id=' . $item['id'])
			),
			'hidden' => 'N'
		);

		// update extra
		BackendModel::getContainer()->get('database')->update('modules_extras', $extra, 'id = ? AND module = ? AND type = ? AND action = ?', array($extra['id'], $extra['module'], $extra['type'], $extra['action']));

		return BackendModel::getContainer()->get('database')->update('dealer', $item, 'id = ?', array((int) $item['id']));
	}

	/**
	 * Update an existing brand.
	 *
	 * @param array $item The new data.
	 * @return int
	 */
	public static function updateBrand(array $item)
	{
		return BackendModel::getContainer()->get('database')->update('dealer_brands', $item, 'id = ?', array((int) $item['id']));
	}

	/**
	 * Update brand index for dealer
	 *
	 * @param   int $id         The dealer id
	 * @param   array $brands 	Array with all dealer brands
	 * @return void
	 */
	public static function updateBrandsForDealer($id, $brands)
	{
		BackendModel::getContainer()->get('database')->delete('dealer_index', 'dealer_id = ?', array((int) $id));

		$brands = (array) $brands;

		foreach($brands as $brand)
		{
			BackendModel::getContainer()->get('database')->insert('dealer_index', array('dealer_id' => $id, 'brand_id' => $brand));
		}
	}
}
