<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Form;

use TYPO3\CMS\Core\Schema\TcaSchema;
use TYPO3\CMS\Core\Schema\Field\InputFieldType;
use TYPO3\CMS\Core\Schema\Field\TextFieldType;
use TYPO3\CMS\Core\Domain\RecordInterface;


use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Richtext as RichtextConfiguration;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Html\RteHtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Fluid\ViewHelpers\Format\HtmlViewHelper;
use TYPO3\CMS\Frontend\Page\PageInformation;
use TYPO3\CMS\VisualEditor\Core\RichtText\RichTextConfigurationService;
use TYPO3\CMS\VisualEditor\Core\RichtText\RichTextConfigurationServiceDto;
use TYPO3\CMS\VisualEditor\Service\EditModeService;
use TYPO3\CMS\VisualEditor\Service\LocalizationService;
use TYPO3\CMS\VisualEditor\Service\ModelToRawRecordService;

final class Field
{
    protected InputFieldType|TextFieldType $fieldSchema;
    protected TcaSchema $schema;
    protected RecordInterface $record;
    protected ?array $richtextOptionsAndProcessingConfiguration = null;

    private const DEFAULT_RTE_CONTENTS_CSS_RESOURCE = 'EXT:rte_ckeditor/Resources/Public/Css/contents.css';

    public function __construct(
        private readonly RteHtmlParser $rteHtmlParser,
        private readonly RichTextConfigurationService $richTextConfigurationService,
        private readonly RichtextConfiguration $richtext,
        private readonly LocalizationService $localizationService,
    )
    {}

    public function init(RecordInterface $record, TcaSchema $schema, InputFieldType|TextFieldType $fieldSchema): self
    {
        $this->record = $record;
        $this->fieldSchema = $fieldSchema;
        $this->schema = $schema;

        return $this;
    }

    public function getUid(): int
    {
        return $this->record->getUid();
    }

    public function getId(): string
    {
        return $this->getTable() . ':' . $this->getUid();
    }

    public function getTitle(): string
    {
        $title = $this->localizationService->tryTranslation(
            'LLL:EXT:visual_editor/Resources/Private/Language/locallang.xlf:editable.title',
            arguments: [$this->getName()],
        );
        return $title;
    }

    public function getName(): string
    {
        $tableLabel = $this->schema->getTitle($this->localizationService->tryTranslation(...));
        $label = $this->localizationService->tryTranslation($this->fieldSchema->getLabel());

        $label = $tableLabel . ': ' . $label;

        return $label;
    }

    public function isAllowNewslines(): bool
    {
        return $this->fieldSchema instanceof TextFieldType;
    }

    public function getValue(): string
    {
        $value = $this->record->get($this->fieldSchema->getName()) ?? '';

        if ($this->isRichText()) {
            return $this->rteHtmlParser->transformTextForRichTextEditor($value, $this->getRichTextProcessingConfiguration());
        }
        return  str_replace('<br>', "\n", $value);
    }

    public function getField(): string
    {
        return $this->fieldSchema->getName();
    }

    public function getTable(): string
    {
        return $this->record->getMainType();
    }

    public function isRichText(): bool
    {
        return $this->fieldSchema instanceof TextFieldType && $this->fieldSchema->isRichText();
    }

    public function getRichTextOptions(): ?array
    {
        if (!$this->isRichText()) {
            return null;
        }
        return $this->getRichTextConfigurationAndProcessingOptions()[0];
    }

    public function getRichTextProcessingConfiguration(): ?array
    {
        if (!$this->isRichText()) {
            return null;
        }
        return $this->getRichTextConfigurationAndProcessingOptions()[1];
    }

    /**
     * @return array{0:string, 1:array<mixed>}
     */
    protected function getRichTextConfigurationAndProcessingOptions(): array
    {
        if ($this->richtextOptionsAndProcessingConfiguration !== null) {
            return $this->richtextOptionsAndProcessingConfiguration;
        }
        $richtextConfiguration = $this->richtext->getConfiguration(
            $this->record->getMainType(),
            $this->getField(),
            $this->record->getPid(),
            $this->record->getRecordType() ?? '',
            $this->fieldSchema->getConfiguration(),
        );

        $rawRecord = $this->record->getRawRecord() ?? $this->record;
        $richTextConfigurationServiceDto = new RichTextConfigurationServiceDto(
            tableName: $this->record->getMainType(),
            uid: $this->record->getComputedProperties()->getLocalizedUid() ?: $this->record->getComputedProperties()->getVersionedUid() ?: $this->record->getUid(),
            fieldName: $this->getField(),
            recordTypeValue: $this->record->getRecordType() ?? '',
            effectivePid: $this->record->getPid(),
            richtextConfigurationName: $richtextConfiguration['preset'],
            label: 'Text',
            placeholder: '',
            readOnly: false,
            data: $rawRecord->toArray(),
            additionalConfiguration: $richtextConfiguration['editor']['config'],
            externalPlugins: $richtextConfiguration['editor']['externalPlugins'],
        );

        $config = $this->richTextConfigurationService->resolveCkEditorConfiguration($richTextConfigurationServiceDto);
        if (is_array($config['contentsCss'] ?? null)) {
            $defaultRteContentsCss = PathUtility::getAbsoluteWebPath(
                GeneralUtility::createVersionNumberedFilename(
                    GeneralUtility::getFileAbsFileName(self::DEFAULT_RTE_CONTENTS_CSS_RESOURCE),
                ),
            );
            $contentsCss = [];
            foreach ($config['contentsCss'] as $path) {
                if (is_string($path) && $path === $defaultRteContentsCss) {
                    continue;
                }

                $contentsCss[] = $path;
            }

            $config['contentsCss'] = $contentsCss;
        }

        unset($config['height']); // height is set by the content itself and css
        $config['debug'] = false; // for now we disable debug mode

        $this->richtextOptionsAndProcessingConfiguration = [$config, $richtextConfiguration['proc.'] ?? []];
        return $this->richtextOptionsAndProcessingConfiguration;
    }
}
