<?php

App::import('Helper', 'Tools.GoogleMapV3');
App::import('Vendor', 'MyCakeTestCase');

class GoogleMapHelperTest extends MyCakeTestCase {
	
	function setUp() {
		$this->GoogleMap = new GoogleMapV3Helper();
		$this->GoogleMap->initHelpers();
	}
	
	function tearDown() {
		
	}
	
	function testObject() {
		$this->assertTrue(is_a($this->GoogleMap, 'GoogleMapV3Helper'));
	}
		
	function testStatic() {
		
	}
	
	
	
	/**
	 * with default options
	 * 2010-12-18 ms
	 */
	function testDynamic() {
		echo '<h3>Map 1</h3>';
		echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>';
		//echo $this->GoogleMap->map($defaul, array('style'=>'width:100%; height: 800px'));
		echo '<script type="text/javascript" src="'.$this->GoogleMap->apiUrl().'"></script>';
		
		$options = array(
    );
    $result = $this->GoogleMap->map($options);
    $this->GoogleMap->addMarker(array('lat'=>48.69847,'lng'=>10.9514, 'title'=>'Marker', 'content'=>'Some Html-<b>Content</b>'));
    
    $this->GoogleMap->addMarker(array('lat'=>47.69847,'lng'=>11.9514, 'title'=>'Marker2', 'content'=>'Some more Html-<b>Content</b>'));
		
		
		$this->GoogleMap->addMarker(array('lat'=>47.19847,'lng'=>11.1514, 'title'=>'Marker3'));
		
		/*
		$options = array(
        'lat'=>48.15144,
        'lng'=>10.198,
        'content'=>'Thanks for using this'
    );
		$this->GoogleMap->addInfoWindow($options);
		//$this->GoogleMap->addEvent();
		*/
		
		$result .= $this->GoogleMap->script();
		
		echo $result;
	}
	
	
	/**
	 * more than 100 markers and it gets reaaally slow...
	 * 2010-12-18 ms
	 */
	function testDynamic2() {
		echo '<h3>Map 2</h3>';
		$options = array(
        'height'=>'111',
        'div' => array('id'=>'someother'),
        'map' => array('type'=>'H', 'typeOptions' => array('style'=>'DROPDOWN_MENU', 'pos'=>'RIGHT_CENTER'))
    );
    echo $this->GoogleMap->map($options);
    $this->GoogleMap->addMarker(array('lat'=>47.69847,'lng'=>11.9514, 'title'=>'Marker2', 'content'=>'Some more Html-<b>Content</b>'));
    
    for($i = 0; $i < 100; $i++) {
    	$lat = mt_rand(46000, 54000) / 1000;
    	$lng = mt_rand(2000, 20000) / 1000;
    	$this->GoogleMap->addMarker(array('lat'=>$lat,'lng'=>$lng, 'title'=>'Marker2', 'content'=>'Lat: <b>'.$lat.'</b><br>Lng: <b>'.$lng.'</b>'));
    }

    echo $this->GoogleMap->script();
    
    
	}
	
}
