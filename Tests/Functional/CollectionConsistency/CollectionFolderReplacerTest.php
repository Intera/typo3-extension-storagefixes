<?php

declare(strict_types=1);

namespace Int\StorageFixes\Tests\Functional\CollectionConsistency;

use Int\StorageFixes\Tests\Functional\AbstractFunctionalTest;
use TYPO3\CMS\Core\Resource\ResourceFactory;

final class CollectionFolderReplacerTest extends AbstractFunctionalTest
{
    private $expectedCollections = [
        31 => [
            'storage' => 1,
            'identifier' => '/folderB/folderA/',
        ],
        33 => [
            'storage' => 1,
            'identifier' => '/folderB/',
        ],
        41 => [
            'storage' => 1,
            'identifier' => '/folderA/',
        ],
        43 => [
            'storage' => 1,
            'identifier' => '/folderA/insideFolderA/',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mkFileadminDir('/storage1/folderA/insideFolderA/');
        $this->mkFileadminDir('/storage1/folderB/folderA');

        $this->mkFileadminDir('/storage2/folderA/');
        $this->mkFileadminDir('/storage2/folderA2/');
        $this->mkFileadminDir('/storage2/folderB2/folderA2/');

        $this->importDataSet(__DIR__ . '/../Fixtures/file_storages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/file_collections.xml');

        $this->setUpBackendUserFromFixture(1);

        $this->assertExpectedCollectionStructure();
    }

    public function testFolderMoveReplacesIdentifier(): void
    {
        $folder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier('1:/folderB/');
        $targetFolder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier('1:/folderA/');
        $storage = ResourceFactory::getInstance()->getStorageObject(1);
        $storage->moveFolder($folder, $targetFolder);

        $this->expectedCollections[31]['identifier'] = '/folderA/folderB/folderA/';
        $this->expectedCollections[33]['identifier'] = '/folderA/folderB/';
        $this->assertExpectedCollectionStructure();
    }

    public function testFolderMoveReplacesStorageUid(): void
    {
        $folder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier('1:/folderB/');
        $targetFolder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier('2:/');
        $folder->moveTo($targetFolder);

        $this->expectedCollections[31]['storage'] = 2;
        $this->expectedCollections[33]['storage'] = 2;
        $this->assertExpectedCollectionStructure();
    }

    public function testFolderRenameInOtherStorageIsIgnored(): void
    {
        $folder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier('2:/folderA/');
        $folder->rename('renamed');

        $this->assertExpectedCollectionStructure();
    }

    public function testFolderRenameReplacesPrefix(): void
    {
        $folder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier('1:/folderA/');
        $folder->rename('renamed');

        $this->expectedCollections[41]['identifier'] = '/renamed/';
        $this->expectedCollections[43]['identifier'] = '/renamed/insideFolderA/';
        $this->assertExpectedCollectionStructure();
    }

    private function assertCollectionFolderIs(int $collectionUid, int $expectedStorage, string $expectedFolder): void
    {
        $collectionOriginal = $this->getDatabaseConnection()->selectSingleRow(
            '*',
            'sys_file_collection',
            'uid=' . $collectionUid
        );
        $this->assertEquals($expectedFolder, $collectionOriginal['folder']);
        $this->assertEquals($expectedStorage, (int)$collectionOriginal['storage']);
    }

    private function assertExpectedCollectionStructure(): void
    {
        foreach ($this->expectedCollections as $collectionUid => $collectionData) {
            $this->assertCollectionFolderIs($collectionUid, $collectionData['storage'], $collectionData['identifier']);
        }
    }
}
