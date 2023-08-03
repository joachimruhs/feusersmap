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
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups',$tmp_feusersmap_columns);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'leafletmapicon;;,', '', 'after:title');

        

