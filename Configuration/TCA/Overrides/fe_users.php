<?php
defined('TYPO3') or die();

$tmp_feusersmap_columns = array(

	'leafletmapicon' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_domain_model_users.mapicon',
		'config' => [
		    'type' => 'select',
			'renderType' => 'selectSingle',
		    'items' => [
		        [ '', 0 ],
			],
			'fileFolder' => 'fileadmin/ext/feusersmap/Resources/Public/Icons/',
			'fileFolder_extList' => 'png,jpg,jpeg,gif',
			'fileFolder_recursions' => 0,
			'fieldWizard' => [
	            'selectIcons' => [
	                'disabled' => false,
	            ],
	        ],
		    'size' => 1,
		    'minitems' => 0,
		    'maxitems' => 1,
		],

	),
	'mapgeocode' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_domain_model_users.mapgeocode',
		'config' => array(
			'type' => 'check',
			'default' => '1',
		),
	),
	'latitude' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_domain_model_users.latitude',
		'config' => array(
			'type' => 'input',
			'default' => '',
		),
	),
	'longitude' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_domain_model_users.longitude',
		'config' => array(
			'type' => 'input',
			'default' => '',
		),
	),

);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tmp_feusersmap_columns);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'leafletmapicon,mapgeocode,latitude,longitude;;,', '', 'after:country');

        

