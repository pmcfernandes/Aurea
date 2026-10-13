<?php
declare(strict_types=1);

namespace Tests\Unit\Util;

use Aurea\Util\TDateTimeUtil;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Util\TDateTimeUtil */
final class TDateTimeUtilTest extends TestCase
{
    public function testConvert(): void
    {
        $this->assertSame(
            '2026-12-25',
            TDateTimeUtil::convertToMask('25/12/2026', 'dd/mm/yyyy', 'yyyy-mm-dd')
        );
    }

    public function testConvertEmptyAndInvalidReturnNull(): void
    {
        $this->assertNull(TDateTimeUtil::convertToMask('', 'dd/mm/yyyy', 'yyyy-mm-dd'));
        $this->assertNull(TDateTimeUtil::convertToMask('xxx', 'dd/mm/yyyy', 'yyyy-mm-dd'));
    }

    public function testDateToPT(): void
    {
        $this->assertSame('25/12/2026', TDateTimeUtil::dateToPT('2026-12-25'));
    }

    public function testDateToUs(): void
    {
        $this->assertSame('2026-12-25', TDateTimeUtil::dateToUs('25/12/2026'));
    }

    public function testToday(): void
    {
        $this->assertSame(date('Y-m-d'), TDateTimeUtil::today('yyyy-mm-dd'));
        $this->assertSame(date('d/m/Y'), TDateTimeUtil::today('dd/mm/yyyy'));
    }

    public function testNow(): void
    {
        $now = TDateTimeUtil::now('yyyy-mm-dd hh:mi:ss');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $now);
    }

    public function testAddDays(): void
    {
        $this->assertSame('2026-12-30', TDateTimeUtil::addDays('2026-12-25', 5));
        $this->assertSame('2026-12-20', TDateTimeUtil::addDays('2026-12-25', -5));
        $this->assertNull(TDateTimeUtil::addDays('xxx', 5));
    }

    public function testDiffDays(): void
    {
        $this->assertSame(5, TDateTimeUtil::diffDays('2026-12-25', '2026-12-30'));
        $this->assertSame(-5, TDateTimeUtil::diffDays('2026-12-30', '2026-12-25'));
        $this->assertNull(TDateTimeUtil::diffDays('xxx', '2026-12-30'));
    }

    public function testIsValid(): void
    {
        $this->assertTrue(TDateTimeUtil::isValid('25/12/2026', 'dd/mm/yyyy'));
        $this->assertFalse(TDateTimeUtil::isValid('31/02/2026', 'dd/mm/yyyy'));
        $this->assertTrue(TDateTimeUtil::isValid('13:45', 'hh:mi'));
        $this->assertFalse(TDateTimeUtil::isValid('25:00', 'hh:mi'));
    }
}
