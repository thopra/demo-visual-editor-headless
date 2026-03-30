<?php

/*
 * This file is part of the "headless" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FriendsOfTYPO3\Headless\DataProcessing;

use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use FriendsOfTYPO3\Headless\Service\VisualEditingService;

/**
 * Provides record information for friendsoftypo3/visual-editor integration.
 * Can be used to add infos about the current content element for frontend editing
 */
class VisualEditingRecordInformationProcessor implements DataProcessorInterface
{
    /**
     * Adds backend information about the current content element or record for visual editing
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        if (empty($processorConfiguration['as'])) {
            throw new Exception('Please specify property \'as\'');
        }

        $targetFieldName = (string)$cObj->stdWrapValue(
            'as',
            $processorConfiguration
        );

        if (!ExtensionManagementUtility::isLoaded('visual_editor')) {
            $processedData[$targetFieldName] = null;
            return $processedData;
        }

        $service = GeneralUtility::makeInstance(VisualEditingService::class);
        $processedData[$targetFieldName] = $service->getRecordInformation($cObj->getCurrentTable(), $processedData['data'], $cObj->getRequest());

        return $processedData;
    }
}
