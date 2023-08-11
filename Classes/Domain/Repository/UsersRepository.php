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
class UsersRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

	/*
	 *	get FE user
	 *
	 *	@param int $uid
	 *
	 *	@return array
	 */	
	function findByUidOverride($uid) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

        $queryBuilder->select('*')
		->from('fe_users')
		->where(
			$queryBuilder->expr()->eq(
				'uid',
				$queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
			)
		);			


		$result = $queryBuilder->execute()->fetchAll();
    	return $result[0];		
	}

	/*
	 *	getUser
	 *
	 *	@param int $userUid
	 *	@return array
	 */	
	function getUser($userUid) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

        $queryBuilder->select('*')
		->from('fe_users')
		->where(
			$queryBuilder->expr()->eq(
				'uid',
				$queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT)
			)
		);			
		$result = $queryBuilder->execute()->fetchAll();

        
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

		$queryBuilder->from('fe_users', 'a');
		$queryBuilder->selectLiteral(
			'a.uid, 
			 (SELECT GROUP_CONCAT(g.title ORDER BY g.title SEPARATOR \', \') from fe_groups g
            	where FIND_IN_SET(g.uid, a.usergroup)
                and a.uid = ' . $userUid .
                
                ') as categories
        ')
		->where(
			$queryBuilder->expr()->eq(
				'uid',
				$queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT)
			)
		);			
        
        

		$result1 = $queryBuilder->execute()->fetchAll();
        $result[0]['categories'] = $result1[0]['categories'];        



    	return $result;		
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
	 *	update latitude and longitude for a FE user
	 *
	 *	@param int $uid
	 *	@param float $lat
	 *	@param float $lon
	 *
	 *	@return array
	 */	
	function updateLatitudeLongitude($uid, $lat, $lon) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

        $queryBuilder->update('fe_users')
		->where(
			$queryBuilder->expr()->eq(
				'uid',
				$queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
			)
		)
        ->set('latitude', $lat)
        ->set('longitude', $lon)
        ->executeStatement();
    	return;		
	}


	/*
	 *	setMapgeocode
	 *
	 *	@param int $uid
	 *	@param int $geocode
	 *
	 *	@return array
	 */	
	function setMapgeocode($uid, $mapgeocode) {
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

        $queryBuilder->update('fe_users')
		->where(
			$queryBuilder->expr()->eq(
				'uid',
				$queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
			)
		)
        ->set('mapgeocode', $mapgeocode)
        ->executeStatement();
    	return;		
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



    /*    
	 * Find locations within radius
	 *
	 * @param stdClass  $latLon
	 * @param int  $radius
	 * @param array $categoryList
	 * @param string $storagePid
	 * @param int  $limit
	 * @param int  $page
	 * 
	 * @return QueryResultInterface|array the locations
	 */
	public function findLocationsInRadius($latLon, $radius, $categories, $storagePid) {
		$radius = intval($radius);
		$lat = $latLon->lat;
		$lon =  $latLon->lon;

		if ($categories)
    		$categories = @implode(',', $categories);
		// sanitizing categories						 
		if ($categories && preg_match('/^[0-9,]*$/', $categories) != 1) {
			$categories = '';
		}		
        $categoryList = $categories;
        
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $sys_language_uid = $context->getPropertyFromAspect('language', 'id'); 
        
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

		$queryBuilder->from('fe_users', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);
		$storagePidList = implode(',', $arrayOfPids);

		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`
			, (SELECT GROUP_CONCAT(g.title ORDER BY g.title SEPARATOR \', \') from fe_groups g
            	where FIND_IN_SET(g.uid, a.usergroup) ) as categories
        ');

		$queryBuilder->orderBy('distance');
		$queryBuilder->addOrderBy('name', 'asc');
        $queryBuilder->having('`distance` <= ' . $queryBuilder->createNamedParameter($radius, \PDO::PARAM_INT));

		$result =  $queryBuilder->execute()->fetchAll();
        if ($categoryList)
            $result = $this->filterCategories($result, $categoryList);
		return $result;
	}
    

	/*
	 * filterCategories	
	 * @param array $result
	 * @param string $categoryList
	 *
	 * @return array
	 */
    protected function filterCategories($result, $categoryList)
    {
        $newResult = [];
        $categories = explode(',', $categoryList ?? '');
        for ($i = 0; $i < count($result); $i++) {
            for ($j = 0; $j < count($categories); $j++) {
                $usergroups = explode(',', $result[$i]['usergroup']);
                for ($k = 0; $k < count($usergroups); $k++) {
                    if ($categories[$j] == $usergroups[$k]) {
                        $newResult[] = $result[$i];
                        continue 3;
                    }
                }
            }
        }
        return $newResult;
    }

	/* function getSortedCategoriesOfFeUser
	 * 	
	 * @param int $feUserUid
	 * @param string $usergroup
	 *
	 * @return string
	 */
    public function getSortedCategoriesOfFeUser($feUserUid, $usergroup)
    {
        $userGroups = explode(',', $usergroup);
        for ($i = 0; $i < count($userGroups); $i++) {
    		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
    			->getQueryBuilderForTable('fe_groups');
    		$queryBuilder->from('fe_groups', 'g');
            $queryBuilder->select('g.title')
            ->from('fe_groups');
            $queryBuilder->where(
                $queryBuilder->expr()->eq(
                    'g.uid',
                    $queryBuilder->createNamedParameter(
                        $userGroups[$i],
                        \PDO::PARAM_INT
                    )
                )
            );		
            $result =  $queryBuilder->execute()->fetchAll();
            if ($i == 0)
                $categories = $result[0]['title'];
            else 
                $categories .= ', ' . $result[0]['title'];
        }
        return $categories;
    }
    

	/* not used yet
	 * 	
	 * @param string $categories
	 * @param QueryBuilder $queryBuilder
	 *
	 * @return QueryBuilder
	 */
    protected function addCategoryQueryPart($categoryList, QueryBuilder $queryBuilder): QueryBuilder
    {
		$arrayOfCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $categoryList, TRUE);

        if (!empty($arrayOfCategories)) {
			$expression = $queryBuilder->expr();


		    $queryBuilder->innerJoin(
				'a',
				'fe_groups',
				'g',

/*

                $expression->andX(
                    $expression->eq('a.usergroup', 'g.uid'),
                    $expression->eq(
						'c.tablenames',
						$queryBuilder->createNamedParameter('tt_address')
                    ),
					$expression->eq(
						'c.fieldname',
						$queryBuilder->createNamedParameter('categories')
					)

                )
*/
            );


            for ($i = 0; $i < count($arrayOfCategories); $i++) {

    			$queryBuilder->orWhere(
                    $expression->andX(
                        $expression->eq(
                        'g.uid',
                        $queryBuilder->createNamedParameter($arrayOfCategories[$i], \PDO::PARAM_INT)
                        ),
                        $expression->in(
                        'g.uid',
                        $queryBuilder->createNamedParameter($arrayOfCategories, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                        )
                    )
                );
                
            }

		}
		return $queryBuilder;
	}
    


}
