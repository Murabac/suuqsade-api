<?php

namespace Tests\Unit;

use App\Support\SupportedProductHost;
use PHPUnit\Framework\TestCase;

class SupportedProductHostTest extends TestCase
{
    public function test_allows_shein_and_amazon_hosts(): void
    {
        $this->assertTrue(SupportedProductHost::isAllowed('https://www.shein.com/product.html'));
        $this->assertTrue(SupportedProductHost::isAllowed('https://onelink.shein.com/42/abc'));
        $this->assertTrue(SupportedProductHost::isAllowed('https://www.amazon.ae/dp/B123'));
        $this->assertTrue(SupportedProductHost::isAllowed('https://amazon.co.uk/dp/B123'));
    }

    public function test_rejects_host_bypass_and_internal_targets(): void
    {
        $this->assertFalse(SupportedProductHost::isAllowed('https://shein.com.evil.com/product'));
        $this->assertFalse(SupportedProductHost::isAllowed('https://notamazon.com/product'));
        $this->assertFalse(SupportedProductHost::isAllowed('http://127.0.0.1/product'));
        $this->assertFalse(SupportedProductHost::isAllowed('http://192.168.1.10/product'));
        $this->assertFalse(SupportedProductHost::isAllowed('ftp://www.shein.com/product'));
    }
}
