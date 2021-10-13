<?php

declare(strict_types=1);

namespace Int\StorageFixes\Domain\Model;

final class FileCollection
{
    /**
     * @var string
     */
    protected $folder;

    /**
     * @var int
     */
    protected $storageUid;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    private $uid;

    public static function createFromRow(array $row): FileCollection
    {
        $collection = new self();
        $collection->uid = (int)$row['uid'];
        $collection->type = $row['type'];
        $collection->storageUid = (int)$row['storage'];
        $collection->folder = $row['folder'];
        return $collection;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setFolder(string $folder)
    {
        $this->folder = $folder;
    }

    public function setStorageUid(int $storageUid)
    {
        $this->storageUid = $storageUid;
    }

    public function toRow(): array
    {
        return [
            'type' => $this->type,
            'storage' => $this->storageUid,
            'folder' => $this->folder,
        ];
    }
}
