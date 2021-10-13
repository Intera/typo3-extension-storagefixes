<?php

declare(strict_types=1);

namespace Int\StorageFixes\Domain\Repository;

use Int\StorageFixes\Domain\Model\FileCollection;
use phpDocumentor\Reflection\Types\Collection;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class FileCollectionRepository
{
    const TABLE_NAME = 'sys_file_collection';

    /**
     * @param int $storageUid
     * @param string $folderPrefix
     * @return array<int, FileCollection>
     */
    public function findManyByStorageAndFolderPrefix(int $storageUid, string $folderPrefix): array
    {
        $db = $this->getDatabaseConnection();
        $result = $db->exec_SELECTquery(
            '*',
            self::TABLE_NAME,
            'type=' . $db->fullQuoteStr('folder', self::TABLE_NAME)
            . ' AND storage=' . $storageUid
            . ' AND folder LIKE ' . $db->fullQuoteStr(
                $db->escapeStrForLike($folderPrefix, self::TABLE_NAME) . '%',
                self::TABLE_NAME
            )
        );
        $collections = [];
        while ($row = $db->sql_fetch_assoc($result)) {
            $collections[] = FileCollection::createFromRow($row);
        }
        return $collections;
    }

    public function getAllLanguageUids(): array
    {
        $db = $this->getDatabaseConnection();
        $result = $db->exec_SELECTquery(
            'sys_language_uid',
            self::TABLE_NAME,
            'sys_language_uid > 0',
            'sys_language_uid'
        );
        $languageUids = [];
        while ($row = $db->sql_fetch_assoc($result)) {
            $languageUids[] = (int)$row['sys_language_uid'];
        }
        return $languageUids;
    }

    public function getDatabaseConnection(): DatabaseConnection
    {
        return $GLOBALS['TYPO3_DB'];
    }

    public function update(FileCollection $collection)
    {
        $this->getDatabaseConnection()->exec_UPDATEquery(
            self::TABLE_NAME,
            'uid=' . $collection->getUid(),
            $collection->toRow()
        );
    }
}
