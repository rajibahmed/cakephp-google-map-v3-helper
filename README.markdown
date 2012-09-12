CakePHP google map v3 helper / wrapper
======================================


Important!!!!
---------------
New home to this project is https://github.com/dereuromark/cakephp-google-map-v3-helper .
I haven't done anything after one sleepless night effort.
If you are using this pluging please check https://github.com/dereuromark.


Introduction
------------
This is a helper that is made upon google maps api version >= 3.0
and this depends on use of JQuery

Dependency
-----------

CakePHP 1.3
The script depends on using jquery, so please add jquery to the layout
or add it from

   https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js


Load Helper
-----------
	On the app_contoller.php for globally adding the helper

	<?php
		class AppController extends Contoller{
			public $helpers = array('Html','Javascript','GoogleMapV3');
		}
	?>

	load it on a
	<?php
		class DemoController extends AppContoller{

			function map() {
				$this->helpers[] = 'GoogleMapV3';
				#	rest of your code
			}
		}
	?>


Usage
------------

Add this to your view:
	<?php
		echo '<script type="text/javascript" src="'.$this->GoogleMap->apiUrl().'"></script>';
	?>
Or use "autoScripts" => true for the next step.

Firstly create a div for google map. Give it a css height and width:

	<?php echo $this->GoogleMapV3->map(array('div'=>array('id'=>'my_map', 'height'=>'400', 'width'=>'100%'))); ?>


**Current Usage**

	1. Adding single/multiple markers
	2. Adding single/multiple infowindow
	3. Adding events on marker to show infowindow
	4. Map Links
	5. StaticMaps

### 1. Adding single/multiple markers

	To add a marker to the google maps pass an associative array
	<?php
		$options = array(
	    'latitude'=>48.95145,
  		'longitude'=>11.6981,
			'icon'=> 'url_to_icon', # optional
			'title' => 'Some title', # optional
			'content' => '<b>HTML</b> Content for the Bubble/InfoWindow' # optional
		);

		//To add more than one marker use this multiple time
		// I use it inside a for loop for multile markers
		$this->GoogleMapV3->addMarker($options);
	?>

### 2. (OPTIONAL) Adding single/multiple infowindow

	<?php
		$options = array(
		    'latitude'=>49.95144,
    		'longitude'=>12.6981,
    		'content'=>'Thanks for using this'
		);

		$this->GoogleMapV3->addInfoWindow($options);
	?>

### 3. (OPTIONAL) Adding events on marker to show infowindow

	<?php
		$marker = $this->GoogleMapV3->addMarker($options);
		$infoWindow = $this->GoogleMapV3->addInfoWindow($options);
		$this->GoogleMapV3->addEvent($marker, $infoWindow);
	?>
or
	<?php
		$marker = $this->GoogleMapV3->addMarker($options);
		$custom = '...'; # js
		$this->GoogleMapV3->addCustomEvent($marker, $custom);
	?>

finally, now time to make the script

	<?php echo $this->GoogleMapV3->script() ?>


### 4. Map Links

	<?php
		$url = $this->GoogleMap->link(array('to'=>'Munich, Germany'));
		echo $this->Html->link('Visit Me', $url, array('target'=>'_blank'));
	?>


### 5. StaticMaps

	<?php
		# a simple image as map
		$markers = array(
			array('lat'=>48.2, 'lng'=>11.1),
			array('lat'=>48.1, 'lng'=>11.2)
		);
		$options = array(
			'size' => '500x400',
			'center' => true,
			'markers' => $this->GoogleMap->staticMarkers($markers)
		);
		$attr = array(
			'title'=>'Yeah'
		);
		echo $this->GoogleMap->staticMap($options, $attr);

		# you can even add an url to click on
		$attr['url'] = $this->GoogleMap->link(array('to'=>'Munich, Germany'));
		echo $this->GoogleMap->staticMap($options, $attr);
	?>


