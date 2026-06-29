<?php
// tests/Unit/Constants/IKMConstantsTest.php

namespace Tests\Unit\Constants;

use Tests\TestCase;
use App\Constants\IKMConstants;
use PHPUnit\Framework\Attributes\Test;

class IKMConstantsTest extends TestCase
{
    #[Test]
    public function it_has_correct_category_for_sangat_baik()
    {
        $category = IKMConstants::getCategory(90);
        
        $this->assertEquals('A', $category['code']);
        $this->assertEquals('Sangat Baik', $category['label']);
        $this->assertEquals('#10B981', $category['color']);
    }

    #[Test]
    public function it_has_correct_category_for_baik()
    {
        $category = IKMConstants::getCategory(80);
        
        $this->assertEquals('B', $category['code']);
        $this->assertEquals('Baik', $category['label']);
        $this->assertEquals('#3B82F6', $category['color']);
    }

    #[Test]
    public function it_has_correct_category_for_kurang_baik()
    {
        $category = IKMConstants::getCategory(70);
        
        $this->assertEquals('C', $category['code']);
        $this->assertEquals('Kurang Baik', $category['label']);
        $this->assertEquals('#F59E0B', $category['color']);
    }

    #[Test]
    public function it_has_correct_category_for_tidak_baik()
    {
        $category = IKMConstants::getCategory(50);
        
        $this->assertEquals('D', $category['code']);
        $this->assertEquals('Tidak Baik', $category['label']);
        $this->assertEquals('#EF4444', $category['color']);
    }

    #[Test]
    public function it_has_correct_constants()
    {
        $this->assertEquals(9, IKMConstants::UNSUUR_COUNT);
        $this->assertEquals(1, IKMConstants::SKALA_MIN);
        $this->assertEquals(4, IKMConstants::SKALA_MAX);
        $this->assertEquals(100, IKMConstants::IKM_MAX);
        $this->assertEquals(25, IKMConstants::IKM_MIN);
        $this->assertEquals(88.31, IKMConstants::TARGET_IKM);
        $this->assertEquals(76.61, IKMConstants::BATAS_BAIK);
        $this->assertEquals(65.00, IKMConstants::BATAS_KURANG_BAIK);
        $this->assertEquals(20, IKMConstants::MAX_RECENT_RESPONDENTS);
        $this->assertEquals(3600, IKMConstants::CACHE_DURATION);
    }
}