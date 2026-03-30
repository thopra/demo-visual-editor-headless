<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Form;

use TYPO3\CMS\Core\Schema\Field\InputFieldType;
use TYPO3\CMS\Core\Schema\Field\TextFieldType;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class FieldFactory
{
    public function __construct(
        private readonly TcaSchemaFactory $tcaSchema
    )
    {}

    public function get(RecordInterface $record, string $fieldName): ?Field
    {
        $schema = $this->tcaSchema->get($record->getFullType());
        $fieldSchema = $schema->getField($fieldName);
        if (!$fieldSchema || (!($fieldSchema instanceof InputFieldType) && !($fieldSchema instanceof TextFieldType))) {
            return null;
        }
        return GeneralUtility::makeInstance(Field::class)->init($record, $schema, $fieldSchema);
    }
}
