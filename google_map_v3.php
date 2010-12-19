<?php

/**
 * This is a CakePHP helper that helps users to integrate google map v3
 * into their application by only writing php codes this helper depends on JQuery
 *
 * @package default
 * @author Rajib Ahmed
 * @version 0.10.12
 * 
 * CakePHP1.3
 *
 * CodeAPI: 		http://code.google.com/intl/de-DE/apis/maps/documentation/javascript/basics.html
 * Icons/Images: 	http://gmapicons.googlepages.com/home
 *
 * fixed brackets, spacesToTabs, indends, some improvements, supports multiple maps now.
 * now capable of resetting itself (full or partly) for multiple maps on a single view
 * 2010-12-18 ms
 */
class GoogleMapV3Helper extends AppHelper {

	public static $MAP_COUNT;
	public static $MARKER_COUNT;
	public static $INFO_WINDOW_COUNT;

	const API = 'http://maps.google.com/maps/api/js?';

	public $types = array('R'=>'ROADMAP','H'=>'HYBRID','S'=>'SATELLITE', 'T'=>'TERRAIN');
	private $key = null; # not needed anymore in v3?
	private $api = null;

	public function __construct() {
		# read constum config settings
		$google = (array)Configure::read('Google');
		if (!empty($google['key'])) {
			$this->key = $google['key'];
		}
		if (!empty($google['api'])) {
			$this->api = $google['api'];
		}
		if (!empty($google['zoom'])) {
			$this->_defaultOptions['map']['zoom'] = $google['zoom'];
		}
		if (!empty($google['lat'])) {
			$this->_defaultOptions['map']['lat'] = $google['lat'];
		}
		if (!empty($google['lng'])) {
			$this->_defaultOptions['map']['lng'] = $google['lng'];
		}
	}

	/**
	 * Cakephp builtin helper
	 *
	 * @var array
	 */
	public $helpers = array('Javascript', 'Html');

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
	public $infoWindows = array();

	/**
	 * google map instance varible
	 *
	 * @var string
	 */
	public $map = '';

	private $mapIds = array(); # remember already used ones (valid xhtml contains ids not more than once)


	/**
	 * settings of the helper
	 *
	 * @var array
	 */
	private $_defaultOptions = array(
		'map'=>array(
			'streetViewControl' => false,
			'navigationControl' => true,
			'mapTypeControl' => true,
			'scaleControl' => true,
			'scrollwheel' => false,
			'zoom' =>5,
			'type' =>'R',
			'typeOptions' => array(),
			'navOptions' => array(),
			'scaleOptions' => array(),
			'lat' => 51,
			'lng' => 11,
			'keyboardShortcuts' => true,
			'scaleControl' => true
		),
		'staticMap' => array(
			//'zoom' => 12
			//'lat' => 51,
			//'lng' => 11,
		),
		'localize' => true,
		'showMarker' => true,
		'showInfoWindow' => true,
		'infoWindow' => array(
			'content'=>'',
			'useMultiple'=>false, # Using single infowindow object for all
			'maxWidth'=>200,
			'lat'=>null,
			'lng'=>null,
			'pixelOffset' => 0,
			'zIndex' => 200,
			'disableAutoPan' => false
		),
		'marker'=>array(
			'autoCenter' => true,
			'icon'		=>'http://google-maps-icons.googlecode.com/files/home.png',
			'title' => ''
		),
		'div'=>array(
			'id'=>'map_canvas'
		),
		'event'=>array(
		),
		'animation' => array(
		),
		'plugins' => array(
			'keydragzoom' => false, # http://google-maps-utility-library-v3.googlecode.com/svn/tags/keydragzoom/
			'markermanager' => false, # http://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/
			'markercluster' => false, # http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerclusterer/
		),
		'autoCenterMarkers'=>false
	);


	private $_currentOptions =array();


/** Google Maps JS **/

	/**
	 * JS maps.google API url
	 * Like:
	 *  http://maps.google.com/maps/api/js?sensor=true
	 * Adds Key - more variables could be added after it with "&key=value&..."
	 * - region
	 * @param bool $sensor
	 * @param string $language (iso2: en, de, ja, ...)
	 * @param string $append (more key-value-pairs to append)
	 * @return string $fullUrl
	 * 2009-03-09 ms
	 */
	function apiUrl($sensor = true, $language = null, $append = null) {
		$url = self::API;

		$url .= 'sensor=' . ($sensor ? 'true' : 'false');
		if (!empty($language)) {
			$url .= '&language='.$language;
		}
		if (!empty($this->key)) {
			$url .= '&key='.$this->key;
		}
		if (!empty($this->api)) {
			$url .= '&v='.$this->api;
		}
		if (!empty($append)) {
			$url .= $append;
		}
		return $url;
	}
	
	
	/**
	 * @return string $currentContainerObject
	 * 2010-12-18 ms
	 */
	public function name() {
		return 'map'.self::$MAP_COUNT;
	}

	public function reset($full = true) {
		self::$MAP_COUNT = self::$MARKER_COUNT = self::$INFO_WINDOW_COUNT = 0;
		$this->markers = $this->infoWindows = array();
		if ($full) {
			$this->_currentOptions = $this->_defaultOptions;
		}	
	}

	/**
	 * This the initialization point of the script
	 * Returns the div container you can echo on the website
	 *
	 * @param array $options associative array of settings are passed
	 * @return string $divContainer
	 * @author Rajib Ahmed
	 * 2010-12-18 ms
	 */
	function map($options = array()) {
		$this->reset();
		$options = $this->_currentOptions = Set::merge($this->_defaultOptions, $options);

		if (!empty($options['autoScript'])) {
			$this->Html->script($this->apiUrl(), array('inline'=>false));
			//http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js
			//http://code.google.com/apis/gears/gears_init.js
		}

		$map = "
			gMarkers".self::$MAP_COUNT." = new Array();
			gInfoWindows".self::$MAP_COUNT." = new Array();
			/*gWindows".self::$MAP_COUNT." = new Array();*/
			var noLocation = new google.maps.LatLng(".$options['map']['lat'].", ".$options['map']['lng'].");
			var initialLocation;
			var browserSupportFlag =  new Boolean();
			var myOptions = ".$this->_mapOptions().";
		";

		#rename "map_canvas" to "map_canvas1", ... if multiple maps on one page
		if (in_array($options['div']['id'], $this->mapIds)) {
			$options['div']['id'] .= '-1'; //TODO: improve
		}
		$this->mapIds[] = $options['div']['id'];

		$map .= "
			var ".$this->name()." = new google.maps.Map(document.getElementById(\"".$options['div']['id']."\"), myOptions);
			";
		$this->map = $map;

		$result = '';
		if (!isset($options['div']) || $options['div'] !== false) {
			$options['div']['style'] = '';
			if (empty($options['div']['width'])) {
				$options['div']['width'] = '100%';
			}
			if (empty($options['div']['height'])) {
				$options['div']['height'] = '400px';
			}
			if (empty($options['div']['class'])) {
				$options['div']['class'] = 'map';
			}
			if (is_int($options['div']['width'])) {
				$options['div']['width'] .= 'px';
			}
			if (is_int($options['div']['height'])) {
				$options['div']['height'] .= 'px';
			}
			
			$options['div']['style'] .= 'width: '.$options['div']['width'].';';
			$options['div']['style'] .= 'height: '.$options['div']['height'].';';
			unset($options['div']['width']); unset($options['div']['height']);

			$defaultText = isset($options['content']) ? h($options['content']) : __('Map cannot be displayed!', true); 
			$result = $this->Html->tag('div', $defaultText, $options['div']);
		}

		return $result;
	}

	/**
	 * @param array $options
	 * - lat, lng, title
	 * @return int $markerCount or false on failure
	 * 2010-12-18 ms
	 */
	function addMarker($options) {
		if (empty($options)) {
			return false;
		}
		if(!isset($options['lat']) || !isset($options['lng'])) {
			return false;
		};
		if (!preg_match("/[-+]?\b[0-9]*\.?[0-9]+\b/", $options['lat']) || !preg_match("/[-+]?\b[0-9]*\.?[0-9]+\b/", $options['lng'])) {
			return false;
		}

		$options = array_merge($this->_currentOptions['marker'], $options);

		$marker = "
			var x".self::$MARKER_COUNT." = new google.maps.Marker({
				position:new google.maps.LatLng(".$options['lat'].",".$options['lng']."),
				map : ".$this->name().",
				icon:'".$options['icon']."',
				title:'".$options['title']."'
			});
			gMarkers".self::$MAP_COUNT.".push(
				x".self::$MARKER_COUNT."
			);
		";
		$this->map.= $marker;

		if (!empty($options['content']) && $this->_currentOptions['infoWindow']['useMultiple']) {
			$x = $this->addInfoWindow();
			$this->setContentInfoWindow($options['content'], $x);
			/*
			$marker .= "

			var window".self::$MARKER_COUNT." = new google.maps.InfoWindow({ content: '".$options['content']."',
		size: new google.maps.Size(50,50)
		});

			google.maps.event.addListener(x".self::$MARKER_COUNT.", 'click', function() {
			/ ".$this->name().".setZoom(7); /
			infowindow.setContent(gWindows[".self::$MARKER_COUNT."]);
				infowindow.setPosition(event.latLng);
				infowindow.open(map);
			});

			";
			*/
			$this->addEvent($x);

		} elseif (!empty($options['content'])) {
			if (!isset($this->_currentOptions['marker']['infoWindow'])) {
				$this->_currentOptions['marker']['infoWindow'] = $this->addInfoWindow();
			}
			$event = "
			gInfoWindows".self::$MAP_COUNT."[".$this->_currentOptions['marker']['infoWindow']."].setContent('".$this->Javascript->escapeScript($options['content'])."');
			gInfoWindows".self::$MAP_COUNT."[".$this->_currentOptions['marker']['infoWindow']."].open(".$this->name().", gMarkers".self::$MAP_COUNT."[".self::$MARKER_COUNT."]);
			";
			$this->addCustomEvent(self::$MARKER_COUNT, $event);
		}

		return self::$MARKER_COUNT++;
	}



	/**
	 * @param array $options
	 * - lat, lng, content, maxWidth, pixelOffset, zIndex
	 * @return int $windowCount
	 * 2010-12-18 ms
	 */
	public function addInfoWindow($options=array()) {
		$options = $this->_currentOptions['infoWindow'];
		$options = array_merge($options,$options);


		if(!empty($options['lat']) && !empty($options['lng'])) {
			$position = "new google.maps.LatLng(".$options['lat'].", ".$options['lng'].")";
		} else {
			$position = " ".$this->name().".getCenter()";
		}

			$windows = "
			gInfoWindows".self::$MAP_COUNT.".push( new google.maps.InfoWindow({
					position: {$position},
					content: '{$options['content']}',
					maxWidth: {$options['maxWidth']},
					pixelOffset: {$options['pixelOffset']},
					/*zIndex: {$options['zIndex']},*/
			}));
			";
		$this->map .= $windows;
		return self::$INFO_WINDOW_COUNT++;
	}

	/**
	 * @param int $marker
	 * @param int $infoWindow
	 * @return void
	 * 2010-12-18 ms
	 */
	public function addEvent($marker, $infoWindow) {
		$this->map .= "
			google.maps.event.addListener(gMarkers[{$marker}], 'click', function(){
				gInfoWindows".self::$MAP_COUNT."[$infoWindow].open(".$this->name().", this);
			});
		";
	}

	/**
	 * @param int $marker
	 * @param string $event (js)
	 * @return void
	 * 2010-12-18 ms
	 */
	public function addCustomEvent($marker, $event) {
		$this->map .= "
			google.maps.event.addListener(gMarkers".self::$MAP_COUNT."[{$marker}], 'click', function(){
				$event
			});
		";
	}

	/**
	 * @param string $custom (js)
	 * @return void
	 * 2010-12-18 ms
	 */
	function addCustom($js) {
		$this->map .= $js;
	}
	
	/**
	 * @param string $content (html/text)
	 * @param int $infoWindowCount
	 * @return void
	 * 2010-12-18 ms
	 */
	public function setContentInfoWindow($con, $index) {
		$this->map .= "
			gInfoWindows".self::$MAP_COUNT."[$index].setContent('".$this->Javascript->escapeString($con)."');";
	}




	/**
	 * This method returns the javascript for the current map container
	 * Just echo it below the map container
	 * @return string
	 * 2010-12-18 ms
	*/
	public function script() {
		$script='<script type="text/javascript">
	jQuery(function(){
		';

		$script .= $this->map;

		if($this->_defaultOptions['showMarker'] && !empty($this->markers) && is_array($this->markers)){
			$script .= implode($this->markers, " ");
		}

		if($this->_defaultOptions['autoCenterMarkers']) {
			$script .= $this->_autoCenter();
		}

		$script .= '
	});
</script>';
		self::$MAP_COUNT++;
		return $script;
	}

	/**
	 * auto center map
	 * careful: with only one marker this can result in too high zoom values!
	 * @return string $autoCenterCommands
	 * 2010-12-17 ms
	 */
	protected function _autoCenter() {
		return '
		var bounds = new google.maps.LatLngBounds();
		$.each(gMarkers'.self::$MAP_COUNT.',function (index, marker){ bounds.extend(marker.position);});
		'.$this->name().'.fitBounds(bounds);
		';
	}

	/**
	 * @return json like js string
	 * 2010-12-17 ms
	 */
	private function _mapOptions(){
		$options = $this->_currentOptions['map'];

		$mapOptions = array_intersect_key($options, array('streetViewControl' => null, 'navigationControl' => null,
			'mapTypeControl' => null,
			'scaleControl' => null,
			'scrollwheel' => null,
			'zoom' => null,
			'keyboardShortcuts' => null,
			'scaleControl' => null));
		$res = array();
		foreach ($mapOptions as $key => $mapOption) {
			$res[] = $key.': '.$this->Javascript->value($mapOption);
		}
		$res[] = 'center: noLocation';
		if (!empty($options['navOptions'])) {
			$res[] = 'navigationControlOptions: '.$this->_controlOptions('nav', $options['navOptions']);
		}
		if (!empty($options['typeOptions'])) {
			$res[] = 'mapTypeControlOptions: '.$this->_controlOptions('type', $options['typeOptions']);
		}
		if (!empty($options['scaleOptions'])) {
			$res[] = 'scaleControlOptions: '.$this->_controlOptions('scale', $options['scaleOptions']);
		}

		if (array_key_exists($options['type'], $this->types)) {
			$type = $this->types[$options['type']];
		} else {
			$type = $options['type'];
		}
		$res[] = 'mapTypeId: google.maps.MapTypeId.'.$type;

		return '{'.implode(', ', $res).'}';
	}
	
	private function _controlOptions($type, $options) {
		$mapping = array(
			'nav' => 'NavigationControlStyle',
			'type' => 'MapTypeControlStyle',
			'scale' => ''
		);
		$res = array();
		if (!empty($options['style']) && ($m = $mapping[$type])) {
			$res[] = 'style: google.maps.'.$m.'.'.$options['style'];
		}
		if (!empty($options['pos'])) {
			$res[] = 'position: google.maps.ControlPosition.'.$options['pos'];
		}
		
		return '{'.implode(', ', $res).'}';
	}

/** Google Maps Link **/

	/**
	 * returns a maps.google link
	 * @param array options:
	 * - from: neccessary (address or lat,lng)
	 * - to: 1x neccessary (address or lat,lng - can be an array of multiple destinations: array('dest1', 'dest2'))
	 * - zoom: optional (defaults to none)
	 * @return string link: http://...
	 * 2010-12-18 ms
	 */
	function link($options = array()) {
		$link = 'http://maps.google.com/maps?';

		$linkArray = array();
		if (!empty($options['from'])) {
			$linkArray[] = 'saddr='.h($options['from']);
		}

		if (!empty($options['to']) && is_array($options['to'])) {
			$to = array_shift($options['to']);
			foreach ($options['to'] as $key => $value) {
				$to .= '+to:'.$value;
			}
			$linkArray[] = 'daddr='.h($to);
		} elseif (!empty($options['to'])) {
			$linkArray[] = 'daddr='.h($options['to']);
		}

		if(!empty($options['zoom'])) {
			$linkArray[] = 'z='.(int)$options['zoom'];
		}
		//$linkArray[] = 'f=d';
		//$linkArray[] = 'hl=de';
		//$linkArray[] = 'ie=UTF8';
		return $link.implode('&', $linkArray);
	}

/** STATIC MAP **/

# Beachten Sie, dass diese URL am Zeichen "\" umbrochen wird.
# Sie können die URL jedoch auch aus dem nachfolgenden Bild kopieren.
# Der Übersichtlichkeit halber ist der aktuell verwendete API-Schlüssel nicht enthalten.
/** http://maps.google.com/staticmap?center=40.714728,-73.998672&zoom=14&size=512x512&maptype=mobile&markers=40.702147,-74.015794,blues%7C40.711614,-74.012318,greeng%7C40.718217,-73.998284,redc&key=MAPS_API_KEY&sensor=false **/


	/**
	 * Create a plain image map
	 * @param options:
	 * - size [NECCESSARY: VALxVAL, e.g. 500x400]
	 * - center: x,y [NECCESSARY, if no markers are given; else tries to take defaults if available] or TRUE/FALSE
	 * - int/string zoom [optional; if no markers are given, default value is used; if set to "auto" and ]*
	 * - markers [optional, separated by | (pipe)]
	 * - maptype [optional: roadmap/mobile; default:roadmap]
	 * @param array $attributes: html attributes for the image
	 * - title
	 * - alt (defaults to 'Map')
	 * - url (tip: you can pass $this->link(...) and it will create a link to maps.google.com)
	 * @return string $imageTag
	 * 2010-12-18 ms
	 */
	function staticMap($options = array(), $attributes = array()) {
		$defaultAttributes = array('alt' => __('Map', true));

		return $this->Html->image($this->staticMapLink($options), array_merge($defaultAttributes, $attributes));
	}

	/**
	 * Create a link to a plain image map
	 * @param options
	 * - see staticMap() for details
	 * @return string $urlOfImage: http://...
	 * 2010-12-18 ms
	 */
	function staticMapLink($options = array()) {
		$map = 'http://maps.google.com/staticmap?';
		$params = array();
		$params['sensor'] = 'false';
		$params['key'] = $this->key;
		if (!empty($options['sensor'])) {
			$params['sensor'] = 'true';
		}
		
		$defaults = $this->_defaultOptions['map'];

		if (!isset($options['center']) || $options['center'] === false) {
			# dont use it
		} elseif ($options['center'] === true && $defaults['lat'] !== null && $defaults['lng'] !== null) {
			$params['center'] = (string)$defaults['lat'].','.(string)$defaults['lng'];
		} elseif (!empty($options['center'])) {
			$params['center'] = $options['center'];
		} else {
			# try to read from markers array???
			if (isset($options['markers']) && count($options['markers']) == 1) {
				//pr ($options['markers']);
			}
		}

		if (!empty($options['zoom'])) {
			if ($options['zoom'] == 'auto') {
				if (!empty($options['markers']) && substr_count($options['zoom'],'|') > 0) {
					# let google find the best zoom value itself
				} else {
					# do something here?
				}
			} else {
				$params['zoom'] = $options['zoom'];
			}
		} else {
			$params['zoom'] = $defaults['zoom'];
		}

		if (!empty($options['maptype'])) {
			$params['maptype'] = $options['maptype'];
		}

		# {latitude},{longitude},{color}{alpha-character}
		if (!empty($options['markers'])) {
			$params['markers'] = $options['markers'];
		}

		# valXval
		if (!empty($options['size'])) {
			$params['size'] = $options['size'];
		}

		foreach ($params as $key => $value) {
			$map .= $key.'='.$value.'&';
		}
		return $map;
	}


	/**
	 * prepare markers for staticMap
	 * @param array $positions
	 * - lat: xx.xxxxxx (NECCESSARY)
	 * - lng: xx.xxxxxx (NECCESSARY)
	 * - color: red/blue/green (optional, default blue)
	 * - char: a-z (optional, default s)
	 * @return string $markers: e.g: 40.702147,-74.015794,blues|40.711614,-74.012318,greeng{|...}
	 * 2010-12-18 ms
	 */
	function staticMarkers($pos = array()) {
		//$markers = 'markers=';
		foreach ($pos as $p) {
			$lat = (is_numeric($p['lat'])?$p['lat']:null);
			$lng = (is_numeric($p['lng'])?$p['lng']:null);
			$color = (!empty($p['color'])?$p['color']:'blue');
			$char = (!empty($p['char'])?$p['char']:'');

			if ($lat == null || $lng == null) { continue; }
			$params[] = $lat.','.$lng.','.$color.''.$char;
		}

		$markers = implode('|', $params);

		return $markers;
	}




/** TODOS/EXP **/

/*
TODOS:

- animations
marker.setAnimation(google.maps.Animation.BOUNCE);

- geocoding (+ reverse)

- directions

- icons (complex)

- overlays

- fluster (for clustering?)
or
- markerManager (many markers)

- infoBox
http://google-maps-utility-library-v3.googlecode.com/svn/tags/infobox/

- ...

*/


	function geocoder() {
		$js = 'var geocoder = new google.maps.Geocoder();';
		//TODO
		
	}
	
	/**
	 * managing lots of markers!
	 * @link http://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/1.0/docs/examples.html
	 * @param options
	 * -
	 * @return void
	 * 2010-12-18 ms
	 */
	function setManager() {
		$js .= '
		var mgr'.self::$MAP_COUNT.' = new MarkerManager('.$this->name().');
		';
	}

	public function addManagerMarker($marker, $options) {
		$js = 'mgr'.self::$MAP_COUNT.'.addMarker('.$marker.');';
	}
	

	/**
	 * clustering for lots of markers!
	 * @link ?
	 * @param options
	 * -
	 * based on Fluster2 0.1.1
	 * @return void
	 */
	public function setCluster($options) {
		$js = self::$flusterScript;
		$js .= '
		var fluster'.self::$MAP_COUNT.' = new Fluster2('.$this->name().');
		';

		# styles
		'fluster'.self::$MAP_COUNT.'.styles = {}';

		$this->map .= $js;
	}

	public function addClusterMarker($marker, $options) {
		$js = 'fluster'.self::$MAP_COUNT.'.addMarker('.$marker.');';
	}

	public function initCluster() {
		$this->map .= 'fluster'.self::$MAP_COUNT.'.initialize();';
	}


	public static $flusterScript = '
function Fluster2(_map,_debug){var map=_map;var projection=new Fluster2ProjectionOverlay(map);var me=this;var clusters=new Object();var markersLeft=new Object();this.debugEnabled=_debug;this.gridSize=60;this.markers=new Array();this.currentZoomLevel=-1;this.styles={0:{image:\'http://gmaps-utility-library.googlecode.com/svn/trunk/markerclusterer/1.0/images/m1.png\',textColor:\'#FFFFFF\',width:53,height:52},10:{image:\'http://gmaps-utility-library.googlecode.com/svn/trunk/markerclusterer/1.0/images/m2.png\',textColor:\'#FFFFFF\',width:56,height:55},20:{image:\'http://gmaps-utility-library.googlecode.com/svn/trunk/markerclusterer/1.0/images/m3.png\',textColor:\'#FFFFFF\',width:66,height:65}};var zoomChangedTimeout=null;function createClusters(){var zoom=map.getZoom();if(clusters[zoom]){me.debug(\'Clusters for zoom level \'+zoom+\' already initialized.\')}else{var clustersThisZoomLevel=new Array();var clusterCount=0;var markerCount=me.markers.length;for(var i=0;i<markerCount;i++){var marker=me.markers[i];var markerPosition=marker.getPosition();var done=false;for(var j=clusterCount-1;j>=0;j--){var cluster=clustersThisZoomLevel[j];if(cluster.contains(markerPosition)){cluster.addMarker(marker);done=true;break}}if(!done){var cluster=new Fluster2Cluster(me,marker);clustersThisZoomLevel.push(cluster);clusterCount++}}clusters[zoom]=clustersThisZoomLevel;me.debug(\'Initialized \'+clusters[zoom].length+\' clusters for zoom level \'+zoom+\'.\')}if(clusters[me.currentZoomLevel]){for(var i=0;i<clusters[me.currentZoomLevel].length;i++){clusters[me.currentZoomLevel][i].hide()}}me.currentZoomLevel=zoom;showClustersInBounds()}function showClustersInBounds(){var mapBounds=map.getBounds();for(var i=0;i<clusters[me.currentZoomLevel].length;i++){var cluster=clusters[me.currentZoomLevel][i];if(mapBounds.contains(cluster.getPosition())){cluster.show()}}}this.zoomChanged=function(){window.clearInterval(zoomChangedTimeout);zoomChangedTimeout=window.setTimeout(createClusters,500)};this.getMap=function(){return map};this.getProjection=function(){return projection.getP()};this.debug=function(message){if(me.debugEnabled){console.log(\'Fluster2: \'+message)}};this.addMarker=function(_marker){me.markers.push(_marker)};this.getStyles=function(){return me.styles};this.initialize=function(){google.maps.event.addListener(map,\'zoom_changed\',this.zoomChanged);google.maps.event.addListener(map,\'dragend\',showClustersInBounds);window.setTimeout(createClusters,1000)}}
function Fluster2Cluster(_fluster,_marker){var markerPosition=_marker.getPosition();this.fluster=_fluster;this.markers=[];this.bounds=null;this.marker=null;this.lngSum=0;this.latSum=0;this.center=markerPosition;this.map=this.fluster.getMap();var me=this;var projection=_fluster.getProjection();var gridSize=_fluster.gridSize;var position=projection.fromLatLngToDivPixel(markerPosition);var positionSW=new google.maps.Point(position.x-gridSize,position.y+gridSize);var positionNE=new google.maps.Point(position.x+gridSize,position.y-gridSize);this.bounds=new google.maps.LatLngBounds(projection.fromDivPixelToLatLng(positionSW),projection.fromDivPixelToLatLng(positionNE));this.addMarker=function(_marker){this.markers.push(_marker)};this.show=function(){if(this.markers.length==1){this.markers[0].setMap(me.map)}else if(this.markers.length>1){for(var i=0;i<this.markers.length;i++){this.markers[i].setMap(null)}if(this.marker==null){this.marker=new Fluster2ClusterMarker(this.fluster,this);if(this.fluster.debugEnabled){google.maps.event.addListener(this.marker,\'mouseover\',me.debugShowMarkers);google.maps.event.addListener(this.marker,\'mouseout\',me.debugHideMarkers)}}this.marker.show()}};this.hide=function(){if(this.marker!=null){this.marker.hide()}};this.debugShowMarkers=function(){for(var i=0;i<me.markers.length;i++){me.markers[i].setVisible(true)}};this.debugHideMarkers=function(){for(var i=0;i<me.markers.length;i++){me.markers[i].setVisible(false)}};this.getMarkerCount=function(){return this.markers.length};this.contains=function(_position){return me.bounds.contains(_position)};this.getPosition=function(){return this.center};this.getBounds=function(){return this.bounds};this.getMarkerBounds=function(){var bounds=new google.maps.LatLngBounds(me.markers[0].getPosition(),me.markers[0].getPosition());for(var i=1;i<me.markers.length;i++){bounds.extend(me.markers[i].getPosition())}return bounds};this.addMarker(_marker)}
function Fluster2ClusterMarker(_fluster,_cluster){this.fluster=_fluster;this.cluster=_cluster;this.position=this.cluster.getPosition();this.markerCount=this.cluster.getMarkerCount();this.map=this.fluster.getMap();this.style=null;this.div=null;var styles=this.fluster.getStyles();for(var i in styles){if(this.markerCount>i){this.style=styles[i]}else{break}}google.maps.OverlayView.call(this);this.setMap(this.map);this.draw()};Fluster2ClusterMarker.prototype=new google.maps.OverlayView();Fluster2ClusterMarker.prototype.draw=function(){if(this.div==null){var me=this;this.div=document.createElement(\'div\');this.div.style.position=\'absolute\';this.div.style.width=this.style.width+\'px\';this.div.style.height=this.style.height+\'px\';this.div.style.lineHeight=this.style.height+\'px\';this.div.style.background=\'transparent url("\'+this.style.image+\'") 50% 50% no-repeat\';this.div.style.color=this.style.textColor;this.div.style.textAlign=\'center\';this.div.style.fontFamily=\'Arial, Helvetica\';this.div.style.fontSize=\'11px\';this.div.style.fontWeight=\'bold\';this.div.innerHTML=this.markerCount;this.div.style.cursor=\'pointer\';google.maps.event.addDomListener(this.div,\'click\',function(){me.map.fitBounds(me.cluster.getMarkerBounds())});this.getPanes().overlayLayer.appendChild(this.div)}var position=this.getProjection().fromLatLngToDivPixel(this.position);this.div.style.left=(position.x-parseInt(this.style.width/2))+\'px\';this.div.style.top=(position.y-parseInt(this.style.height/2))+\'px\'};Fluster2ClusterMarker.prototype.hide=function(){this.div.style.display=\'none\'};Fluster2ClusterMarker.prototype.show=function(){this.div.style.display=\'block\'};
function Fluster2ProjectionOverlay(map){google.maps.OverlayView.call(this);this.setMap(map);this.getP=function(){return this.getProjection()}}Fluster2ProjectionOverlay.prototype=new google.maps.OverlayView();Fluster2ProjectionOverlay.prototype.draw=function(){};
\'';

}