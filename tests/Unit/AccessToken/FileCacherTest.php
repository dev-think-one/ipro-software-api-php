<?php

namespace Angecode\IproSoftware\Tests\Unit\AccessToken;

use Angecode\IproSoftware\AccessToken\FileCacher;
use Angecode\IproSoftware\Contracts\AccessToken;
use Angecode\IproSoftware\Contracts\AccessTokenCacher;
use Angecode\IproSoftware\Tests\TestCase;
use Carbon\Carbon;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class FileCacherTest extends TestCase
{

    /** @var AccessTokenCacher */
    protected $cacher;

    /** @var string */
    protected $file = 'cache.txt';

    /** @var vfsStreamDirectory */
    protected $root;

    /** @var AccessToken */
    protected $accessToken;

    protected function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup('exampleDir');

        $this->cacher = new FileCacher(vfsStream::url('exampleDir/' . $this->file));


        $this->accessToken = new \Angecode\IproSoftware\AccessToken\AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addDay()->toString());
    }


    public function testPut()
    {
        $result = $this->cacher->put($this->accessToken);

        $this->assertTrue($result);
        $this->assertTrue($this->root->hasChild($this->file));
    }

    public function testGet()
    {
        $result = $this->cacher->put($this->accessToken);

        $this->assertTrue($result);
        $this->assertTrue($this->root->hasChild($this->file));

        $result = $this->cacher->get();

       $this->assertEqualsCanonicalizing($this->accessToken, $result);
    }

}
