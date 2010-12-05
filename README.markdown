CakePHP google map v3 helper / wrapper
======================================


Introduction
------------
This is a helper that is made upon google maps api version > 3.0
and this depends on use of JQuery

Dependency
-----------
The script depends on using jquery, so please add jquery to the layout
or add it from
   

   https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js
   
Demo View Script
-----------------
Added a demo view script to this repo check if you are confused else delete it

   
Load Helper
-----------
	On the app_contoller.php for globally addind the helper
	
	<?php 	
		class AppController extends Contoller{
			public $helpers= array('Html','Javascript','GoogleMapV3');
		}	
	?>

	load it on a 
	<?php 	
		class DemoController extends AppContoller{
			funtion map(){
				$this->helpers[]='GoogleMapV3';
				#	rest of your code		
			}
		}	
	?>
	

Usage
------------
Firstly create a div for google map. Give it a css height and width:
	
	<div id='my_map' style='height:400px; width:400px'></div>
	

Secondly, add the div id to the map method:

	<?php $googleMapV3->map(array('div'=>array('id'=>'my_map'))); ?>
	
From a request of a cakephp user friend i am trying to expand it little bit more

**Current Usage**

	1. Adding single/multiple markers
	2. Adding single/multiple infowindow
	3. Adding events on marker to show infowindow
	
### 1. Adding single/multiple markers

	To add a marker to the google maps pass an associative array
	<?php  
		$options = array(
		    'latitude'=>-73.95144,
    		'longitude'=>40.698
			'icon'=>url_to_icon
		);

		//To add more than one marker use this multiple time
		// I use it inside a for loop for multile markers
		$googleMapV3->addMarker($options);
	?>		 

### 2. Adding single/multiple infowindow

	<?php 
		$options = array(
		    'latitude'=>-73.95144,
    		'longitude'=>40.698,
    		'content'=>'Thanks for using this'
		);
		
		$googleMapV3->addInfoWindow($options);
	?>

### 3. Adding events on marker to show infowindow

	TODO add the doc later

finally, now time to make the script

	<?php echo $googleMapV3->toScript() ?> #there is a echo in this line only