<?php

declare(strict_types=1);

namespace WSR\Feusersmap\Domain\Repository;


use Doctrine\DBAL\Connection;
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
        $queryBuilder->orderBy('title');
        $result = $queryBuilder->execute()->fetchAll();
    	return $result;		
	}

	/*
	 *	find categories defined in backend form
	 *
	 *	@param array $definedPids
	 *	@return array
	 */	
	function findDefinedCategories($definedPids) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_groups');

		$queryBuilder->select('*')
		->from('fe_groups')
		->where(
			$queryBuilder->expr()->in(
				'uid',
				$queryBuilder->createNamedParameter($definedPids, Connection::PARAM_INT_ARRAY)
			)
		);			
		$queryBuilder->andWhere(
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter('', \PDO::PARAM_INT)),
						$queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter('', \PDO::PARAM_INT))
					)
			);
		$queryBuilder->orderBy('title');
		$result = $queryBuilder->execute()->fetchAll();
		return $result;		
	}


}
