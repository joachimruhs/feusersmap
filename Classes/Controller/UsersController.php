<?php

declare(strict_types=1);

namespace WSR\Feusersmap\Controller;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\Filesystem\Filesystem;


/**
 * This file is part of the "FeUsersMap" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Joachim Ruhs
 */

/**
 * UsersController
 */
class UsersController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	public function initializeObject() {
		//		$this->_GP = $this->request->getArguments();
		$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$this->conf['storagePid'] = $configuration['persistence']['storagePid'];
        
		/** @var TypoScriptService $typoScriptService */
/*
   		$frontend = $GLOBALS['TSFE'];

		$typoScriptService = GeneralUtility::makeInstance('TYPO3\CMS\Core\TypoScript\TypoScriptService');
		$this->configuration = $typoScriptService->convertTypoScriptArrayToPlainArray($frontend->tmpl->setup['plugin.']['tx_feusersmap.']);
*/	
//        $fullTypoScript = $this->request->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_feusersmap.'] ;
//	    $this->configuration = $this->request->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_feusersmap.'];

//		$this->settings = $this->configuration['settings.'];
//		$this->conf['storagePid'] = $this->configuration['persistence.']['storagePid'];

    }


    /**
     * usersRepository
     *
     * @var \WSR\Feusersmap\Domain\Repository\UsersRepository
     */
    protected $usersRepository = null;

    /**
     * @param \WSR\Feusersmap\Domain\Repository\UsersRepository $usersRepository
     */
    public function injectUsersRepository(\WSR\Feusersmap\Domain\Repository\UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

 
 	/**
	 * groupsRepository
	 * 
     * @var \WSR\Feusersmap\Domain\Repository\GroupsRepository $groupsRepository
 	 */
	protected $groupsRepository = NULL;

    /**
     * Inject a groupsRepository to enable DI
     *
     * @param \WSR\Feusersmap\Domain\Repository\GroupsRepository $groupsRepository
     * @return void
     */
    public function injectGroupsRepository(\WSR\Feusersmap\Domain\Repository\GroupsRepository $groupsRepository) {
        $this->groupsRepository = $groupsRepository;
    }


	/**
	 * action geocode
	 * @return \stdclass $latLon
	 */
	public function geocodeAction($theAddress) {
//		$requestArguments = $this->request->getParsedBody()['tx_feusersmap_map'];

		$address = urlencode($theAddress['address'] ?? '');
		$country = urlencode($theAddress['country'] ?? '');

		$latLon = new \stdClass();
		$latLon->lat = 0;
		$latLon->lon = 0;
		$latLon->status = '';

        if($address == '+') return $latLon;
/*
https://nominatim.openstreetmap.org/search/elzstr.%2010%20rheinhausen?format=json&addressdetails=1&limit=1&polygon_svg=1
max 1 call/sec
*/

		$apiURL = "https://nominatim.openstreetmap.org/search?q=$address,+$country&format=json&limit=1";
		$addressData = $this->get_webpage($apiURL);
        $addressData = $addressData ?? '{}';
        if ($addressData == '{}' || $addressData == '[]') {
            $this->flashMessage('Error in geocoding by nominatim.openstreetmap.org!', '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $latLon;
        }

		$coordinates[1] = json_decode($addressData)[0]->lat;
		$coordinates[0] = json_decode($addressData)[0]->lon;

		$latLon->lat = (float) $coordinates[1];
		$latLon->lon = (float) $coordinates[0];
		if ($latLon->lat) 
			$latLon->status = 'OK';
		else 
			$latLon->status = 'NOT FOUND';

		return $latLon;
	}

	function get_webpage($url) {
		$sessions = curl_init();
		curl_setopt($sessions, CURLOPT_URL, $url);
		curl_setopt($sessions, CURLOPT_HEADER, 0);
		curl_setopt($sessions, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($sessions, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
		$data = curl_exec($sessions);
		curl_close($sessions);
		return $data;
	}




    /**
     * action list
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        $users = $this->usersRepository->findAll();
        $this->view->assign('users', $users);
        return $this->htmlResponse();
    }

    /**
     * action map
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function mapAction(): \Psr\Http\Message\ResponseInterface
    {
        $fullTypoScript = $this->request->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_feusersmap.'] ;
	    $this->configuration = $this->request->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_feusersmap.'];
//krexx($this->configuration);
        
    
		$iconPath = 'fileadmin/ext/feusersmap/Resources/Public/MapIcons/';

   		if (!is_dir(Environment::getPublicPath() . '/' . $iconPath)) {
            $fileSystem = new FileSystem();
            if (Environment::getPublicPath() != Environment::getProjectPath()) {
                //  we are in composerMode
    			$sourceDir = Environment::getProjectPath() . '/vendor/wsr/feusersmap/Resources/Public/';
            } else {
                $sourceDir = 'typo3conf/ext/feusersmap/Resources/Public/';
            }
            $fileSystem->mirror($sourceDir, 'fileadmin/ext/feusersmap/Resources/Public/');
			$this->addFlashMessage('Directory ' . $iconPath . ' created for use with own mapIcons!', '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
        }

        $this->updateLatLon();

		$arguments = $this->request->getParsedBody()['tx_feusersmap_map'] ?? '';
        if ($arguments) $requestArguments = $arguments;
        
        $requestArguments['address'] = $requestArguments['address'] ?? '';
        $requestArguments['categories'] = $requestArguments['categories'] ?? [];
        // if no request arguments are given set them here
        if (strlen($requestArguments['address']) == 0)
        $requestArguments['address'] = $this->settings['searchAddress'];
        $requestArguments['country'] = $requestArguments['country'] ?? 'Deutschland';
        $requestArguments['radius'] = $requestArguments['radius'] ?? $this->settings['searchRadius'];
          
        $theAddress['address'] = $requestArguments['address'];
        $theAddress['country'] = $requestArguments['country'];
        
        $latLon = $this->geocodeAction($theAddress);

        $this->_GP['categories'] = $requestArguments['categories'] ?? [];       

		$locations = $this->usersRepository->findLocationsInRadius($latLon, $requestArguments['radius'], $this->_GP['categories'], $this->conf['storagePid']);
//        $markerJS = $this->getMarkerJS($locations, $categories, $latLon, $radius);

        if(is_array($locations) && count($locations) == 0) {
			$this->flashMessage('Feusersmap', 'No locations found in radius ' . $requestArguments['radius'] . ' km',
					\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
            $requestArguments['radius'] = $this->settings['searchRadius'];
    		$locations = $this->usersRepository->findLocationsInRadius($latLon, $requestArguments['radius'], $this->_GP['categories'], $this->conf['storagePid']);

// krexx($this->request->getQueryParams());
// This get the GET params 
/*
            return (new ForwardResponse('details'))
                ->withControllerName('Users')
                ->withExtensionName('feusersmap')
                ->withArguments(['locationUid' => '2'])
            ;
*/
        }
  
 		// field images
		if (is_array($locations)) {
			for ($i = 0; $i < count($locations); $i++) {
                // hide password
                $locations[$i]['password'] = '';

				$address = $locations[$i]['address'] ?? '';
				$description = $locations[$i]['description'] ?? '';
				$locations[$i]['address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', $locations[$i]['address']);  
	
				$locations[$i]['infoWindowAddress'] = str_replace(array("\r\n", "\r", "\n"), '<br />', htmlspecialchars($address, ENT_QUOTES));
	
				$locations[$i]['infoWindowDescription'] = str_replace(array("\r\n", "\r", "\n"), '<br />', htmlspecialchars($description, ENT_QUOTES));

                if ($this->settings['useGroupLeafletmapicons']) {
                    if (count($this->_GP['categories']) == 1) {
                        // get the lefletMapIcon of the usergroup if only one category is selected   
                        $locations[$i]['leafletmapicon'] = $this->groupsRepository->getLeafletIcon($this->_GP['categories'][0]);
                    } else {
                        $locations[$i]['leafletmapicon'] = $this->groupsRepository->getLeafletIcon($locations[$i]['usergroup']);
                    }
                }
				if ($locations[$i]['image'] > 0) {
						$images = $this->usersRepository->getImages($locations[$i]['uid'], 'fe_users');
    					$locations[$i]['images'] =	$images;				

				}
// not used yet
//                $locations[$i]['categories'] = $this->usersRepository->getSortedCategoriesOfFeUser($locations[$i]['uid'], $locations[$i]['usergroup']);
			}
		}
        //$categories = $this->groupsRepository->findAllCategories($this->conf['storagePid']);
		//new: get categories/usergroups from settings in backend form
		$pidsFromBackendForm = $this->settings['categories'];
		$definedPids = explode (",", str_replace("fe_groups_","",$pidsFromBackendForm));
		$categories = $this->groupsRepository->findDefinedCategories($definedPids);

        // get the parents of subgroup        
		$arr = [];
        for($i = 0; $i < count($categories); $i++) {
            $arr[$i]['parent'] = 0;
        }
		for($i = 0; $i < count($categories); $i++) {
			$arr[$i]['uid'] = $categories[$i]['uid'];
            if ($categories[$i]['subgroup']) {
                $parent = $this->groupsRepository->getParent($categories[$i]['subgroup']);
                for ($j = 0; $j < count($categories); $j++) {
                    if ($categories[$j]['uid'] == $categories[$i]['subgroup']) $arr[$j]['parent'] = $parent;
                }
            }
			$arr[$i]['name'] = $categories[$i]['title'];	
			$arr[$i]['leafletmapicon'] = $categories[$i]['leafletmapicon'];	
		}	

	
		if (!count($arr)) {
			$this->addFlashMessage('No location categories found, please insert some first!', '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
		} else {
			$categories = $this->groupsRepository->buildTree($arr);
		}
        $markerJS = $this->getMarkerJS($locations, $categories, $latLon, $requestArguments['radius']);

        $this->view->assign('address', $theAddress['address']);
        $this->view->assign('categories', $categories);
        $this->view->assign('selectedCategories', $requestArguments['categories']);
        $this->view->assign('selectedRadius', $requestArguments['radius']);
        $this->view->assign('markerJS', $markerJS);
        
        return $this->htmlResponse();
    }

	protected function updateLatLon() {
        
		$addresses = $this->usersRepository->updateLatLon($this->conf['storagePid']);
		for ($i = 0; $i < count($addresses); $i++) {	
			$theAddress = array (
				'uid' => $addresses[$i]['uid'],		
				'address' => $addresses[$i]['address'] . ' ' . $addresses[$i]['zip'] . ' ' . $addresses[$i]['city'],		
				'country' => $addresses[$i]['country'],		
			);
			sleep(rand(1, 3)); 

			$latLon = $this->geocodeAction($theAddress);

			if ($latLon->status == 'OK') {
				$address['latitude'] = $latLon->lat;
				$address['Longitude'] = $latLon->lon;
				$this->usersRepository->updateLatitudeLongitude($theAddress['uid'], $latLon->lat, $latLon->lon);
				$this->flashMessage('Feusersmap geocoder', 'Geocoded ' .  ' ' . $addresses[$i]['first_name'] . ' ' . $addresses[$i]['name'] . ' ' .$theAddress['address'] . ' ' . $latLon->status,
					\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
			}
			else {
				$this->flashMessage('Feusersmap geocoder', 'could not geocode ' . $addresses[$i]['first_name'] . ' ' . $addresses[$i]['name'] . ' ' . $latLon->status,
					\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
				$this->usersRepository->setMapgeocode($theAddress['uid'], 0);
			}
				
		}
	}
    
    
    
    /**
     * action details
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detailsAction(): \Psr\Http\Message\ResponseInterface
    {
//		$requestArguments = $this->request->getParsedBody()['tx_feusersmap_map'];

        $locationUid = $this->request->getArgument('locationUid');
        $user = $this->usersRepository->getUser($locationUid);
        $user['categories'] = $this->usersRepository->getSortedCategoriesOfFeUser($user['uid'], $user['usergroup']);
		$images = $this->usersRepository->getImages($locationUid, 'fe_users');

        $this->view->assign('images', $images);
        $this->view->assign('user', $user);
        return $this->htmlResponse();
    }

	protected function getMarkerJS($locations, $categories, $latLon, $radius) {
        if (!$locations) return '';
        
		$out = '';

		// remove marker from map
       
		$out .= 'var markerGroup = L.featureGroup(); //.addTo(map);

			for(i=0;i<marker.length;i++) {
				map.removeLayer(marker[i]);
				markerClusterGroup.removeLayer(marker[i]);
			}

			marker = [];
			markerClusterGroup = L.markerClusterGroup();
			';
			
		for ($i = 0; $i < count($locations); $i++) {
			$lat = $locations[$i]['latitude'];
			$lon = $locations[$i]['longitude'];
			
			if (!$lat) continue;

			if ($locations[$i]['leafletmapicon']) {
			$out .= '
		
				var mapIcon' . $i . ' = L.icon({
					iconUrl: "/fileadmin/ext/feusersmap/Resources/Public/MapIcons/' . $locations[$i]['leafletmapicon'] .'",
					iconSize:     [' . $this->settings["markerIconWidth"] . ' , ' . $this->settings["markerIconHeight"] . ' ], // size of the icon
					iconAnchor:   [' . intval($this->settings["markerIconWidth"] / 2) . ' , ' . $this->settings["markerIconHeight"] . ' ]
				});
				marker[' . $i . '] = L.marker([' . $lat . ',' . $lon . '], {icon: mapIcon' . $i . '}).addTo(markerGroup);
                oms.addMarker(marker[' . $i .']);  // <-- here
			';
			
			} else {
				$out .= "marker[$i] = L.marker([$lat, $lon]).addTo(markerGroup);
                    oms.addMarker(marker[" . $i ."]);  // <-- here
				";
			}

			// infoWindows
			$out .= $this->renderFluidTemplate('LocationListInfoWindow.html', array('location' => $locations[$i], 'categories' => $categories, 'i' => $i,
																						'startingPoint' => $latLon, 'settings' => $this->settings));
			
		} // for

		if ($this->settings['enableMarkerClusterer'] == 1) {
			$out .= '
			markerClusterGroup = L.markerClusterGroup();
			markerClusterGroup.clearLayers();
			map.removeLayer(markerClusterGroup);
			markerClusterGroup = L.markerClusterGroup();
			for (var i = 0; i < marker.length; i++) {
				markerClusterGroup.addLayer(marker[i]);
			}
			map.addLayer(markerClusterGroup);
			map.fitBounds(markerClusterGroup.getBounds());
			';				
		} else {
			$out .= 'markerGroup = L.featureGroup(marker).addTo(map);
					map.fitBounds(markerGroup.getBounds());';
		}
		return $out;
	}


	/**
	 * Renders the fluid template
	 * @param string $template
	 * @param array $assign
	 * @return string
	 */
	public function renderFluidTemplate($template, Array $assign = array()) {
      	$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

		$templateRootPath = $this->configuration['view.']['templateRootPaths.'][1];

		if (!$templateRootPath) 	
		$templateRootPath = $this->configuration['view.']['templateRootPath.'][0];
		
		$templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($templateRootPath . 'Users/' . $template);
		$view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename($templatePath);
		$view->assignMultiple($assign);
        $view->setFormat('html');



        if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() > 11)
            $view->setRequest($this->request);
		return $view->render();
	}




	/**
	 * Flash a message
	 *
	 * @param string title 
	 * @param string message
	 * 
	 * @return void
	 */
	private function flashMessage($title, $message, $severity = \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING) {
		$this->addFlashMessage(
			$message,
			$title,
			$severity,
			$storeInSession = TRUE
		);
	}	



}

