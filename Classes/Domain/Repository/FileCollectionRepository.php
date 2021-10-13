<?php

declare(strict_types=1);

namespace Int\StorageFixes\Domain\Repository;

use Doctrine\DBAL\FetchMode;
use Int\StorageFixes\Domain\Model\FileCollection;
use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $query = $this->getQueryBuilder();
        $query->getRestrictions()->removeAll();
        $result = $query->select('*')
            ->from(self::TABLE_NAME)
            ->andWhere($query->expr()->eq('type', $query->expr()->literal('folder')))
            ->andWhere($query->expr()->eq('storage', $query->createNamedParameter($storageUid, PDO::PARAM_INT)))
            ->andWhere(
                $query->expr()->like(
                    'folder',
                    $query->createNamedParameter($query->escapeLikeWildcards($folderPrefix) . '%')
                )
            )
            ->execute();

        $collections = [];
        while ($row = $result->fetch(FetchMode::ASSOCIATIVE)) {
            $collections[] = FileCollection::createFromRow($row);
        }
        return $collections;
    }

    public function update(FileCollection $collection): void
    {
        $updateQuery = $this->getQueryBuilder()->update(self::TABLE_NAME);

        foreach ($collection->toRow() as $column => $value) {
            $updateQuery->set($column, $value);
        }

        $updateQuery->getRestrictions()->removeAll();
        $updateQuery->andWhere(
            $updateQuery->expr()->eq(
                'uid',
                $updateQuery->createNamedParameter($collection->getUid(), PDO::PARAM_INT)
            )
        );

        $updateQuery->execute();
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable(self::TABLE_NAME);
    }
}
