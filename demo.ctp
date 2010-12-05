

<div id="map_canvas" style="width:500px; height:400px">	</div>	


<?php 
	$googleMapV3->map(array(
		'div' => array(
			'id' => 'map_canvas'
		)
	));

  $marker = $googleMapV3->addMarker(array(
    'latitude'=>23.72,
    'longitude'=>90.41
  );

	//for infowindow
	$infoWindow = $googleMapV3->addInfoWindow(
	    'latitude'=>23.72,
	    'longitude'=>90.41
	);

	echo $googleMapV3->toScript();
?>