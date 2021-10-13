<?php

declare(strict_types=1);

namespace Int\StorageFixes\Tests\Functional\Resource;

use Int\StorageFixes\Tests\Functional\AbstractFunctionalTest;
use TYPO3\CMS\Core\Resource\ResourceFactory;

final class ResourceStorageTest extends AbstractFunctionalTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->touchFileadminFile('/storage1/folderA/textfile1.txt');
        $this->touchFileadminFile('/storage1/folderA/subfolderA/textfile2.txt');
        $this->touchFileadminFile('/storage2/testfile21.txt');

        $this->importDataSet(__DIR__ . '/../Fixtures/file_storages.xml');

        $this->setUpBackendUserFromFixture(1);
    }

    public function testCopyFolder()
    {
        $folderA = ResourceFactory::getInstance()->getStorageObject(1)->getFolder('folderA');
        $targetFolder = ResourceFactory::getInstance()->getStorageObject(1)->getFolder('');
        $folderA->copyTo($targetFolder, 'folderCopy');
        $this->assertFileadminFileExists('/storage1/folderCopy/textfile1.txt');
        $this->assertFileadminFileExists('/storage1/folderCopy/subfolderA/textfile2.txt');
    }

    public function testCopyFolderBetweenStorages()
    {
        $folderA = ResourceFactory::getInstance()->getStorageObject(1)->getFolder('folderA');
        $targetFolder = ResourceFactory::getInstance()->getStorageObject(2)->getFolder('');
        $folderA->copyTo($targetFolder);
        $this->assertFileadminFileExists('/storage2/folderA/textfile1.txt');
        $this->assertFileadminFileExists('/storage2/folderA/subfolderA/textfile2.txt');
    }

    public function testMoveFolder()
    {
        $folderA = ResourceFactory::getInstance()->getStorageObject(1)->getFolder('folderA');
        $targetFolder = ResourceFactory::getInstance()->getStorageObject(1)->getFolder('');
        $folderA->moveTo($targetFolder, 'folderMove');

        $this->assertFileadminDirectoryWasRemoved('/storage1/folderA');

        $this->assertFileadminFileExists('/storage1/folderMove/textfile1.txt');
        $this->assertFileadminFileExists('/storage1/folderMove/subfolderA/textfile2.txt');
    }

    public function testMoveFolderBetweenStorages()
    {
        $folderA = ResourceFactory::getInstance()->getStorageObject(1)->getFolder('folderA');
        $targetFolder = ResourceFactory::getInstance()->getStorageObject(2)->getFolder('');
        $folderA->moveTo($targetFolder);

        $this->assertFileadminDirectoryWasRemoved('/storage1/folderA');

        $this->assertFileadminFileExists('/storage2/folderA/textfile1.txt');
        $this->assertFileadminFileExists('/storage2/folderA/subfolderA/textfile2.txt');
    }

    private function assertFileadminDirectoryWasRemoved(string $path)
    {
        $this->assertDirectoryNotExists($this->getFileadminRoot() . $path);
    }

    private function assertFileadminFileExists(string $pathAndFilename)
    {
        $this->assertFileExists($this->getFileadminRoot() . $pathAndFilename);
    }

    private function touchFileadminFile(string $pathAndFilename)
    {
        $this->mkFileadminDir(dirname($pathAndFilename));
        touch($this->getFileadminRoot() . $pathAndFilename);
    }
}
