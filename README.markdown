CakePHP google map v3 helper / wrapper
======================================


Introduction
------------
This is a helper that is made upon google maps api version > 3.0


Usage
------------
Firstly create a div for google map. Give it a css height and width:
	
	<div id='my_map' style='height:400px; width:400px'></div>
	

Secondly, add the div id to the map method:

	<?php $googleMapV3->map(array('div'=>array('id'=>'my_map'))); ?>
	
	
optional, add markers to the map:

	<?php $googleMapV3->addMarker(array(
		'latitude'=>-73.95144,
		'longitude'=>40.698
	)); ?>

finally, now time to make the script

	<?php echo $googleMapV3->toScript() ?> #there is a echo in this line only