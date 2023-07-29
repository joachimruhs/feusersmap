<?php
defined('TYPO3') || die();

(static function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_feusersmap_domain_model_users', 'EXT:feusersmap/Resources/Private/Language/locallang_csh_tx_feusersmap_domain_model_users.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_feusersmap_domain_model_users');
})();
