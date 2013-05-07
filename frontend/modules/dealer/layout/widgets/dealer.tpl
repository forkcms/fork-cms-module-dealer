{*
	variables that are available:
	- {$widgetDealerItem}: contains data about this dealer locator
	- {$widgetDealerSettings}: contains this module's settings
*}

{option:widgetDealerItem}

	{* Store item text in a div because JS goes bananas with multiline HTML *}
		<div id="markerText{$widgetDealerItem.id}" style="display:none;">
			{option:widgetDealerItem.avatar}
				<img src="{$FRONTEND_FILES_URL}/dealer/avatars/64x64/{$widgetDealerItem.avatar}" width="64" height="64" alt="" style="float:right; margin: 5px;" />
			{/option:widgetDealerItem.avatar}

			{$widgetDealerItem.street} {$widgetDealerItem.number}<br>
			{$widgetDealerItem.zip} {$widgetDealerItem.city} <br>

			{option:widgetDealerItem.tel}
				{$lblPhone}: {$widgetDealerItem.tel} <br>
			{/option:widgetDealerItem.tel}

			{option:widgetDealerItem.fax}
				{$lblFax}: {$widgetDealerItem.fax} <br>
			{/option:widgetDealerItem.fax}

			{option:widgetDealerItem.email}
				{$lblEmail}: <a href="mailto:{$dealerItems.email}">{$widgetDealerItem.email}</a><br>
			{/option:widgetDealerItem.email}

			{option:widgetDealerItem.site}
				{$lblSite}: {$widgetDealerItem.site} <br>
			{/option:widgetDealerItem.site}

			<strong>{$lblBrands}</strong> <br>
        	{iteration:widgetDealerItem.brandInfo}
        		{option:widgetDealerItem.brandInfo.name}
           			{$widgetDealerItem.brandInfo.name},
           		{/option:widgetDealerItem.brandInfo.name}
        	{/iteration:widgetDealerItem.brandInfo}
        	<a href="http://maps.google.com/?q={$widgetDealerItem.street|urlencode}+{$widgetDealerItem.number|urlencode}+{$widgetDealerItem.zip|urlencode}+{$widgetDealerItem.city|urlencode}" target="_blank">{$msgViewOnBigMap}</a>
		</div>

	<div id="mapWidget" style="height: {$widgetDealerSettings.height_widget}px; width: {$widgetDealerSettings.width_widget}px;"></div>

	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
	<script type="text/javascript">
		var marker = new Array();

		var initialize = function()
		{
			// create boundaries
			var latlngBounds = new google.maps.LatLngBounds();

			// set options
			var options =
			{
				// set zoom as defined by user, or as 0 if to be done automatically based on boundaries
				zoom: '{$widgetDealerSettings.zoom_level_widget}' == 'auto' ? 0 : {$widgetDealerSettings.zoom_level_widget},
				// set default center as first item's location
				center: new google.maps.LatLng({$widgetDealerItem.lat}, {$widgetDealerItem.lng}),
				// no interface, just the map
				disableDefaultUI: false,
				// dragging the map around
				draggable: true,
				// no zooming in/out using scrollwheel
				scrollwheel: false,
				// no double click zoom
				disableDoubleClickZoom: true,
				// set map type
				mapTypeId: google.maps.MapTypeId.{$widgetDealerSettings.map_type_widget}
			};

			// create map
			var mapWidget = new google.maps.Map(document.getElementById('mapWidget'), options);


			// function to add markers to the map
			function addMarker(id, lat, lng, title, text)
			{
				// create position
				position = new google.maps.LatLng(lat, lng);

				// add to boundaries
				latlngBounds.extend(position);

				// add marker
				marker[id] = new google.maps.Marker(
				{
					// set position
					position: position,
					// add to map
					map: mapWidget,
					// set title
					title: title
				});

				// add click event on marker
				google.maps.event.addListener(marker[id], 'click', function()
				{
					// create infowindow
					new google.maps.InfoWindow({ content: '<h1>'+ title +'</h1>' + text }).open(mapWidget, marker[id]);
				});
			}

			// add marker to map
			{option:widgetDealerItem.lat}{option:widgetDealerItem.lng}addMarker({$widgetDealerItem.id}, {$widgetDealerItem.lat}, {$widgetDealerItem.lng}, '{$widgetDealerItem.name}', $('#markerText' + {$widgetDealerItem.id}).html());{/option:widgetDealerItem.lat}{/option:widgetDealerItem.lng}

			// set center to the middle of our boundaries
			mapWidget.setCenter(latlngBounds.getCenter());

			// set zoom automatically, defined by points (if allowed)
			if('{$widgetDealerSettings.zoom_level}' == 'auto') mapWidget.fitBounds(latlngBounds);
		}

		function openMarker(id, title, text)
		{
			// create infowindow
			new google.maps.InfoWindow({ content: '<h1>'+ title +'</h1>' + text }).open(mapWidget, marker[id]);
		}

		google.maps.event.addDomListener(window, 'load', initialize);
	</script>
{/option:widgetDealerItem}