<?php

namespace dnl_blkv\alcdef;

use Exception;

/**
 */
class SimpleAlcdefCodec implements AlcdefDecoder, AlcdefEncoder
{
    /**
     * Error constants.
     */
    const ERROR_UNABLE_TO_DETERMINE_FIELD_TYPE = 'Unable to determine field type. Field name: %s.';

    /**
     * Pattern and replacement used for newline normalization to Unix format.
     */
    const PATTERN_NEW_LINE_ANY = '@\\r?\\n@';
    const NEW_LINE_UNIX = "\n";

    /**
     * Delimiters to split ALCDEF document into lines.
     */
    const DELIMITER_LINES = self::NEW_LINE_UNIX;

    /**
     * String representation of the "true" boolean value.
     */
    const STRING_BOOL_TRUE = 'TRUE';

    /**
     * Pattern to match the comparisons (*COMP{X}).
     */
    const PATTERN_COMPARISON = '@^COMP([A-Z]+)([0-9]+)$@';

    /**
     * Indices of sub-keys in comparison keys.
     */
    const INDEX_COMPARISON_PARAMETER = 1;
    const INDEX_COMPARISON_STAR_NUMBER = 2;

    /**
     * Constants to split an ALCDEF unit into data and metadata.
     */
    const PATTERN_ALCDEF_METADATA_DATA = '@STARTMETADATA\\n(.*)\\nENDMETADATA\\n(.*)\\n@ms';
    const SUBMATCH_INDEX_METADATA = 1;
    const SUBMATCH_INDEX_DATA = 2;

    /**
     * Constants to fetch key and value from the ACLDEF line.
     */
    const DELIMITER_ALCDEF_KEY_VALUE = '=';
    const ALCDEF_LINE_PART_COUNT = 2;
    const ALCDEF_LINE_PART_INDEX_KEY = 0;
    const ALCDEF_LINE_PART_INDEX_VALUE = 1;

    /**
     * Constants to denote delimiters of the values in data lines.
     */
    const DELIMITER_LITERAL_TAB = 'TAB';
    const DELIMITER_TAB = '\t';
    const DELIMITER_LITERAL_PIPE = 'PIPE';
    const DELIMITER_PIPE = '|';

    /**
     * Delimiter for the data elements in a data line.
     */
    const DELIMITER_DATA_VALUE_DEFAULT = self::DELIMITER_PIPE;

    /**
     * Indices of the data elements in data line.
     */
    const INDEX_DATA_JD = 0;
    const INDEX_DATA_MAG = 1;
    const INDEX_DATA_MAGERR = 2;
    const INDEX_DATA_AIRMASS = 3;

    /**
     * @var mixed[]
     */
    private $alcdefArray = [
        AlcdefFormat::FIELD_DATA => [],
    ];

    /**
     * @var string
     */
    private $delimiterDataValue = self::DELIMITER_DATA_VALUE_DEFAULT;

    /**
     * @param string $alcdefString
     *
     * @return mixed[]
     */
    public function decode($alcdefString)
    {
        $alcdefString = $this->normalizeNewlines($alcdefString);
        $alcdefParts = $this->parseAlcdefParts($alcdefString);
        $this->loadMetadata($alcdefParts[self::SUBMATCH_INDEX_METADATA]);
        $this->loadData($alcdefParts[self::SUBMATCH_INDEX_DATA]);

        return $this->alcdefArray;
    }

    /**
     * @param $string
     *
     * @return string
     */
    private function normalizeNewlines($string)
    {
        return preg_replace(self::PATTERN_NEW_LINE_ANY, self::NEW_LINE_UNIX, $string);
    }

    /**
     * @param $alcdefString
     *
     * @return string[]
     */
    private function parseAlcdefParts($alcdefString)
    {
        preg_match(self::PATTERN_ALCDEF_METADATA_DATA, $alcdefString, $alcdefParts);

        return $alcdefParts;
    }

    /**
     * @param string $metadataString
     */
    private function loadMetadata($metadataString)
    {
        foreach (explode(self::DELIMITER_LINES, $metadataString) as $line) {
            $this->loadMetadataLine($line);
        }
    }

    /**
     * @param string $line
     */
    private function loadMetadataLine($line)
    {
        $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $line, self::ALCDEF_LINE_PART_COUNT);

        if (preg_match(self::PATTERN_COMPARISON, $field[self::ALCDEF_LINE_PART_INDEX_KEY], $comparison)) {
            $starNumber = $comparison[self::INDEX_COMPARISON_STAR_NUMBER];

            if (!isset($this->alcdefArray[AlcdefFormat::FIELD_COMP][$starNumber])) {
                $this->alcdefArray[AlcdefFormat::FIELD_COMP][$starNumber] = [];
            }

            $parameter = $comparison[self::INDEX_COMPARISON_PARAMETER];
            $this->alcdefArray[AlcdefFormat::FIELD_COMP][$starNumber][$parameter] = $this->castValue(
                $parameter,
                $field[self::ALCDEF_LINE_PART_INDEX_VALUE]
            );
        } else {
            if ($field[self::ALCDEF_LINE_PART_INDEX_KEY] === AlcdefFormat::FIELD_DELIMITER) {
                $this->setDelimiterDataValueFromLiteral($field[self::ALCDEF_LINE_PART_INDEX_VALUE]);
            }

            $this->alcdefArray[$field[self::ALCDEF_LINE_PART_INDEX_KEY]] = $this->castValue(
                $field[self::ALCDEF_LINE_PART_INDEX_KEY],
                $field[self::ALCDEF_LINE_PART_INDEX_VALUE]
            );
        }
    }

    /**
     * @param string $field
     * @param string $value
     *
     * @return string|int|bool|double
     * @throws Exception when the field name is unexpected.
     */
    private function castValue($field, $value)
    {
        if (empty($value)) {
            return null;
        } elseif ($this->isString($field)) {
            return (string)$value;
        } elseif ($this->isDouble($field)) {
            return (double)$value;
        } elseif ($this->isBoolean($field)) {
            return $value === self::STRING_BOOL_TRUE;
        } elseif ($this->isInteger($field)) {
            return (int)$value;
        } else {
            throw new Exception(sprintf(self::ERROR_UNABLE_TO_DETERMINE_FIELD_TYPE, $field));
        }
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private function isString($field)
    {
        $stringFields = [
            AlcdefFormat::FIELD_BIBCODE => true,
            AlcdefFormat::FIELD_CIBAND => true,
            AlcdefFormat::FIELD_COMMENT => true,
            AlcdefFormat::SUBFIELD_COMP_DEC => true,
            AlcdefFormat::SUBFIELD_COMP_NAME => true,
            AlcdefFormat::SUBFIELD_COMP_RA => true,
            AlcdefFormat::FIELD_CONTACTINFO => true,
            AlcdefFormat::FIELD_CONTACTNAME => true,
            AlcdefFormat::FIELD_DELIMITER => true,
            AlcdefFormat::FIELD_FILTER => true,
            AlcdefFormat::FIELD_LTCAPP => true,
            AlcdefFormat::FIELD_LTCTYPE => true,
            AlcdefFormat::FIELD_MAGBAND => true,
            AlcdefFormat::FIELD_MPCDESIG => true,
            AlcdefFormat::FIELD_OBJECTDEC => true,
            AlcdefFormat::FIELD_OBJECTNAME => true,
            AlcdefFormat::FIELD_OBJECTRA => true,
            AlcdefFormat::FIELD_OBSERVERS => true,
            AlcdefFormat::FIELD_PUBLICATION => true,
            AlcdefFormat::FIELD_REDUCEDMAGS => true,
            AlcdefFormat::FIELD_SESSIONDATE => true,
            AlcdefFormat::FIELD_SESSIONTIME => true,
            AlcdefFormat::FIELD_STANDARD => true,
        ];

        return isset($stringFields[$field]);
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private function isDouble($field)
    {
        $doubleFields = [
            AlcdefFormat::FIELD_CITARGET => true,
            AlcdefFormat::SUBFIELD_COMP_CI => true,
            AlcdefFormat::SUBFIELD_COMP_MAG => true,
            AlcdefFormat::FIELD_LTCDAYS => true,
            AlcdefFormat::FIELD_MAGADJUST => true,
            AlcdefFormat::FIELD_OBSLATITUDE => true,
            AlcdefFormat::FIELD_OBSLONGITUDE => true,
            AlcdefFormat::FIELD_PABB => true,
            AlcdefFormat::FIELD_PABL => true,
            AlcdefFormat::FIELD_PHASE => true,
            AlcdefFormat::FIELD_UCORMAG => true,
        ];

        return isset($doubleFields[$field]);
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private function isBoolean($field)
    {
        $booleanFields = [
            AlcdefFormat::FIELD_CICORRECTION => true,
            AlcdefFormat::FIELD_DIFFERMAGS => true,
            AlcdefFormat::FIELD_REVISEDDATA => true,
        ];

        return isset($booleanFields[$field]);
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private function isInteger($field)
    {
        $integerFields = [
            AlcdefFormat::FIELD_LCBLOCKID => true,
            AlcdefFormat::FIELD_OBJECTNUMBER => true,
        ];

        return isset($integerFields[$field]);
    }

    /**
     * @param string $delimiterLiteral
     */
    private function setDelimiterDataValueFromLiteral($delimiterLiteral)
    {
        if ($delimiterLiteral === self::DELIMITER_LITERAL_TAB) {
            $this->delimiterDataValue = self::DELIMITER_TAB;
        } elseif ($delimiterLiteral === self::DELIMITER_LITERAL_PIPE) {
            $this->delimiterDataValue = self::DELIMITER_PIPE;
        }
    }

    /**
     * @param string $dataString
     */
    private function loadData($dataString)
    {
        foreach (explode(self::DELIMITER_LINES, $dataString) as $line) {
            $this->loadDataLine($line);
        }
    }

    /**
     * @param string $dataLine
     */
    private function loadDataLine($dataLine)
    {
        $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $dataLine, self::SUBMATCH_INDEX_DATA);
        $dataValues = explode($this->delimiterDataValue, $field[self::SUBMATCH_INDEX_METADATA]);
        $dataMap = [
            AlcdefFormat::DATA_KEY_JD => (double)$dataValues[self::INDEX_DATA_JD],
            AlcdefFormat::DATA_KEY_MAG => (double)$dataValues[self::INDEX_DATA_MAG],
        ];

        if (isset($dataValues[self::INDEX_DATA_MAGERR])) {
            $dataMap[AlcdefFormat::DATA_KEY_MAGERR] = (double)$dataValues[self::INDEX_DATA_MAGERR];
        }

        if (isset($dataValues[self::INDEX_DATA_AIRMASS])) {
            $dataMap[AlcdefFormat::DATA_KEY_AIRMASS] = (double)$dataValues[self::INDEX_DATA_AIRMASS];
        }

        $this->alcdefArray[AlcdefFormat::FIELD_DATA][] = $dataMap;
    }

    /**
     * @param AlcdefItem $alcdefItem
     *
     * @return string
     */
    public function encode(AlcdefItem $alcdefItem)
    {

    }
}
