<?php

namespace IproSoftwareApi\Tests\Unit\AccessToken;

use IproSoftwareApi\AccessToken\NoneCacher;
use IproSoftwareApi\Contracts\AccessToken;
use IproSoftwareApi\Contracts\AccessTokenCacher;
use IproSoftwareApi\Tests\TestCase;

class NoneCacherTest extends TestCase
{
    protected AccessTokenCacher $cacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacher = new NoneCacher();
    }

    /** @test */
    public function put_to_cache()
    {
        $result = $this->cacher->put(\Mockery::mock(AccessToken::class));

        $this->assertTrue($result);
    }

    /** @test */
    public function get_from_cache()
    {
        $result = $this->cacher->put(\Mockery::mock(AccessToken::class));
        $this->assertTrue($result);
        $this->assertNull($this->cacher->get());
    }
}
