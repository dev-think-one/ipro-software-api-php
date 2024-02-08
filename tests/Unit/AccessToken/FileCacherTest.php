<?php

namespace IproSoftwareApi\Tests\Unit\AccessToken;

use Carbon\Carbon;
use IproSoftwareApi\AccessToken\FileCacher;
use IproSoftwareApi\Contracts\AccessTokenCacher;
use IproSoftwareApi\Tests\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class FileCacherTest extends TestCase
{
    protected AccessTokenCacher $cacher;

    protected string $file = 'cache.txt';

    /** @var vfsStreamDirectory */
    protected vfsStreamDirectory $root;

    protected \IproSoftwareApi\AccessToken\AccessToken $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup('exampleDir');

        $this->cacher = new FileCacher(vfsStream::url('exampleDir/' . $this->file));

        $this->accessToken = new \IproSoftwareApi\AccessToken\AccessToken(uniqid('', true), 'some_type', '100', Carbon::now()->addDay()->toString());
    }

    /** @test */
    public function put_to_cache()
    {
        $result = $this->cacher->put($this->accessToken);

        $this->assertTrue($result);
        $this->assertTrue($this->root->hasChild($this->file));
    }

    /** @test */
    public function get_from_cache()
    {
        $result = $this->cacher->put($this->accessToken);

        $this->assertTrue($result);
        $this->assertTrue($this->root->hasChild($this->file));

        $result = $this->cacher->get();

        $this->assertEqualsCanonicalizing($this->accessToken, $result);
    }
}
