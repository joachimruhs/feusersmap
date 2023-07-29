<?php

declare(strict_types=1);

namespace WSR\Feusersmap\Domain\Model;


/**
 * This file is part of the "FeUsersMap" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 
 */

/**
 * Users
 */
//class Users extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser

class Users extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * latitude
     *
     * @var int
     */
    protected $latitude = 0;

    /**
     * longitude
     *
     * @var int
     */
    protected $longitude = 0;

    /**
     * mapicon
     *
     * @var string
     */
    protected $mapicon = '';

    /**
     * mapgeocode
     *
     * @var bool
     */
    protected $mapgeocode = false;

    /**
     * Returns the latitude
     *
     * @return int
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude
     *
     * @param int $latitude
     * @return void
     */
    public function setLatitude(int $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Returns the longitude
     *
     * @return int
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude
     *
     * @param int $longitude
     * @return void
     */
    public function setLongitude(int $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Returns the mapicon
     *
     * @return string
     */
    public function getMapicon()
    {
        return $this->mapicon;
    }

    /**
     * Sets the mapicon
     *
     * @param string $mapicon
     * @return void
     */
    public function setMapicon(string $mapicon)
    {
        $this->mapicon = $mapicon;
    }

    /**
     * Returns the mapgeocode
     *
     * @return bool
     */
    public function getMapgeocode()
    {
        return $this->mapgeocode;
    }

    /**
     * Sets the mapgeocode
     *
     * @param bool $mapgeocode
     * @return void
     */
    public function setMapgeocode(bool $mapgeocode)
    {
        $this->mapgeocode = $mapgeocode;
    }

    /**
     * Returns the boolean state of mapgeocode
     *
     * @return bool
     */
    public function isMapgeocode()
    {
        return $this->mapgeocode;
    }
}
