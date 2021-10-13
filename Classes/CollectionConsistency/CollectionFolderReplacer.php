<?php

declare(strict_types=1);

namespace Int\StorageFixes\CollectionConsistency;

use Int\StorageFixes\Domain\Model\FileCollection;
use Int\StorageFixes\Domain\Repository\FileCollectionRepository;

final class CollectionFolderReplacer
{
    /**
     * @var FileCollectionRepository
     */
    private $collectionRepository;

    /**
     * @var PrefixReplacer
     */
    private $prefixReplacer;

    public function injectCollectionRepository(FileCollectionRepository $collectionRepository)
    {
        $this->collectionRepository = $collectionRepository;
    }

    public function injectPrefixReplacer(PrefixReplacer $prefixReplacer)
    {
        $this->prefixReplacer = $prefixReplacer;
    }

    public function replaceFolderInCollections(
        int $oldStorageUid,
        string $oldPrefix,
        int $newStorageUid,
        string $newPrefix
    ) {
        $collections = $this->collectionRepository->findManyByStorageAndFolderPrefix(
            $oldStorageUid,
            $oldPrefix
        );

        foreach ($collections as $collection) {
            $collection->setStorageUid($newStorageUid);
            $this->replaceFolderPrefix($collection, $oldPrefix, $newPrefix);
            $this->collectionRepository->update($collection);
        }
    }

    private function replaceFolderPrefix(FileCollection $collection, string $oldPrefix, string $newPrefix)
    {
        $newColledtionFolder = $this->prefixReplacer->replacePrefix(
            $collection->getFolder(),
            $oldPrefix,
            $newPrefix
        );

        $collection->setFolder($newColledtionFolder);
    }
}
