<?php

declare(strict_types=1);

namespace WSR\Feusersmap\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;


/**
 * This file is part of the "FeUsersMap" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Joachim Ruhs
 */

/**
 * The repository for Users
 */
class GroupsRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

	/*
	 *	get parent
	 *
	 *	@param int $uid
	 *
	 *	@return array
	 */	
	function getParent($subgroup) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

        $queryBuilder->select('uid')
		->from('fe_groups')
		->where(
			$queryBuilder->expr()->eq(
				'subgroup',
				$queryBuilder->createNamedParameter($subgroup, \PDO::PARAM_INT)
			)
		);			
		$result = $queryBuilder->execute()->fetchAll();
    	return $result[0]['uid'];		
	}


	function buildTree(array &$elements, $parentId = 0) {
		$branch = [];
		foreach ($elements as &$element) {
			if ($element['parent'] == $parentId) {
				$children = $this->buildTree($elements, $element['uid']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[$element['uid']] = $element;
				unset($element);
			}
		}
		return $branch;
	}

	/*
	 *	get leafletmapicon
	 *
	 *	@param int $usergroup
	 *	
	 *	@return string
	 */	
	function getLeafletIcon($usergroup) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_groups');

        $queryBuilder->select('leafletmapicon')
		->from('fe_groups')
		->where(
			$queryBuilder->expr()->eq(
				'uid',
				$queryBuilder->createNamedParameter($usergroup, \PDO::PARAM_INT)
			)
		);			
		$result = $queryBuilder->execute()->fetchAll();
    	return $result[0]['leafletmapicon'];		
	}




	/*
	 *	getImage
	 *
	 *	@param int $userUid	
	 *	@return array
	 */	
	function getImages($userUid, $tablenames) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('sys_file_reference');

        $queryBuilder->select('*')
		->from('sys_file_reference')
        ->join(
           'sys_file_reference',
           'sys_file',
           'f',
           $queryBuilder->expr()->eq('f.uid', $queryBuilder->quoteIdentifier('uid_local'))
        )
		->where(
			$queryBuilder->expr()->eq(
				'uid_foreign',
				$queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT)
			)
		)
        -> andWhere (
    			$queryBuilder->expr()->eq(
    				'tablenames',
    				$queryBuilder->createNamedParameter($tablenames, \PDO::PARAM_STR)
    			)
        );			
        $queryBuilder->orderBy('identifier', 'asc');

		$result = $queryBuilder->execute()->fetchAll();

    	return $result;		

/*
        $uid_local = $result[0]['uid_local'];

		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('sys_file');

        $queryBuilder->select('*')
		->from('sys_file')
		->where(
			$queryBuilder->expr()->eq(
				'uid',
				$queryBuilder->createNamedParameter($uid_local, \PDO::PARAM_INT)
			)
		);
		$result = $queryBuilder->execute()->fetchAll();
    	return $result[0]['identifier'];		
 */
    }    




	/*
	 *	find allCategories
	 *
	 *	@param int $storagePid
	 *	@return array
	 */	
	function findAllCategories($storagePid) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_groups');

        $queryBuilder->select('*')
		->from('fe_groups')
		->where(
			$queryBuilder->expr()->eq(
				'pid',
				$queryBuilder->createNamedParameter($storagePid, \PDO::PARAM_INT)
			)
		);			
		$queryBuilder->andWhere(
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter('', \PDO::PARAM_INT)),
						$queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter('', \PDO::PARAM_INT))
					)
            );
		$result = $queryBuilder->execute()->fetchAll();
    	return $result;		
	}

	/**
	 * search for records which need to be updated lat lon when coordinates are 0.0 and
	 * mapgeocode = 1
	 * @param string $storagePid
	 * 
	 * @return array
	 */
	public function updateLatLon($storagePid) {

		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_myttaddressmap_domain_model_address');

		$queryBuilder->select('*')->from('fe_users', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);

		$queryBuilder->where(
			$queryBuilder->expr()->in(
				'a.pid',
				$queryBuilder->createNamedParameter(
					$arrayOfPids,
					\Doctrine\DBAL\Connection::PARAM_INT_ARRAY
				)
			)
		);		

		$queryBuilder->andWhere(
				$queryBuilder->expr()->andX(
					$queryBuilder->expr()->eq('mapgeocode', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT))
				),
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('latitude', $queryBuilder->createNamedParameter('0.0', \PDO::PARAM_STR)),
						$queryBuilder->expr()->eq('longitude', $queryBuilder->createNamedParameter('0.0', \PDO::PARAM_STR))
					),
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('latitude', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)),
						$queryBuilder->expr()->eq('longitude', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
					),
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->isNull('latitude', $queryBuilder->createNamedParameter(NULL, \PDO::PARAM_NULL)),
						$queryBuilder->expr()->isNull('longitude', $queryBuilder->createNamedParameter(NULL, \PDO::PARAM_NULL))
					)
				)
				
		);
		$result = $queryBuilder->execute()->fetchAll();
		return $result;
	}




}
