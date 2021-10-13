<?php

declare(strict_types=1);

namespace Int\StorageFixes\CollectionConsistency;

use TYPO3\CMS\Core\Resource\Folder;

final class CollectionConsistencyListener
{
    /**
     * @var CollectionFolderReplacer
     */
    private $collectionFolderReplacer;

    public function injectCollectionFolderReplacer(CollectionFolderReplacer $collectionFolderReplacer)
    {
        $this->collectionFolderReplacer = $collectionFolderReplacer;
    }

    public function onFolderMove(Folder $folder, Folder $targetFolder, string $newName)
    {
        $oldIdentifier = $folder->getIdentifier();
        $newIdentifier = $targetFolder->getIdentifier() . $newName . '/';

        $this->processCollections(
            $folder->getStorage()->getUid(),
            $oldIdentifier,
            $targetFolder->getStorage()->getUid(),
            $newIdentifier
        );
    }

    public function onFolderRename(Folder $folder, $newName)
    {
        $this->processCollections(
            $folder->getStorage()->getUid(),
            $folder->getIdentifier(),
            $folder->getStorage()->getUid(),
            $folder->getParentFolder()->getIdentifier() . $newName . '/'
        );
    }

    private function processCollections(int $oldStorageUid, string $oldPrefix, int $newStorageUid, string $newPrefix)
    {
        $this->collectionFolderReplacer->replaceFolderInCollections(
            $oldStorageUid,
            $oldPrefix,
            $newStorageUid,
            $newPrefix
        );
    }
}
