<?php
defined('TYPO3') || die();

use TYPO3\CMS\Core\Utility\GeneralUtility;


(static function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Feusersmap',
        'Map',
        [
            \WSR\Feusersmap\Controller\UsersController::class => 'map, details'
        ],
        // non-cacheable actions
        [
            \WSR\Feusersmap\Controller\UsersController::class => 'map, details'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Feusersmap',
        'Details',
        [
            \WSR\Feusersmap\Controller\UsersController::class => 'details'
        ],
        // non-cacheable actions
        [
            \WSR\Feusersmap\Controller\UsersController::class => 'details'
        ]
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    map {
                        iconIdentifier = feusersmap-plugin-map
                        title = LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_map.name
                        description = LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_map.description
                        tt_content_defValues {
                            CType = list
                            list_type = feusersmap_map
                        }
                    }
                    details {
                        iconIdentifier = feusersmap-plugin-details
                        title = LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_details.name
                        description = LLL:EXT:feusersmap/Resources/Private/Language/locallang_db.xlf:tx_feusersmap_details.description
                        tt_content_defValues {
                            CType = list
                            list_type = feusersmap_details
                        }
                    }
                }
                show = *
            }
       }'
    );
    

//    GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
//    ->registerImplementation(\fe_users::class, \WSR\Feusersmap\Domain\Model\Users::class);



})();
