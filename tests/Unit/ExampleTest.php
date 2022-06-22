<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $p =Product::find(1);

        $this->assertTrue($p->createdAt()=="31-Aug-2020");
    }
}
