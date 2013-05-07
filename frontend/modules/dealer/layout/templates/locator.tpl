{*
	variables that are available:
	- {$dealerItem}: contains data about all the dealer
	- {$dealerSettings}: contains data about map settings
	- {$lblBrands}
	- {$lblCityOrZip|ucfirst} {$lblTel|ucfirst} {$lblFax|ucfirst} {$lblAddress|ucfirst} {$lblNumber|ucfirst} {$lblwebsite|ucfirst}
	- {$msgDealerNoItems}
	- {$msgViewOnMap}
	- {$msgViewOnBigMap}
*}

{option:dealerItem}
	<div id="map" style="height: {$dealerSettings.height}px; width: {$dealerSettings.width}px;"></div>

	{* Store item text in a div because JS goes bananas with multiline HTML *}
		<div id="markerText{$dealerItem.id}" style="display:none;">	
			{option:dealerItem.avatar}
				<img src="{$FRONTEND_FILES_URL}/dealer/avatars/64x64/{$dealerItem.avatar}" width="64" height="64" alt="" style="float:right; margin: 5px;" />
			{/option:dealerItem.avatar}
										
			{$dealerItem.street|ucfirst} {$dealerItem.number}<br>
			{$dealerItem.zip} {$dealerItem.city|ucfirst} <br>
			
			{option:dealerItem.tel}
				{$lblPhone|ucfirst}: {$dealerItem.tel} <br>
			{/option:dealerItem.tel}
			
			{option:dealerItem.fax}
				{$lblFax|ucfirst}: {$dealerItem.fax} <br>
			{/option:dealerItem.fax}
			
			{option:dealerItem.email}
				{$lblEmail|ucfirst}: <a href="mailto:{$dealerItem.email}">{$dealerItem.email}</a> <br>
			{/option:dealerItem.email}
									
			{option:dealerItem.website}
				{$lblSite|ucfirst}: <a href="{$dealerItem.website}" target="_blank">{$dealerItem.website}</a> <br>
			{/option:dealerItem.website}
			
			{option:dealerItem.brandInfo}
				<strong>{$lblBrands|ucfirst}</strong> <br>
	        	{iteration:dealerItem.brandInfo}
	        		{option:dealerItem.brandInfo.name}
	           			{$dealerItem.brandInfo.name}, 
	           		{/option:dealerItem.brandInfo.name}
	        	{/iteration:dealerItem.brandInfo}
	        	<a href="http://maps.google.com/?q={$dealerItem.street|urlencode}+{$dealerItem.number|urlencode}+{$dealerItem.zip|urlencode}+{$dealerItem.city|urlencode}" target="_blank">{$msgViewOnBigMap}</a>
			{/option:dealerItem.brandInfo}
		
		</div>

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
				center: new google.maps.LatLng({$dealerItem.lat}, {$dealerItem.lng}),
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
			
			// add marker to map
			{option:dealerItem.lat}{option:dealerItem.lng}addMarker({$dealerItem.id}, {$dealerItem.lat}, {$dealerItem.lng}, '{$dealerItem.name}', $('#markerText' + {$dealerItem.id}).html());{/option:dealerItem.lat}{/option:dealerItem.lng}

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
	<div id="dealerItem">
			<div class="dealerBlock">
					<div>
						<span class="dealerTitle"><a href="#" onClick="openMarker({$dealerItem.id}, '{$dealerItem.name}', $('#markerText' + {$dealerItem.id}).html());">{$dealerItem.name}</a></span>
						<a href="#" onClick="openMarker({$dealerItem.id}, '{$dealerItem.name}', $('#markerText' + {$dealerItem.id}).html());">{$msgViewOnMap}</a>
					</div>
					<div class="dealerInfo">
						{option:dealerItem.avatar}
							<a href="#" onClick="openMarker({$dealerItem.id}, '{$dealerItem.name}', $('#markerText' + {$dealerItem.id}).html());"><img src="{$FRONTEND_FILES_URL}/dealer/avatars/128x128/{$dealerItem.avatar}" width="128" height="128" alt="{$dealerItem.name}" border="0" style="float:left; margin: 5px;" /></a>
						{/option:dealerItem.avatar}
						{$dealerItem.street|ucfirst} {$dealerItem.number} <br>
						{$dealerItem.zip} {$dealerItem.city|ucfirst} <br>
						
						{option:dealerItem.tel}
							{$lblPhone|ucfirst}: {$dealerItem.tel} <br>
						{/option:dealerItem.tel}
						
						{option:dealerItem.fax}
							{$lblFax|ucfirst}: {$dealerItem.fax} <br>
						{/option:dealerItem.fax}
						
						{option:dealerItem.email}
							{$lblEmail|ucfirst}: <a href="mailto:{$dealerItem.email}">{$dealerItem.email}</a><br>
						{/option:dealerItem.email}
												
						{option:dealerItem.website}
							{$lblSite|ucfirst}: <a href="{$dealerItem.website}" target="_blank">{$dealerItem.website}</a><br>
						{/option:dealerItem.website}
					</div>
				<div class="dealerMapLink">
					<a href="http://maps.google.com/?q={$dealerItem.street|urlencode}+{$dealerItem.number|urlencode}+{$dealerItem.zip|urlencode}+{$dealerItem.city|urlencode}" target="_blank">{$msgViewOnBigMap}</a>
				</div>
				<div class="dealerInfo"">
					{option:dealerItem.brandInfo}
						<strong>{$lblBrands|ucfirst}:</strong><br>
			        	 <ul>
			            	{iteration:dealerItem.brandInfo}
			            		{option:dealerItem.brandInfo.name}
			               			<li>
				               			<div class="dealerBrand">
					               			<a href="{$dealerItem.brandInfo.full_url}">
						               			<img src="{$FRONTEND_FILES_URL}/dealer/brands/32x32/{$dealerItem.brandInfo.image}" width="32" height="32" border="0" alt="" style="float:left; margin: 5px;" />
						               			{$dealerItem.brandInfo.name}
					               			</a>
				               			</div>
			               			</li>
			               		{/option:dealerItem.brandInfo.name}
			            	{/iteration:dealerItem.brandInfo}
			        	 </ul>
		        	 {/option:dealerItem.brandInfo}
				</div>
			</div>
	</div>
{/option:dealerItem}