<?php

declare(strict_types=1);

namespace Int\StorageFixes\Frontend;

use ArrayObject;
use PDO;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\RootLevelRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Aspect\FileMetadataOverlayAspect;

/**
 * Fixes handling for file metadata translations in free mode.
 *
 * @see FileMetadataOverlayAspect for core funciton that handles overlay in non free mode
 */
final class FileMetadataFreeTranslationAspect
{
    /**
     * Do translation and workspace overlay
     *
     * @param ArrayObject $data
     */
    public function languageAndWorkspaceOverlay(ArrayObject $data)
    {
        // Should only be in Frontend, but not in eID context
        if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_FE) || isset($_REQUEST['eID'])) {
            return;
        }

        $context = GeneralUtility::makeInstance(Context::class);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $context->getAspect('language');

        // Overlaying is active -> no strict mode, nothing to do for us, TYPO3 core is working.
        if ($languageAspect->doOverlays()) {
            return;
        }

        if (!$languageAspect->getContentId()) {
            return;
        }

        $this->replaceOriginalWithTranslationForStrictMode($data, $languageAspect->getContentId());
    }

    private function findTranslationByFileUid(int $parentUid, int $languageUid): ?ArrayObject
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_metadata');
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(RootLevelRestriction::class));
        $record = $queryBuilder
            ->select('*')
            ->from('sys_file_metadata')
            ->where(
                $queryBuilder->expr()->eq(
                    'l10n_parent',
                    $queryBuilder->createNamedParameter($parentUid, PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageUid, PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        return $record ? new ArrayObject($record) : null;
    }

    private function replaceOriginalWithTranslationForStrictMode(ArrayObject $data, int $contentId): void
    {
        $translation = $this->findTranslationByFileUid((int)$data['uid'], $contentId);

        if ($translation) {
            $data->exchangeArray($translation->getArrayCopy());
        }
    }
}
