<?php

class GoogleMapV3Helper extends Helper {

    public $helpers=array('Javascript', 'Html');

    public $markers = array();

    public $map = null;


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
        'id'=>'map_canvas'
       ),
      'usesJquery'=>true,
      'autoCenterMarkers'=>true
      );


      public function to_script(){
        $script='<script type="text/javascript">
        $(function(){



        ';

        $script.=$this->map;

        if($this->_defaultSettings['showMarker'] && !empty($this->markers) && is_array($this->markers)){
          $script.=implode($this->markers, " ");
        }

        if($this->_defaultSettings['autoCenterMarkers'])
        { $script.= '
        var bounds = new google.maps.LatLngBounds();
        $.each(gMarkers,function (index, marker){ bounds.extend(marker.position);});
        gMap.fitBounds(bounds);
        ';
        }

        $script.='
        });
        </script>';

        return $script;
      }


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

  }
?>

