<?php

declare(strict_types=1);

namespace Int\StorageFixes\Resource;

use InvalidArgumentException;
use RuntimeException;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\InvalidTargetFolderException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\FolderInterface;

final class ResourceStorage extends \TYPO3\CMS\Core\Resource\ResourceStorage
{
    /**
     * Copies a folder.
     *
     * @param FolderInterface $folderToCopy The folder to copy
     * @param FolderInterface $targetParentFolder The target folder
     * @param string $newFolderName
     * @param string $conflictMode a value of the \TYPO3\CMS\Core\Resource\DuplicationBehavior enumeration
     * @return Folder The new (copied) folder object
     * @throws InvalidTargetFolderException
     */
    public function copyFolder(
        FolderInterface $folderToCopy,
        FolderInterface $targetParentFolder,
        $newFolderName = null,
        $conflictMode = DuplicationBehavior::RENAME
    ): Folder {
        // @todo implement the $conflictMode handling
        $this->assureFolderCopyPermissions($folderToCopy, $targetParentFolder);
        /** @noinspection PhpParamsInspection */
        $sanitizedNewFolderName = $targetParentFolder->getStorage()->sanitizeFileName(
            $newFolderName ?: $folderToCopy->getName(),
            $targetParentFolder
        );
        if ($folderToCopy instanceof Folder && $targetParentFolder instanceof Folder) {
            $this->emitPreFolderCopySignal($folderToCopy, $targetParentFolder, $sanitizedNewFolderName);
        }
        $sourceStorage = $folderToCopy->getStorage();
        // Call driver method to move the file that also updates the file object properties
        if ($sourceStorage === $this) {
            /** @noinspection PhpParamsInspection */
            if ($this->isWithinFolder($folderToCopy, $targetParentFolder)) {
                throw new InvalidTargetFolderException(
                    sprintf(
                        'Cannot copy folder "%s" into target folder "%s",'
                        . ' because the target folder is already within the folder to be copied!',
                        $folderToCopy->getName(),
                        $targetParentFolder->getName()
                    ),
                    1422723059
                );
            }
            $this->driver->copyFolderWithinStorage(
                $folderToCopy->getIdentifier(),
                $targetParentFolder->getIdentifier(),
                $sanitizedNewFolderName
            );
        } else {
            /** @noinspection PhpParamsInspection */
            $this->copyFolderBetweenStorages($folderToCopy, $targetParentFolder, $sanitizedNewFolderName);
        }
        $returnObject = $this->getFolder($targetParentFolder->getSubfolder($sanitizedNewFolderName)->getIdentifier());
        /** @noinspection PhpParamsInspection */
        $this->emitPostFolderCopySignal($folderToCopy, $targetParentFolder, $returnObject->getName());
        return $returnObject;
    }

    /**
     * Moves a folder. If you want to move a folder from this storage to another
     * one, call this method on the target storage, otherwise you will get an exception.
     *
     * @param Folder $folderToMove The folder to move.
     * @param Folder $targetParentFolder The target parent folder
     * @param string $newFolderName
     * @param string $conflictMode a value of the \TYPO3\CMS\Core\Resource\DuplicationBehavior enumeration
     *
     * @return Folder
     * @throws InvalidArgumentException
     * @throws InvalidTargetFolderException
     * @throws \Exception|Exception
     */
    public function moveFolder(
        Folder $folderToMove,
        Folder $targetParentFolder,
        $newFolderName = null,
        $conflictMode = DuplicationBehavior::RENAME
    ): Folder {
        // @todo add tests
        $originalFolder = $folderToMove->getParentFolder();
        $this->assureFolderMovePermissions($folderToMove, $targetParentFolder);
        $sourceStorage = $folderToMove->getStorage();
        $sanitizedNewFolderName = $targetParentFolder->getStorage()->sanitizeFileName(
            $newFolderName ?: $folderToMove->getName(),
            $targetParentFolder
        );
        // @todo check if folder already exists in $targetParentFolder, handle this conflict then
        $this->emitPreFolderMoveSignal($folderToMove, $targetParentFolder, $sanitizedNewFolderName);
        // Get all file objects now so we are able to update them after moving the folder
        $fileObjects = $this->getAllFileObjectsInFolder($folderToMove);
        if ($sourceStorage === $this) {
            if ($this->isWithinFolder($folderToMove, $targetParentFolder)) {
                throw new InvalidTargetFolderException(
                    sprintf(
                        'Cannot move folder "%s" into target folder "%s",'
                        . ' because the target folder is already within the folder to be moved!',
                        $folderToMove->getName(),
                        $targetParentFolder->getName()
                    ),
                    1422723050
                );
            }
            $fileMappings = $this->driver->moveFolderWithinStorage(
                $folderToMove->getIdentifier(),
                $targetParentFolder->getIdentifier(),
                $sanitizedNewFolderName
            );
            // Update the identifier and storage of all file objects
            foreach ($fileObjects as $oldIdentifier => $fileObject) {
                $newIdentifier = $fileMappings[$oldIdentifier];
                /** @noinspection PhpInternalEntityUsedInspection */
                $fileObject->updateProperties(['storage' => $this->getUid(), 'identifier' => $newIdentifier]);
                $this->getIndexer()->updateIndexEntry($fileObject);
            }
            $returnObject = $this->getFolder($fileMappings[$folderToMove->getIdentifier()]);
        } else {
            $this->moveFolderBetweenStorages(
                $folderToMove,
                $targetParentFolder,
                $sanitizedNewFolderName
            );
            $returnObject = $this->getFolder(
                $targetParentFolder->getSubfolder($sanitizedNewFolderName)->getIdentifier()
            );
        }

        $this->emitPostFolderMoveSignal($folderToMove, $targetParentFolder, $returnObject->getName(), $originalFolder);
        return $returnObject;
    }

    /**
     * Copies a folder between storages.
     *
     * @param Folder $folderToCopy
     * @param Folder $targetParentFolder
     * @param string $newFolderName
     *
     * @return bool
     * @throws RuntimeException
     */
    protected function copyFolderBetweenStorages(Folder $folderToCopy, Folder $targetParentFolder, $newFolderName): bool
    {
        if ($targetParentFolder->hasFolder($newFolderName)) {
            throw new InvalidTargetFolderException(
                sprintf(
                    'Cannot copy folder "%s" in storage "%s" into target folder "%s" in storage "%s",'
                    . ' because the target folder is already within the folder to be copied!',
                    $folderToCopy->getName(),
                    $folderToCopy->getStorage()->getName(),
                    $newFolderName,
                    $targetParentFolder->getStorage()->getName()
                ),
                1626370193
            );
        }

        $allFiles = $folderToCopy->getFiles(0, 0, Folder::FILTER_MODE_NO_FILTERS);
        $allFolders = $folderToCopy->getStorage()->getFoldersInFolder($folderToCopy, 0, 0, false);

        $newFolder = $targetParentFolder->createFolder($newFolderName);
        foreach ($allFiles as $file) {
            $this->copyFile($file, $newFolder);
        }
        foreach ($allFolders as $folder) {
            $sanitizedSubFolderName = $this->driver->sanitizeFileName($folder->getName());
            $this->copyFolderBetweenStorages($folder, $newFolder, $sanitizedSubFolderName);
        }
        return true;
    }

    /**
     * Moves the given folder from a different storage to the target folder in this storage.
     *
     * @param Folder $folderToMove
     * @param Folder $targetParentFolder
     * @param string $newFolderName
     *
     * @return bool
     * @throws RuntimeException
     */
    protected function moveFolderBetweenStorages(Folder $folderToMove, Folder $targetParentFolder, $newFolderName): bool
    {
        if ($targetParentFolder->hasFolder($newFolderName)) {
            throw new InvalidTargetFolderException(
                sprintf(
                    'Cannot copy folder "%s" in storage "%s" into target folder "%s" in storage "%s",'
                    . ' because the target folder is already within the folder to be copied!',
                    $folderToMove->getName(),
                    $folderToMove->getStorage()->getName(),
                    $newFolderName,
                    $targetParentFolder->getStorage()->getName()
                ),
                1626370287
            );
        }

        $allFiles = $folderToMove->getFiles(0, 0, Folder::FILTER_MODE_NO_FILTERS);
        $allFolders = $folderToMove->getStorage()->getFoldersInFolder($folderToMove, 0, 0, false);

        $newFolder = $targetParentFolder->createFolder($newFolderName);
        foreach ($allFiles as $file) {
            $this->moveFile($file, $newFolder);
        }
        foreach ($allFolders as $folder) {
            $sanitizedSubFolderName = $this->driver->sanitizeFileName($folder->getName());
            $this->moveFolderBetweenStorages($folder, $newFolder, $sanitizedSubFolderName);
        }
        $folderToMove->delete();
        return true;
    }
}
