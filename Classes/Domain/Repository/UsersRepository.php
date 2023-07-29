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
	public function findLocationsInRadius($latLon, $radius, $categoryList, $storagePid) {
		$radius = intval($radius);
		$lat = $latLon->lat;
		$lon =  $latLon->lon;

        $categoryList = @implode(',', $categoryList);

        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $sys_language_uid = $context->getPropertyFromAspect('language', 'id'); 
        
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('fe_users');

		$queryBuilder->from('fe_users', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);
		$storagePidList = implode(',', $arrayOfPids);

		if ($language  && $sys_language_uid) {
		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`,

			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e , sys_category_record_mm m
						where  m.uid_foreign = d.uid
						and e.sys_language_uid = ' . intval($sys_language_uid) . '
						and e.l10n_parent = m.uid_local
						and d.uid = a.uid
						and e.pid in (' . $storagePidList  . ')
					) as categories			

			');
		} else {
		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`
			');
			
		}			


		$queryBuilder->orderBy('distance');

        $queryBuilder->having('`distance` <= ' . $queryBuilder->createNamedParameter($radius, \PDO::PARAM_INT));

		$result =  $queryBuilder->execute()->fetchAll();
        if ($categoryList)
            $result = $this->filterCategories($result, $categoryList);
		return $result;
	}
    
/*
			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e , sys_category_record_mm m
						where m.uid_local = e.uid
						and m.uid_foreign = d.uid
						and e.sys_language_uid = 0
						and d.uid = a.uid
						and e.pid in (' . $storagePidList  . ')
					) as categories
 */   
    
    


	/*
	 * filterCategories	
	 * @param array $result
	 * @param string $categoryList
	 *
	 * @return array
	 */
    protected function filterCategories($result, $categoryList)
    {
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
