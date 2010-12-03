<?php

/**
 * This is a CakePHP helper that helps users to integrate google map v3 
 * into their application by only writing php codes this helper depends on JQuery
 *
 * @package default
 * @author Rajib Ahmed
 * @version 0.10.12 
 */
class GoogleMapV3Helper extends Helper {


	/**
	 * Cakephp builtin helper
	 *
	 * @var array 
	 */
    public $helpers=array('Javascript', 'Html');

	/**
	 * google maker config instance variable
	 *
	 * @var array
	 */
    public $markers = array();
	
	/**
	 * google infoWindow config instance variable
	 *
	 * @var array
	 */
    public $infoWindow = array();	

	/**
	 * google map instance varible
	 *
	 * @var string
	 */
    public $map = '';



	/**
	 * settings of the helper
	 *
	 * @var array
	 */
    private $_defaultSettings = array(
		'map'=>array(
			
		),
		'zoom'    =>6,
		'type'    =>'ROADMAP',
		'longitude'=>-73.95144,
		'latitude'=>40.698,
		'localize'=>true,
		'showMarker'  =>true,
		'showInfoWindow'=>true,
		'markerIcon'=>'http://google-maps-icons.googlecode.com/files/home.png',
		'infoWindow'=>array(
			
		),
		'marker'=>array(
			'autoCenter'=>true,
			'icon'		=>'http://google-maps-icons.googlecode.com/files/home.png'
		),
		'div'=>array(
			'id'=>'map_canvas'
		),
		'event'=>array(),
		
		'autoCenterMarkers'=>true
	);

	
	private $_currentSettings =array();


		
	
	/**
	 * This method outputs string javascript to the html
	 *
	 * @return string
	*/	
    public function to_script(){
        $script='<script type="text/javascript">
	    //<![CDATA[ 
	    	$(function(){ 
        ';
		
        
        $script.=$this->map;

        if($this->_defaultSettings['showMarker'] && !empty($this->markers) && is_array($this->markers)){
          $script.=implode($this->markers, " ");
        }

        if($this->_defaultSettings['autoCenterMarkers'])
        { 
        	$script.= $this->autoCenter();
        }
		
 
		
        $script.='
		    });
      	 //]]>
        </script>';

        return $script;
      }



	/**
	 * This the initialization point of the script
	 *
	 * @param array $options associative array of settings are passed
	 * @return void
	 * @author Rajib Ahmed
	 */
    function map($options=null){
      $settings = Set::merge($this->_defaultSettings,$options);

      $this->Javascript->link('http://maps.google.com/maps/api/js?sensor=true',false);
      $this->Javascript->link("http://code.google.com/apis/gears/gears_init.js",false);

      $map = "
            gMarkers = new Array();
            var noLocation = new google.maps.LatLng(".$settings['latitude'].", ".$settings['longitude'].");
            var initialLocation;
            var browserSupportFlag =  new Boolean();
            var myOptions = {
              zoom: ".$settings['zoom'].",
              mapTypeId: google.maps.MapTypeId.".$settings['type'].",
              center:noLocation
            };

            //Global variables
            gMap = new google.maps.Map(document.getElementById(\"".$settings['div']['id']."\"), myOptions);

            ";
            $this->map = $map;
    }


    function addMarker($options){
        if($options==null) return null;
        if(!isset($options['latitude']) || $options['latitude']==null || !isset($options['longitude']) || $options['longitude']==null) return null;
        if (!preg_match("/[-+]?\b[0-9]*\.?[0-9]+\b/", $options['latitude']) || !preg_match("/[-+]?\b[0-9]*\.?[0-9]+\b/", $options['longitude'])) return null;


        $marker = "
            gMarkers.push(
              new google.maps.Marker({
               position:new google.maps.LatLng(".$options['latitude'].",".$options['longitude']."),
               map:gMap,
               icon:'".$options['icon']."'
              }));
        ";

        $this->markers[] = $marker;
    }
    
    public function autoCenter()
    {
    	return '
        var bounds = new google.maps.LatLngBounds();
        $.each(gMarkers,function (index, marker){ bounds.extend(marker.position);});
        gMap.fitBounds(bounds);
        ';
    }
    
    public function addInfoWindow($options=array())
    {
		$this->infoWindow[] = " var in = new google.maps.InfoWindow({
									content:'hello',
									position:gMap.getCenter()
								});"
								
								
		$this->map .= $this->infoWindow[0];
    	
    }
	
	#Private methods
	private function _mapOptions(){
	
	
	}

  }
?>