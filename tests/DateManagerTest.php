<?php


namespace App\Services;

use PHPUnit\Framework\TestCase;

class DateManagerTest extends TestCase
{
    public function testisOpenDayMonday()
    {
        $date = new DateManager();

        $this->assertSame(true, $date->isOpened(new \DateTime('2020-3-16')));
    }

    public function testisOpenTuesday()
    {
        $date = new DateManager();

        $this->assertSame(false, $date->isOpened(new \DateTime('2020-3-17')));
    }

    public function testisOpenHoliday()
    {
        $date = new DateManager();

        $this->assertSame(false, $date->isOpened(new \DateTime('2020-7-14')));
    }
}