<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the dealer locator module
 *
 * @author Arend Pijls <arend.pijls@wijs.be>
 */
class FrontendDealerModel
{
	/**
	 * Get an dealer locator by url
	 *
	 * @param string $URL The URL for the item.
	 * @return array
	 */
	public static function get($URL)
	{
		$return = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite,
			 m.url,
			 m.data AS meta_data
			 FROM dealer AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.language = ? AND i.hidden = ? AND m.url = ?
			 LIMIT 1',
			array(FRONTEND_LANGUAGE, 'N', (string) $URL)
		);

		// unserialize
		if(isset($return['meta_data'])) $return['meta_data'] = @unserialize($return['meta_data']);

		// init url
		$linkLocator = FrontendNavigation::getURLForBlock('dealer', 'locator');
		$return['full_url'] = $linkLocator . '/' . $return['url'];

		// add brands
		$brands = FrontendDealerModel::getDealerBrands($return['id']);;
		foreach($brands as $brand)
		{
			$return['brandInfo'][] = FrontendDealerModel::getBrand($brand['brand_id']);
		}

		return $return;
	}

	/**
	 * Get an dealer locator
	 *
	 * @param string $URL The URL for the item.
	 * @return array
	 */
	public static function getDealer($id)
	{
		$return = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite,
			 m.url,
			 m.data AS meta_data
			 FROM dealer AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.language = ? AND i.hidden = ? AND i.id = ?
			 LIMIT 1',
			array(FRONTEND_LANGUAGE, 'N', (int) $id)
		);

		// unserialize
		if(isset($return['meta_data'])) $return['meta_data'] = @unserialize($return['meta_data']);

		// init url
		$linkPlace = FrontendNavigation::getURLForBlock('dealer', 'place');
		$return['full_url'] = $linkPlace . '/' . $return['url'];

		// add brands
		$brands = FrontendDealerModel::getDealerBrands($return['id']);;
		foreach($brands as $brand)
		{
			$return['brandInfo'][] = FrontendDealerModel::getBrand($brand['brand_id']);
		}

		return $return;
	}

	/**
	 * Get all dealer.
	 *
	 * @param string $area 			The city or postcode
	 * @param array $brands 		An array of selected brands
	 * @param string $country 		Search only in: BE, FR and NL
	 * @return array
	 */
	public static function getAll($area, $brands, $country, $addDistance = '0')
	{
		// get module settings
		$moduleSettings = FrontendModel::getModuleSettings('dealer');
		$limit = $moduleSettings['limit'];
		$distance = $moduleSettings['distance']+$addDistance;
		$unit = $moduleSettings['units'];

		// The url for quering Google Maps api to get latitude/longitude coordinates for an address.
		$urlGoogleMaps = 'http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false';

		// build address & full url to google
		try
		{
			// get country code by ip
			if($country == 'AROUND') $country = SpoonHTTP::getContent('http://api.hostip.info/country.php?ip=' . SpoonHTTP::getIp());

			// set country BE when getIP is private (we are using private ip's in our workplace)
			if($country == 'XX') $country = 'BE';
		}
		catch(Exception $e)
		{
			// api of hostip isn't working
			$country = '';
		}

		$fullAddress = $area . ', ' . $country;
		$url = sprintf($urlGoogleMaps, urlencode($fullAddress));

		// fetch data from google
		$geocode = json_decode(SpoonHTTP::getContent($url));

		// results found?
		$lat = isset($geocode->results[0]->geometry->location->lat) ? $geocode->results[0]->geometry->location->lat : null;
		$lng = isset($geocode->results[0]->geometry->location->lng) ? $geocode->results[0]->geometry->location->lng : null;

		// radius of earth; @note: the earth is not perfectly spherical, but this is considered the 'mean radius'
		if($unit == 'KM') $radius = 6371.009; // in kilometers
		elseif($unit == 'MILES') $radius = 3958.761; // in miles

		// latitude boundaries
		$maxLat = (float) $lat + rad2deg($distance / $radius);
		$minLat = (float) $lat - rad2deg($distance / $radius);

		// longitude boundaries (longitude gets smaller when latitude increases)
		$maxLng = (float) $lng + rad2deg($distance / $radius / cos(deg2rad((float) $lat)));
		$minLng = (float) $lng - rad2deg($distance / $radius / cos(deg2rad((float) $lat)));

		// show only dealers in selected country
		$sqlCountry = '';
		if($country != 'AROUND') $sqlCountry = ' AND country = "' . $country . '"';

		// show only selected brands
		$innerJoin = '';
		$sqlBrands = '';
		$groupBy = '';
		if(!empty($brands))
		{
			$innerJoin = 'INNER JOIN dealer_index AS di ON di.dealer_id = d.id
						  INNER JOIN dealer_brands AS b ON di.brand_id = b.id';
			$sqlBrands = ' AND di.brand_id IN (' . implode(',', $brands) . ')';
			$groupBy = 'GROUP BY dealer_id';
		}

		// set db records in temp arr
		$tempArr = (array) FrontendModel::getContainer()->get('database')->GetRecords(
			'SELECT *, d.name as name, d.id as not_index_dealer_id
			 FROM dealer AS d
			 ' . $innerJoin . '
			 INNER JOIN meta AS m ON d.meta_id = m.id
			 WHERE d.language = ? AND d.lat > ? AND d.lat < ? AND d.lng > ? AND d.lng < ? AND d.hidden = ? ' . $sqlCountry . ' ' . $sqlBrands . '
			 ' . $groupBy . '
			 ORDER BY ABS(d.lat - ?) + ABS(d.lng - ?) ASC
			 LIMIT ?',
			array(FRONTEND_LANGUAGE, $minLat, $maxLat, $minLng, $maxLng, 'N', (float) $lat, (float) $lng, (int) $limit)
		);

		// loop db records and add brand info
		$dealers = array();
		for($i = 0; $i < count($tempArr); $i++)
		{
			// init url
			$dealers[$i] = $tempArr[$i];
			$linkLocator = FrontendNavigation::getURLForBlock('dealer', 'locator');
			$dealers[$i]['full_url'] = $linkLocator . '/' . $dealers[$i]['url'];

			// add distance to array
			$dealers[$i]['proximity'] = self::getDistance($lat, $lng, $dealers[$i]['lat'], $dealers[$i]['lng'], $unit);

			// set dealer_id to the original id (fix when no brands where selected)
			$dealers[$i]['dealer_id'] = $dealers[$i]['not_index_dealer_id'];

			// get all brands of locator
			$brandsId = FrontendDealerModel::getDealerBrands($dealers[$i]['not_index_dealer_id']);;
			foreach($brandsId as $brand)
			{
				$dealers[$i]['brandInfo'][] = FrontendDealerModel::getBrand($brand['brand_id']);
			}
		}

		// check if addDistance axist
		if(empty($addDistance)) $addDistance = 0;

		// check if we fond ealers
		if(empty($dealers))
		{
			// add distance with 50 km / miles
			$addDistance = $addDistance+50;

			// stop checking after 200 km / miles (4 loops)
			if($addDistance < 200)
			{
				return self::getAll($area, $brands, $country, $addDistance);
			}
		}
		else
		{
			// sorting dealers
			uasort($dealers, array('self', 'sortProximity'));
			return $dealers;
		}
	}

	/**
	 * Get all the brands.
	 *
	 * @return array
	 */
	public static function getAllBrands()
	{
		return (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT *
			 FROM dealer_brands
			 WHERE language = ?',
			array(FRONTEND_LANGUAGE)
		);
	}

	/**
	 * Get brand info.
	 *
	 * @param int $id The id of the item to fetch.
	 * @return array
	 */
	public static function getBrand($id)
	{
		$items = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT *
			 FROM dealer_brands as d
			 INNER JOIN meta AS m ON d.meta_id = m.id
			 WHERE d.id = ?
			 LIMIT 1',
			array((int) $id)
		);

		// init url
		$linkBrand = FrontendNavigation::getURLForBlock('dealer', 'brand');
		$items['full_url'] = $linkBrand . '/' . $items['url'];

		return $items;
	}

	/**
	 * Get all data for the brand with the given ID.
	 *
	 * @param int $id The id for the dealer locater to get.
	 * @return array
	 */
	public static function getBrandDealers($id)
	{
		$return = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT *
			 FROM dealer AS d
			 INNER JOIN dealer_index AS di ON d.id = di.dealer_id
			 INNER JOIN meta AS m ON d.meta_id = m.id
			 WHERE di.brand_id = ?',
			array((int) $id)
		);

		// loop db records and add full url
		for($i=0; $i < count($return); $i++)
		{
			// init url
			$linkLocator = FrontendNavigation::getURLForBlock('dealer', 'locator');
			$return[$i]['full_url'] = $linkLocator . '/' . $return[$i]['url'];

			// add brands
			$brandsId = FrontendDealerModel::getDealerBrands($return[$i]['dealer_id']);;
			foreach($brandsId as $brand)
			{
				$return[$i]['brandInfo'][] = FrontendDealerModel::getBrand($brand['brand_id']);
			}
		}

		return $return;
	}

	/**
	 * Get an dealer locator
	 *
	 * @param string $URL The URL for the item.
	 * @return array
	 */
	public static function getBrandInfo($URL)
	{
		$return = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite,
			 m.url,
			 m.data AS meta_data
			 FROM dealer_brands AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.language = ? AND m.url = ?
			 LIMIT 1',
			array(FRONTEND_LANGUAGE, (string) $URL)
		);

		// unserialize
		if(isset($return['meta_data'])) $return['meta_data'] = @unserialize($return['meta_data']);

		return $return;
	}

	/**
	 * Get all data for the brand with the given ID.
	 *
	 * @param int $id The id for the dealer locater to get.
	 * @return array
	 */
	public static function getDealerBrands($id)
	{
		return (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT brand_id
			 FROM dealer_index
			 WHERE dealer_id = ?',
			array((int) $id)
		);
	}

	/**
	 * Calculate distance between 2 points
	 *
	 * @param	float $lat1
	 * @param	float $lng1
	 * @param	float $lat2
	 * @param	float $lng2
	 * @param	string[optional] $unit
	 */
	public static function getDistance($lat1, $lng1, $lat2, $lng2, $unit = 'KM')
	{
		// radius of earth; @note: the earth is not perfectly spherical, but this is considered the 'mean radius'
		if($unit == 'KM') $radius = 6371.009; // in kilometers
		elseif($unit == 'MILES') $radius = 3958.761; // in miles

		// convert degrees to radians
		$lat1 = deg2rad((float) $lat1);
		$lng1 = deg2rad((float) $lng1);
		$lat2 = deg2rad((float) $lat2);
		$lng2 = deg2rad((float) $lng2);

		// great circle distance formula
		return $radius * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lng1 - $lng2));
	}

	/**
	 * Sort based on proximity
	 *
	 * @param	array $a1
	 * @param	array $a2
	 * @return	int
	 */
	public static function sortProximity($a1, $a2)
	{
		if($a1['proximity'] > $a2['proximity']) return 1;
		elseif($a1['proximity'] == $a2['proximity']) return 0;
		else return -1;
	}

	/**
	 * Parse the search results for this module
	 *
	 * Note: a module's search function should always:
	 * 		- accept an array of entry id's
	 * 		- return only the entries that are allowed to be displayed, with their array's index being the entry's id
	 *
	 * @param array $ids The ids of the found results.
	 * @return array
	 */
	public static function search(array $ids)
	{
		$items = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT i.id, i.name as title, i.name as text, m.url
			 FROM dealer AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.hidden = ? AND i.language = ? AND i.id IN (' . implode(',', $ids) . ')',
			array('N', FRONTEND_LANGUAGE), 'id'
		);

		// prepare items for search
		foreach($items as &$item)
		{
			$item['full_url'] = FrontendNavigation::getURLForBlock('dealer', 'locator') . '/' . $item['url'];
		}

		return $items;
	}
}
