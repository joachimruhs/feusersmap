<?php
defined('TYPO3') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Feusersmap',
    'Map',
    'Feusersmap(Map)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Feusersmap',
    'Details',
    'Feusersmap(Details)'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['feusersmap_map']
	= 'select_key, recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['feusersmap_map']
	= 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	'feusersmap_map',
	'FILE:EXT:feusersmap/Configuration/FlexForms/PluginFeusersmap.xml'
);
