<?php

/**
 * @noinspection PhpMissingStrictTypesDeclarationInspection
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

defined('TYPO3_MODE') or die();

$bootStorageFixes = function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\ResourceStorage::class]['className'] =
        \Int\StorageFixes\Resource\ResourceStorage::class;

    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
    );
    /** @see \TYPO3\CMS\Core\Resource\ResourceStorage::emitPostFolderMoveSignal() */
    $signalSlotDispatcher->connect(
        \TYPO3\CMS\Core\Resource\ResourceStorage::class,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderMove,
        /** @uses \Int\StorageFixes\CollectionConsistency\CollectionConsistencyListener::onFolderMove() */
        \Int\StorageFixes\CollectionConsistency\CollectionConsistencyListener::class,
        'onFolderMove'
    );
    /** @see \TYPO3\CMS\Core\Resource\ResourceStorage::emitPostFolderRenameSignal() */
    $signalSlotDispatcher->connect(
        \TYPO3\CMS\Core\Resource\ResourceStorage::class,
        \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PostFolderRename,
        /** @uses \Int\StorageFixes\CollectionConsistency\CollectionConsistencyListener::onFolderRename() */
        \Int\StorageFixes\CollectionConsistency\CollectionConsistencyListener::class,
        'onFolderRename'
    );
};

$bootStorageFixes();
unset($bootStorageFixes);
