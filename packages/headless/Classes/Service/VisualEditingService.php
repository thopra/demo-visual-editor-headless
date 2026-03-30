<?php

/*
 * This file is part of the "headless" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FriendsOfTYPO3\Headless\Service;

use FriendsOfTYPO3\Headless\Utility\HeadlessFrontendUrlInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\VisualEditor\Service\ContentElementWrapperService;
use TYPO3\CMS\VisualEditor\Service\EditModeService;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\VisualEditor\Form\FieldFactory;

class VisualEditingService
{
    private readonly EditModeService $editModeService;

    public function __construct(
        private readonly SystemResourceFactory $resourceFactory,
        private readonly SystemResourcePublisherInterface $resourcePublisher,
        private readonly ContentElementWrapperService $contentElementWrapperService,
        private readonly RecordFactory $recordFactory,
        private readonly TcaSchemaFactory $tcaSchema,
        private readonly FieldFactory $fieldFactory,
        private readonly HeadlessFrontendUrlInterface $urlUtility,
        private PageRenderer $pageRenderer,
    ) {
        // instanciate manually, must not be a required dependency
        $this->editModeService = GeneralUtility::makeInstance(EditModeService::class);
    }

    public function getPageInformation(ServerRequestInterface $request): array
    {
        $pageInfo = $this->editModeService->getPageInfo($request);
        //$pageInfo['allowedOrigins'] = [...$pageInfo['allowedOrigins'], $this->urlUtility->getFrontendUrl()];

        /*$baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
        $pageInfo['editContentUrl'] = $baseUrl . $pageInfo['editContentUrl'];
        $pageInfo['editContentContextualUrl'] = $baseUrl . $pageInfo['editContentContextualUrl'];*/

        return $pageInfo;
    }

    public function getRecordInformation(string $table, array $data, ServerRequestInterface $request): ?array
    {
        $record = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $data);
        if (!$record) {
            return null;
        }
        // if ($record instanceof PageInformation) {
        //     $record = $this->recordFactory->createResolvedRecordFromDatabaseRow('pages', $record->getPageRecord());
        // }
        //
        // if ($record instanceof DomainObjectInterface) {
        //     $record = $this->modelToRawRecordService->modelToRawRecord($record);
        // }

        $table = $record->getMainType();

        $editableFields = [];

        foreach ($this->tcaSchema->get($table)->getFields() as $fieldSchema) {

            $field = $fieldSchema->getName();

            if (!$record->has($field) || in_array($field, ['uid', 'pid'])) {
                continue;
            }

            $value = $record->get($field) ?? '';

            $canEdit = $this->editModeService->canEditField($record, $field, $request);

            if ($canEdit && is_string($value)) {
                $editableField = $this->fieldFactory->get($record, $field);

                if ($editableField) {

                    $editableFields[] =  [
                        'table' => $editableField->getTable(),
                        'name' => $editableField->getName(),
                        'field' => $editableField->getField(),
                        'title' => $editableField->getTitle(),
                        'allowNewlines' => $editableField->isAllowNewslines(),
                        'value' => $editableField->getValue(),
                        'richtext' => $editableField->isRichText(),
                        'richtextOptions' => $editableField->getRichTextOptions(),
                        'uid' => $editableField->getUid(),
                        'id' => $editableField->getId(),
                    ];
                }

            }
        }

        return [
            'record' => $this->contentElementWrapperService->getAttributes($table, $data, $request),
            'fields' => $editableFields
        ];
    }

    /**
     * Gets the resources (JS modules and css) that are required to be loaded for visual editing
     *
     * @todo: This is currently unsolved. ES modules handled by typo3 require the import map that is rendered in the head of the default typo3 html response.
     *
     * This is experimenting with adding the ES modules and the importmap to the json response so it can be added during ssr.
     * Without SSR, this will not be possible.
     *
     * Import maps cannot be added dynamicly after the headless API has been fetched. So currently there is no way of accessing the modules that render the
     * web components of visual_editor or any other required assets from the backend.
     */
    public function getResources(ServerRequestInterface $request): array
    {
        if (!$this->editModeService->isEditMode($request)) {
            return [];
        }
        $host = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();

        $css = [];
        foreach ($this->editModeService->getStyleResources() as $cssFile) {
            $resource = $this->resourceFactory->createPublicResource($cssFile);
            $css[] = (string)$this->resourcePublisher->generateUri($resource, $request, (new UriGenerationOptions(absoluteUri: true, cacheBusting: false)));
        }

        //$this->editModeService->init($request);

        // @todo: no idea how to do this cleanly: we really can't use the pageRenderer / javaScriptRenderer for this?
        $js = [];
        foreach ($this->editModeService->getJavaScriptModules() as $jsFile) {
            $resource = $this->resourceFactory->createPublicResource(str_replace(
                ["@typo3/visual-editor", "@typo3/backend"],
                ["EXT:visual_editor/Resources/Public/JavaScript", "EXT:backend/Resources/Public/JavaScript"],
                $jsFile)
            );

            $url = (string)$this->resourcePublisher->generateUri($resource, $request, (new UriGenerationOptions(absoluteUri: true, cacheBusting: false)));
            $js[] = $url . (str_ends_with($url, '.js') ? '' : '.js');
            $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
                new JavaScriptModuleInstruction($jsFile, JavaScriptModuleInstruction::FLAG_LOAD_IMPORTMAP)
            );
        }

        $rteLang = $this->resourceFactory->createPublicResource('EXT:rte_ckeditor/Resources/Public/Contrib/translations/en.js');
        $js[] = (string)$this->resourcePublisher->generateUri($rteLang, $request, (new UriGenerationOptions(absoluteUri: true, cacheBusting: false)));

        $importmap = $this->pageRenderer->getJavaScriptRenderer()->renderImportMap($host . '/');

        return [
            'language' => $this->editModeService->getLanguageLabels(),
            'styles' => $css,
            'javascript' => $js,
            'importmap' => strip_tags($importmap)
        ];
    }
}
