<?php 

class GoogleMapV3Helper extends Helper {
 
    var $helpers=array('Javascript', 'Html'); 
    

    private $_defaultSettings = array(
      'zoom'    =>6,
      'type'    =>'ROADMAP',
      'longitude'=>-73.95144,
      'latitude'=>40.698,
      'localize'=>true,
      'showMarker'  =>true,
      'showInfoWindow'=>true,
      'markerIcon'=>'http://google-maps-icons.googlecode.com/files/home.png',
      'infoWindowText'=>'Change Me',
      'div'=>array(
        'id'=>'map_canvas',
        'style'=>'width:500px; height:500px'
       ),
      'usesJquery'=>true
      );
        
   /** 
     * Function map 
     * 
     * This method generates a tag called map_canvas and insert
     * a google maps.
     * 
     * Pass an array with the options listed above in order to customize it
     * 
     * @author Marc Fernandez <info (at) marcfg (dot) com> 
     * @param array $options - options array 
     * @return string - will return all the javascript script to generate the map
     * 
     */    
    function map($options=null){
      $settings = Set::merge($this->_defaultSettings,$options);    
      
      $this->Javascript->link('http://maps.google.com/maps/api/js?sensor=true',false);
      $this->Javascript->link("http://code.google.com/apis/gears/gears_init.js",false);
    
      $map = $this->Html->div('','',$settings['div']);
        $map .= "
        <script type='text/javascript'>
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
            gMarkers = new Array();
        ";
        
        /*$map .= "
            function localize(){
                if(navigator.geolocation) { // Try W3C Geolocation method (Preferred)
                    browserSupportFlag = true;
                    navigator.geolocation.getCurrentPosition(function(position) {
                      initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
                      gMap.setCenter(initialLocation);";
                      if($settings['showMarker']) $map .= "setMarker(initialLocation);";
                               
                    $map .= "}, function() {
                      handleNoGeolocation(browserSupportFlag);
                    });
                    
                } else if (google.gears) { // Try Google Gears Geolocation
                    browserSupportFlag = true;
                    var geo = google.gears.factory.create('beta.geolocation');
                    geo.getCurrentPosition(function(position) {
                      initialLocation = new google.maps.LatLng(position.latitude,position.longitude);
                      gMap.setCenter(initialLocation);";
                      if($settings['showMarker']) $map .= "setMarker(initialLocation);";         
                
                    $map .= "}, function() {
                      handleNoGeolocation(browserSupportFlag);
                    });
                } else {
                    // Browser doesn't support Geolocation
                    browserSupportFlag = false;
                    handleNoGeolocation(browserSupportFlag);
                }
            }
            
            function handleNoGeolocation(errorFlag) {
                if (errorFlag == true) {
                  initialLocation = noLocation;
                  contentString = \"Error: The Geolocation service failed.\";
                } else {
                  initialLocation = noLocation;
                  contentString = \"Error: Your browser doesn't support geolocation.\";
                }
                gMap.setCenter(initialLocation);
                gMap.setZoom(3);
            }";	

            $map .= "
            function setMarker(position){
                var contentString = 'hello';
                var image = '".$settings['markerIcon']."';
                var infowindow = new google.maps.InfoWindow({
                    content: contentString
                });
                var marker = new google.maps.Marker({
                    position: position,
                    map: gMap,
                    title:\"My Position\"
                });";
            /*    
             if($infoWindow){   
                 $map .= "google.maps.event.addListener(marker, 'click', function() {
                                infowindow.open(map,marker);
                            });";
             }
             $map .= "}";*/
            
            if($settings['usesJquery']){
              $map .=" 
              $(function(){
                //initialize();
              });
              ";
              }else{
                $map .= "
                window.onload=function(){
                  initialize();
                }
                ";
              }
              if($settings['localize']) $map .= "localize();"; 
              
             $map .= "</script>";
        return $map;
    }
    
    
    /** 
     * Function addMarker 
     * 
     * This method puts a marker in the google map generated with the function map
     * 
     * Pass an array with the options listed above in order to customize it
     * 
     * @author Marc Fernandez <info (at) marcfg (dot) com> 
     * @param array $options - options array 
     * @return string - will return all the javascript script to add the marker to the map
     * 
     */ 
    function addMarker($options){
        if($options==null) return null;
        
        if(!isset($options['latitude']) || $options['latitude']==null || !isset($options['longitude']) || $options['longitude']==null) return null;
        if (!preg_match("/[-+]?\b[0-9]*\.?[0-9]+\b/", $options['latitude']) || !preg_match("/[-+]?\b[0-9]*\.?[0-9]+\b/", $options['longitude'])) return null;        
        
        
        $marker = "<script>";  
          $marker .= "        
            gMarkers.push(
              new google.maps.Marker({
               position:new google.maps.LatLng(".$options['latitude'].",".$options['longitude']."),
               map:gMap,
               icon:'".$options['icon']."'
              }));
        ";
            /*$marker .= "
           var contentString = '".$windowText."';
            var infowindow".$id." = new google.maps.InfoWindow({
                content: contentString
            });";
        if($infoWindow){   
                 $marker .= "google.maps.event.addListener(marker".$id.", 'click', function() {
                                infowindow".$id.".open(map,marker".$id.");
                            });";
        }*/
        $marker .= "</script>";
        return $marker;
    }
    
    function autocenter()
    {
      $center="<script>";
      $center.='
      function auto_center(){
        var bounds = new google.maps.LatLngBounds(); 
        $.each(gMarkers,function (index, marker){ bounds.extend(marker.position);}); 
        gMap.fitBounds(bounds);
      }
      auto_center();
      </script>';
      return $center;
    }
  }
?> 