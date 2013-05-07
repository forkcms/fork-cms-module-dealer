{*
	variables that are available:
	- {$brandItem}: contains data about all the brand
	- {$dealerItems}: contains data about all the dealers
	- {$dealerSettings}: contains data about map settings
	- {$lblBrands}
	- {$lblCityOrZip|ucfirst} {$lblTel|ucfirst} {$lblFax|ucfirst} {$lblAddress|ucfirst} {$lblNumber|ucfirst} {$lblSite|ucfirst}
	- {$msgDealerNoItems}
	- {$msgViewOnMap}
	- {$msgViewOnBigMap}
*}
{option:brandItem}
<h3>{$brandItem.name}</h3>
<img src="{$FRONTEND_FILES_URL}/dealer/brands/128x128/{$brandItem.image}" width="128" height="128" alt="" />
{/option:brandItem}

{option:dealerItems}
	<h3>{$dealerHeadingText|sprintf:{$numDealers}}</h3>
	<div id="map" style="height: {$dealerSettings.height}px; width: {$dealerSettings.width}px;"></div>

	{* Store item text in a div because JS goes bananas with multiline HTML *}
	{iteration:dealerItems}
		<div id="markerText{$dealerItems.dealer_id}" style="display:none;">	
			{option:dealerItems.avatar}
				<img src="{$FRONTEND_FILES_URL}/dealer/avatars/64x64/{$dealerItems.avatar}" width="64" height="64" alt="" style="float:right; margin: 5px;" />
			{/option:dealerItems.avatar}
										
			{$dealerItems.street|ucfirst} {$dealerItems.number}<br>
			{$dealerItems.zip} {$dealerItems.city|ucfirst} <br>
			
			{option:dealerItems.tel}
				{$lblPhone|ucfirst}: {$dealerItems.tel} <br>
			{/option:dealerItems.tel}
			
			{option:dealerItems.fax}
				{$lblFax|ucfirst}: {$dealerItems.fax} <br>
			{/option:dealerItems.fax}
			
			{option:dealerItems.email}
				{$lblEmail|ucfirst}: <a href="mailto:{$dealerItems.email}">{$dealerItems.email}</a><br>
			{/option:dealerItems.email}
									
			{option:dealerItems.website}
				{$lblSite|ucfirst}: <a href="{$dealerItems.website}" target="_blank">{$dealerItems.website}</a> <br>
			{/option:dealerItems.website}

        	<a href="http://maps.google.com/?q={$dealerItems.street|urlencode}+{$dealerItems.number|urlencode}+{$dealerItems.zip|urlencode}+{$dealerItems.city|urlencode}" target="_blank">{$msgViewOnBigMap}</a>
		</div>
	{/iteration:dealerItems}

	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
	<script type="text/javascript">
	
		var marker =new Array();
		
		var initialize = function()
		{
			// create boundaries
			var latlngBounds = new google.maps.LatLngBounds();
			
			
			// set options
			var options =
			{
				// set zoom as defined by user, or as 0 if to be done automatically based on boundaries
				zoom: '{$dealerSettings.zoom_level}' == 'auto' ? 0 : {$dealerSettings.zoom_level},
				// set default center as first item's location
				center: new google.maps.LatLng({$dealerItems.0.lat}, {$dealerItems.0.lng}),
				// no interface, just the map
				disableDefaultUI: false,
				// dragging the map around
				draggable: true,
				// no zooming in/out using scrollwheel
				scrollwheel: false,
				// no double click zoom
				disableDoubleClickZoom: true,
				// set map type
				mapTypeId: google.maps.MapTypeId.{$dealerSettings.map_type}
			};

			// create map
			var map = new google.maps.Map(document.getElementById('map'), options);


			// function to add markers to the map
			function addMarker(id, lat, lng, title, text)
			{
				// create position
				position = new google.maps.LatLng(lat, lng);

				// add to boundaries
				latlngBounds.extend(position);

				// add marker
				var image = '';
				marker[id] = new google.maps.Marker(
				{
					// set position
					position: position,
					// add to map
					map: map,
					// set title
					title: title,
					// set image
					icon: image
				});

				// add click event on marker
				google.maps.event.addListener(marker[id], 'click', function()
				{
					// create infowindow
					new google.maps.InfoWindow({ content: '<h1>'+ title +'</h1>' + text }).open(map, marker[id]);
				});
			}
			
			// loop items and add to map
			{iteration:dealerItems}
				{option:dealerItems.lat}{option:dealerItems.lng}addMarker({$dealerItems.dealer_id}, {$dealerItems.lat}, {$dealerItems.lng}, '{$dealerItems.name}', $('#markerText' + {$dealerItems.dealer_id}).html());{/option:dealerItems.lat}{/option:dealerItems.lng}
			{/iteration:dealerItems}

			// set center to the middle of our boundaries
			map.setCenter(latlngBounds.getCenter());

			// set zoom automatically, defined by points (if allowed)
			if('{$dealerSettings.zoom_level}' == 'auto') map.fitBounds(latlngBounds);
			
		}
		
		function openMarker(id, title, text)
		{
			// create infowindow
			new google.maps.InfoWindow({ content: '<h1>'+ title +'</h1>' + text }).open(map, marker[id]);
		}			

		google.maps.event.addDomListener(window, 'load', initialize);
	</script>
	<div id="dealerItems">
		{iteration:dealerItems}
			<div class="dealerBlock">
					<div>
						<span class="dealerTitle"><a href="#" onClick="openMarker({$dealerItems.dealer_id}, '{$dealerItems.name}', $('#markerText' + {$dealerItems.dealer_id}).html());">{$dealerItems.name}</a></span>
						<a href="#" onClick="openMarker({$dealerItems.dealer_id}, '{$dealerItems.name}', $('#markerText' + {$dealerItems.dealer_id}).html());">{$msgViewOnMap}</a>
					</div>
					<div class="dealerInfo">
						{option:dealerItems.avatar}
							<a href="#" onClick="openMarker({$dealerItems.dealer_id}, '{$dealerItems.name}', $('#markerText' + {$dealerItems.dealer_id}).html());"><img src="{$FRONTEND_FILES_URL}/dealer/avatars/128x128/{$dealerItems.avatar}" width="128" height="128" alt="{$dealerItems.name}" border="0" style="float:left; margin: 5px;" /></a>
						{/option:dealerItems.avatar}
						{$dealerItems.street|ucfirst} {$dealerItems.number} <br>
						{$dealerItems.zip} {$dealerItems.city|ucfirst} <br>
						
						{option:dealerItems.tel}
							{$lblPhone|ucfirst}: {$dealerItems.tel} <br>
						{/option:dealerItems.tel}
						
						{option:dealerItems.fax}
							{$lblFax|ucfirst}: {$dealerItems.fax} <br>
						{/option:dealerItems.fax}
						
						{option:dealerItems.email}
							{$lblEmail|ucfirst}: <a href="mailto:{$dealerItems.email}">{$dealerItems.email}</a><br>
						{/option:dealerItems.email}
												
						{option:dealerItems.website}
							{$lblSite|ucfirst}: <a href="{$dealerItems.website}" target="_blank">{$dealerItems.website}</a> <br>
						{/option:dealerItems.website}
					</div>
				<div class="dealerMapLink">
					<a href="http://maps.google.com/?q={$dealerItems.street|urlencode}+{$dealerItems.number|urlencode}+{$dealerItems.zip|urlencode}+{$dealerItems.city|urlencode}" target="_blank">{$msgViewOnBigMap}</a>
				</div>
				<div class="dealerInfo"">
					{option:dealerItems.brandInfo}
						<strong>{$lblBrands|ucfirst}:</strong><br>
			        	 <ul>
			            	{iteration:dealerItems.brandInfo}
			            		{option:dealerItems.brandInfo.name}
			               			<li>
				               			<div class="dealerBrand">
					               			<a href="{$dealerItems.brandInfo.full_url}">
						               			<img src="{$FRONTEND_FILES_URL}/dealer/brands/32x32/{$dealerItems.brandInfo.image}" width="32" height="32" border="0" alt="" style="float:left; margin: 5px;" />
						               			{$dealerItems.brandInfo.name}
					               			</a>
				               			</div>
			               			</li>
			               		{/option:dealerItems.brandInfo.name}
			            	{/iteration:dealerItems.brandInfo}
			        	 </ul>
		        	 {/option:dealerItems.brandInfo}
				</div>
			</div>
		{/iteration:dealerItems}
	</div>
{/option:dealerItems}