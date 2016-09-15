<?php

namespace Liip\MonitorBundle\Tests\Check;

use org\bovigo\vfs\vfsStream;
use Liip\MonitorBundle\Check\FileReadable;
use org\bovigo\vfs\vfsStreamDirectory;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

/**
 * Class FileReadableTest.
 */
class FileReadableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    /**
     * if FileReadable instance of CheckInterface.
     */
    public function testshouldImplementCheckInterface()
    {
        static::assertInstanceOf(
            CheckInterface::class,
            $this->createMock(FileReadable::class)
        );
    }

    /**
     * test constructor.
     */
    public function testConstructor()
    {
        new FileReadable($this->providerValidConstructorArguments());
    }

    /**
     * test constructor fail object.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a file name (string) , an array or Traversable of strings, got stdClass
     */
    public function testConstructorFailObject()
    {
        new FileReadable((object) ['test']);
    }

    /**
     * test constructor fail int.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a file path (string) or an array of strings
     */
    public function testConstructorFailInt()
    {
        new FileReadable(1);
    }

    /**
     * @return array
     */
    public function providerValidConstructorArguments()
    {
        return [
            __FILE__,
            vfsStream::newFile('foo.txt')->at($this->root)->url(),
        ];
    }

    /**
     * @return array
     */
    public function providerValidConstructorArgumentsFail()
    {
        return [
            vfsStream::newDirectory('foo1')->at($this->root)->url(),
            vfsStream::newDirectory('foo2')->at($this->root)->url(),
        ];
    }

    /**
     * test the single path check success.
     */
    public function testCheckSuccessSinglePath()
    {
        $object = new FileReadable(vfsStream::newFile('foo.txt')->at($this->root)->url());

        $r = $object->check();
        $this->assertInstanceOf(Success::class, $r);
        $this->assertEquals('The path is a readable file.', $r->getMessage());
    }

    /**
     * test multiple paths check success.
     */
    public function testCheckSuccessMultiplePaths()
    {
        $object = new FileReadable($this->providerValidConstructorArguments());
        $r = $object->check();
        $this->assertInstanceOf(Success::class, $r);

        $this->assertEquals('All paths are readable file.', $r->getMessage());
    }

    /**
     * test the single path check failure.
     */
    public function testCheckFailureSingleInvalidFile()
    {
        $object = new FileReadable(vfsStream::newDirectory('foo')->at($this->root)->url());
        $r = $object->check();
        $this->assertInstanceOf(Failure::class, $r);
        $this->assertContains('vfs://root/foo is not a valid file.', $r->getMessage());
    }

    /**
     * test multiple paths check failure.
     */
    public function testCheckFailureMultipleInvalidDirs()
    {
        $object = new FileReadable($this->providerValidConstructorArgumentsFail());
        $r = $object->check();
        $this->assertInstanceOf(Failure::class, $r);
        $this->assertContains('The following paths are not valid file: vfs://root/foo1, vfs://root/foo2', $r->getMessage());
    }

    /**
     * test permission single.
     */
    public function testCheckFailureSingleUnwritableDir()
    {
        $unwritableFile = vfsStream::newFile('unwritabledir', 000)->at($this->root);
        $object = new FileReadable($unwritableFile->url());
        $r = $object->check();
        $this->assertInstanceOf('ZendDiagnostics\Result\Failure', $r);
        $this->assertEquals('vfs://root/unwritabledir file is not readable.', $r->getMessage());
    }

    /**
     * test permission multiple.
     */
    public function testCheckFailureMultipleUnwritableDirs()
    {
        $unwritableFile1 = vfsStream::newFile('unwritablefile1', 000)->at($this->root);
        $unwritableFile2 = vfsStream::newFile('unwritablefile2', 000)->at($this->root);

        $object = new FileReadable(array($unwritableFile1->url(), $unwritableFile2->url()));
        $r = $object->check();
        $this->assertInstanceOf('ZendDiagnostics\Result\Failure', $r);
        $this->assertEquals('The following files are not readable: vfs://root/unwritablefile1, vfs://root/unwritablefile2', $r->getMessage());
    }
}
