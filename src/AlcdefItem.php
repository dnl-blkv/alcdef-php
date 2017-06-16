<?php

namespace dnl_blkv\alcdef;

/**
 */
class AlcdefItem
{
    /**
     * Newline values to use in our ALCDEF strings.
     */
    const PATTERN_NEW_LINE_ANY = '@\\r?\\n@';
    const NEW_LINE_UNIX = "\n";

    /**
     * Delimiters to split alcdef into meaningful chunks of data.
     */
    const DELIMITER_ALCDEF_KEY_VALUE = '=';
    const DELIMITER_ALCDEF_LINES = self::NEW_LINE_UNIX;

    /**
     * ALCDEF field names.
     */
    const FIELD_DATA = 'DATA';
    const FIELD_OBJECT_NAME = 'OBJECTNAME';

    /**
     * Delimiter ending the metadata.
     */
    const PATTERN_ALCDEF_METADATA_DATA = '@STARTMETADATA\\n(.*)\\nENDMETADATA\\n(.*)\\n@ms';

    /**
     * @var mixed[]
     */
    private $alcdefArray = [];

    /**
     * @param string $alcdefString
     */
    public function __construct($alcdefString)
    {
        $this->alcdefArray = static::parseAlcdefArray($alcdefString);
    }

    /**
     * @param string $alcdefString
     *
     * @return mixed[]
     */
    private static function parseAlcdefArray($alcdefString)
    {
        $alcdefString = static::cleanString($alcdefString);
        preg_match(self::PATTERN_ALCDEF_METADATA_DATA, $alcdefString, $alcdefParts);
        $alcdefMetadata = static::parseAlcdefMetadata($alcdefParts[1]);
        $alcdefData = static::parseAlcdefData($alcdefParts[2]);

        return array_merge($alcdefMetadata, [self::FIELD_DATA => $alcdefData]);
    }

    /**
     * @param $string
     *
     * @return string
     */
    private static function cleanString($string)
    {
        return preg_replace(self::PATTERN_NEW_LINE_ANY, self::NEW_LINE_UNIX, $string);
    }

    /**
     * @param string $alcdefMetadataString
     *
     * @return mixed[]
     */
    private static function parseAlcdefMetadata($alcdefMetadataString)
    {
        $metadataFields = [];

        foreach (explode(self::DELIMITER_ALCDEF_LINES, $alcdefMetadataString) as $line) {
            $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $line, 2);
            $metadataFields[$field[0]] = $field[1];
        }

        return $metadataFields;
    }

    /**
     * @param string $alcdefDataString
     *
     * @return mixed[]
     */
    private static function parseAlcdefData($alcdefDataString)
    {
        $data = [];

        foreach (explode(self::DELIMITER_ALCDEF_LINES, $alcdefDataString) as $line) {
            $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $line, 2);
            $data[] = $field[1];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return $this->alcdefArray[self::FIELD_OBJECT_NAME];
    }
}
