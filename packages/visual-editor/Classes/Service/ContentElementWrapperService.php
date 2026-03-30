<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Service;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use UnexpectedValueException;

#[Autoconfigure(public: true)]
final readonly class ContentElementWrapperService
{
    public function __construct(
        private RecordFactory $recordFactory,
        private EditModeService $editModeService,
        private TcaSchemaFactory $tcaSchema,
        private LocalizationService $localizationService,
    ) {
    }

    /**
     * @param array<string, mixed> $data the raw database row
     */
    public function wrapContentElementHtml(string $table, array $data, string $content, ServerRequestInterface $request): string
    {
        if (!$this->editModeService->isEditMode($request)) {
            return $content;
        }

        $this->editModeService->init($request);

        $tag = GeneralUtility::makeInstance(TagBuilder::class, 've-content-element', $content);
        $tag->forceClosingTag(true);

        foreach ($this->getAttributes($table, $data, $request) as $attribute => $value) {
            $tag->addAttribute($attribute, $value);
        }

        return $tag->render();
    }

    public function getAttributes(string $table, array $data, ServerRequestInterface $request): array
    {
        $canModifyRecord = true;
        /** @var BackendUserAuthentication $beUser */
        $beUser = $GLOBALS['BE_USER'];
        if (!$beUser->check('tables_modify', $table)) {
            $canModifyRecord = false; // no edit rights
        }

        if ($table === 'tt_content' && !$beUser->check('explicit_allowdeny', 'tt_content:CType:' . $data['CType'])) {
            $canModifyRecord = false;
            // no access to this content element type
        }

        $record = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $data);
        if (!$record instanceof Record) {
            throw new UnexpectedValueException('Record array must be a ' . Record::class, 1772465047);
        }

        $schema = $this->tcaSchema->get($record->getFullType());

        $hiddenFieldType = $schema->getCapability(TcaSchemaCapability::RestrictionDisabledField);
        $hiddenFieldName = $hiddenFieldType->getFieldName();
        if (
            $schema->getField($hiddenFieldName)->supportsAccessControl() && !$beUser->check(
                'non_exclude_fields',
                $record->getMainType() . ':' . $hiddenFieldName,
            )
        ) {
            $hiddenFieldName = ''; // user has no access to hidden field
        }

        $attributes = [];

        $attributes['elementName'] = $this->getContentTypeLabel($record);
        $attributes['CType'] = $record->get('CType');
        $attributes['table'] = $table;

        $uid = $record->getComputedProperties()->getLocalizedUid() ?: $record->getComputedProperties()->getVersionedUid() ?: $record->getUid();
        $attributes['id'] = $table . ':' . $uid;
        $attributes['uid'] = (string)$uid;
        $attributes['pid'] = (string)$record->getPid();
        $attributes['colPos'] = $record->get('colPos');
        $attributes['hiddenFieldName'] = $hiddenFieldName;
        if ($canModifyRecord) {
            $attributes['canModifyRecord'] = 'true';
        }

        if (!$record->getLanguageInfo()?->getTranslationParent()) {
            $attributes['canBeMoved'] = 'true';
        }

        if ($record->getSystemProperties()?->isDisabled()) {
            $attributes['isHidden'] = 'true';
        }

        if ($record->has('tx_container_parent')) {
            // EXT:container compatibility
            $attributes['tx_container_parent'] = $record->getRawRecord()->get('tx_container_parent');
            // TODO (test with sys_language_uid > 1) (test with workspace) possibly we need to find the correct overlay uid
        }

        return $attributes;
    }

    private function getContentTypeLabel(Record $record): string
    {
        $recordType = $record->getRecordType() ?? '';
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $item) {
            if ($item['value'] === $recordType && isset($item['label'])) {
                return $this->localizationService->tryTranslation($item['label']);
            }
        }

        return $recordType;
    }
}
