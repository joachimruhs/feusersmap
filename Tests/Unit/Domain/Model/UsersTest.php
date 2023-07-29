<?php

declare(strict_types=1);

namespace WSR\Feusersmap\Tests\Unit\Domain\Model;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class UsersTest extends UnitTestCase
{
    /**
     * @var \WSR\Feusersmap\Domain\Model\Users|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \WSR\Feusersmap\Domain\Model\Users::class,
            ['dummy']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getLatitudeReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getLatitude()
        );
    }

    /**
     * @test
     */
    public function setLatitudeForIntSetsLatitude(): void
    {
        $this->subject->setLatitude(12);

        self::assertEquals(12, $this->subject->_get('latitude'));
    }

    /**
     * @test
     */
    public function getLongitudeReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getLongitude()
        );
    }

    /**
     * @test
     */
    public function setLongitudeForIntSetsLongitude(): void
    {
        $this->subject->setLongitude(12);

        self::assertEquals(12, $this->subject->_get('longitude'));
    }

    /**
     * @test
     */
    public function getMapiconReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getMapicon()
        );
    }

    /**
     * @test
     */
    public function setMapiconForStringSetsMapicon(): void
    {
        $this->subject->setMapicon('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('mapicon'));
    }

    /**
     * @test
     */
    public function getMapgeocodeReturnsInitialValueForBool(): void
    {
        self::assertFalse($this->subject->getMapgeocode());
    }

    /**
     * @test
     */
    public function setMapgeocodeForBoolSetsMapgeocode(): void
    {
        $this->subject->setMapgeocode(true);

        self::assertEquals(true, $this->subject->_get('mapgeocode'));
    }
}
