<?php

declare(strict_types=1);

namespace Int\StorageFixes\CollectionConsistency;

use InvalidArgumentException;
use TYPO3\CMS\Core\SingletonInterface;

final class PrefixReplacer implements SingletonInterface
{
    public function replacePrefix(string $prefixedString, string $oldPrefix, string $newPrefix): string
    {
        if (substr($prefixedString, 0, strlen($oldPrefix)) !== $oldPrefix) {
            throw new InvalidArgumentException('The string ' . $prefixedString . ' is not prefixed with ' . $oldPrefix);
        }

        $collectionFolderSuffix = substr($prefixedString, strlen($oldPrefix));
        return $newPrefix . $collectionFolderSuffix;
    }
}
