<?php

declare(strict_types=1);

namespace Int\StorageFixes\Tests\Functional;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFunctionalTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/storagefixes'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupFileadminRoot();
    }

    protected function cleanupFileadminRoot(): void
    {
        if (is_dir($this->getFileadminRoot())) {
            GeneralUtility::rmdir($this->getFileadminRoot(), true);
        }
    }

    protected function getFileadminRoot(): string
    {
        return $this->getInstancePath() . '/fileadmin/testing/';
    }

    protected function mkFileadminDir(string $directory): void
    {
        $fileadminDirectory = $this->getFileadminRoot() . $directory;
        if (!is_dir($fileadminDirectory)) {
            mkdir($fileadminDirectory, 0775, true);
        }
    }
}
