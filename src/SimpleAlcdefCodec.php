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
    const NEW_LINE_WINDOWS = "\r\n";

    /**
     * Delimiters to split ALCDEF document into lines.
     */
    const DELIMITER_LINES = self::NEW_LINE_WINDOWS;

    /**
     * String representation of the "true" boolean value.
     */
    const STRING_BOOL_TRUE = 'TRUE';
    const STRING_BOOL_FALSE = 'FALSE';

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
    const PATTERN_ALCDEF_METADATA_DATA = '@STARTMETADATA\\r\\n(.*)\\r\\nENDMETADATA\\r\\n(.*)\\r\\n@ms';
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
     * Decimal precision constants.
     */
    const DECIMAL_PRECISION_ANY = -1;
    const DECIMAL_PRECISION_NONE = 0;
    const DECIMAL_PRECISION_1 = 1;
    const DECIMAL_PRECISION_2 = 2;
    const DECIMAL_PRECISION_3 = 3;
    const DECIMAL_PRECISION_6 = 6;

    /**
     * Delimiter between whole and decimal parts of decimal numbers.
     */
    const DELIMITER_DECIMAL = '.';

    /**
     * (Meta)format constants for formatting doubles.
     */
    const META_FORMAT_DOUBLE_WITH_PRECISION = '%%.%sf';
    const META_FORMAT_DOUBLE_WITH_PRECISION_AND_SIGN = '%%+.%sf';
    const FORMAT_DOUBLE_WITH_SIGN = '%+f';

    /**
     * Offset of one in a string or array.
     */
    const OFFSET_ONE = 1;

    /**
     * @var mixed[]
     */
    protected $alcdefDefinition;

    /**
     * @var string
     */
    protected $alcdefString;

    /**
     * @var string
     */
    protected $delimiterDataValue = self::DELIMITER_DATA_VALUE_DEFAULT;

    /**
     * @param string $alcdefString
     *
     * @return mixed[]
     */
    public function decode($alcdefString)
    {
        $this->resetAlcdefDefinition();
        $alcdefString = $this->normalizeNewlines($alcdefString);
        $alcdefParts = $this->parseAlcdefParts($alcdefString);
        $this->loadMetadata($alcdefParts[self::SUBMATCH_INDEX_METADATA]);
        $this->loadData($alcdefParts[self::SUBMATCH_INDEX_DATA]);
        $alcdefDefinition = $this->alcdefDefinition;
        $this->resetAlcdefDefinition();

        return $alcdefDefinition;
    }

    /**
     */
    private function resetAlcdefDefinition()
    {
        $this->alcdefDefinition = [
            AlcdefFormat::FIELD_DATA => [],
        ];
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected function normalizeNewlines($string)
    {
        return preg_replace(self::PATTERN_NEW_LINE_ANY, self::NEW_LINE_WINDOWS, $string);
    }

    /**
     * @param $alcdefString
     *
     * @return string[]
     */
    protected function parseAlcdefParts($alcdefString)
    {
        preg_match(self::PATTERN_ALCDEF_METADATA_DATA, $alcdefString, $alcdefParts);

        return $alcdefParts;
    }

    /**
     * @param string $metadataString
     */
    protected function loadMetadata($metadataString)
    {
        foreach (explode(self::DELIMITER_LINES, $metadataString) as $line) {
            $this->loadMetadataLine($line);
        }
    }

    /**
     * @param string $line
     */
    protected function loadMetadataLine($line)
    {
        $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $line, self::ALCDEF_LINE_PART_COUNT);

        if (preg_match(self::PATTERN_COMPARISON, $field[self::ALCDEF_LINE_PART_INDEX_KEY], $comparison)) {
            $starNumber = $comparison[self::INDEX_COMPARISON_STAR_NUMBER];

            if (!isset($this->alcdefDefinition[AlcdefFormat::FIELD_COMP][$starNumber])) {
                $this->alcdefDefinition[AlcdefFormat::FIELD_COMP][$starNumber] = [];
            }

            $parameter = $comparison[self::INDEX_COMPARISON_PARAMETER];
            $this->alcdefDefinition[AlcdefFormat::FIELD_COMP][$starNumber][$parameter] = $this->castValue(
                $parameter,
                $field[self::ALCDEF_LINE_PART_INDEX_VALUE]
            );
        } else {
            if ($field[self::ALCDEF_LINE_PART_INDEX_KEY] === AlcdefFormat::FIELD_DELIMITER) {
                $this->setDelimiterDataValueFromLiteral($field[self::ALCDEF_LINE_PART_INDEX_VALUE]);
            }

            $this->alcdefDefinition[$field[self::ALCDEF_LINE_PART_INDEX_KEY]] = $this->castValue(
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
    protected function castValue($field, $value)
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
    protected function isString($field)
    {
        $stringFields = [
            AlcdefFormat::COMP_FIELD_DEC => true,
            AlcdefFormat::COMP_FIELD_NAME => true,
            AlcdefFormat::COMP_FIELD_RA => true,
            AlcdefFormat::FIELD_BIBCODE => true,
            AlcdefFormat::FIELD_CIBAND => true,
            AlcdefFormat::FIELD_COMMENT => true,
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
    protected function isDouble($field)
    {
        $doubleFields = [
            AlcdefFormat::COMP_FIELD_CI => true,
            AlcdefFormat::COMP_FIELD_MAG => true,
            AlcdefFormat::FIELD_CITARGET => true,
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
    protected function isBoolean($field)
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
    protected function isInteger($field)
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
    protected function setDelimiterDataValueFromLiteral($delimiterLiteral)
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
    protected function loadData($dataString)
    {
        foreach (explode(self::DELIMITER_LINES, $dataString) as $line) {
            $this->loadDataLine($line);
        }
    }

    /**
     * @param string $dataLine
     */
    protected function loadDataLine($dataLine)
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

        $this->alcdefDefinition[AlcdefFormat::FIELD_DATA][] = $dataMap;
    }

    /**
     * @param mixed[] $definition
     *
     * @return string
     */
    public function encode(array $definition)
    {
        $this->resetAlcdefString();

        foreach ($definition as $key => $value) {
            if (!$this->isSpecialKey($key)) {
                $valueEncoded = $this->encodeValue($key, $value);
                $this->alcdefString .= $key . self::DELIMITER_ALCDEF_KEY_VALUE .
                    $valueEncoded . self::NEW_LINE_WINDOWS;
            }
        }

        $compSorted = $definition[AlcdefFormat::FIELD_COMP];
        ksort($compSorted);

        foreach ($compSorted as $compIndex => $compBody) {
            $this->alcdefString .=
                $this->encodeCompField(AlcdefFormat::COMP_FIELD_NAME, $compIndex, $compBody) .
                $this->encodeCompField(AlcdefFormat::COMP_FIELD_RA, $compIndex, $compBody) .
                $this->encodeCompField(AlcdefFormat::COMP_FIELD_DEC, $compIndex, $compBody) .
                $this->encodeCompField(AlcdefFormat::COMP_FIELD_MAG, $compIndex, $compBody) .
                $this->encodeCompField(AlcdefFormat::COMP_FIELD_CI, $compIndex, $compBody);
        }

        $this->alcdefString .= AlcdefFormat::TAG_ENDMETADATA . self::NEW_LINE_WINDOWS;
        $this->setDelimiterDataValueFromLiteral($this->alcdefDefinition[AlcdefFormat::FIELD_DELIMITER]);

        foreach ($definition[AlcdefFormat::FIELD_DATA] as $dataEntry) {
            $this->alcdefString .= $this->encodeDataEntry($dataEntry);
        }

        $alcdefString = $this->alcdefString;
        $this->resetAlcdefString();

        return $alcdefString;
    }

    /**
     */
    private function resetAlcdefString()
    {
        $this->alcdefString = AlcdefFormat::TAG_STARTMETADATA . self::NEW_LINE_WINDOWS;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isSpecialKey($key)
    {
        $specialKeys = [
            AlcdefFormat::FIELD_COMP => true,
            AlcdefFormat::FIELD_DATA => true,
        ];

        return isset($specialKeys[$key]);
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     *
     * @return string
     */
    private function encodeValue($fieldName, $value)
    {
        if ($this->isBoolean($fieldName)) {
            return $value ? self::STRING_BOOL_TRUE : self::STRING_BOOL_FALSE;
        } elseif ($this->isDouble($fieldName)) {
            return $this->formatDoubleValue($fieldName, $value);
        } else {
            return $value;
        }
    }

    /**
     * @param string $fieldName
     * @param double $value
     *
     * @return string
     */
    private function formatDoubleValue($fieldName, $value)
    {
        return $this->formatDoubleToPrecisionWithSign(
            $value,
            $this->determinePrecisionForDoubleField($fieldName)
        );
    }

    /**
     * @param double $value
     * @param int $precision
     *
     * @return string
     */
    private function formatDoubleToPrecisionWithSign($value, $precision)
    {
        if ($precision < self::DECIMAL_PRECISION_NONE) {
            return sprintf(self::FORMAT_DOUBLE_WITH_SIGN, $value);
        } else {
            return $this->formatDoubleToPrecisionWithMetaFormat(
                self::META_FORMAT_DOUBLE_WITH_PRECISION_AND_SIGN,
                $value,
                $precision
            );
        }
    }

    /**
     * @param string $metaFormat
     * @param double $value
     * @param int $precision
     *
     * @return string
     */
    private function formatDoubleToPrecisionWithMetaFormat($metaFormat, $value, $precision)
    {
        $format = sprintf($metaFormat, $precision);

        return sprintf($format, $value);
    }

    /**
     * @param string $fieldName
     *
     * @return int
     */
    private function determinePrecisionForDoubleField($fieldName)
    {
        if ($this->isPrecision1($fieldName)) {
            return self::DECIMAL_PRECISION_1;
        } elseif ($this->isPrecision2($fieldName)) {
            return self::DECIMAL_PRECISION_2;
        } elseif ($this->isPrecision3($fieldName)) {
            return self::DECIMAL_PRECISION_3;
        } elseif ($this->isPrecision6($fieldName)) {
            return self::DECIMAL_PRECISION_6;
        } else {
            return self::DECIMAL_PRECISION_ANY;
        }
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    private function isPrecision1($fieldName)
    {
        $fieldsWithPrecisionMax3 = [
            AlcdefFormat::FIELD_PABB => true,
            AlcdefFormat::FIELD_PABL => true,
        ];

        return isset($fieldsWithPrecisionMax3[$fieldName]);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    private function isPrecision2($fieldName)
    {
        $fieldsWithPrecisionMax3 = [
            AlcdefFormat::FIELD_PHASE => true,
        ];

        return isset($fieldsWithPrecisionMax3[$fieldName]);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    private function isPrecision3($fieldName)
    {
        $fieldsWithPrecisionMax3 = [
            AlcdefFormat::COMP_FIELD_CI => true,
            AlcdefFormat::COMP_FIELD_MAG => true,
            AlcdefFormat::FIELD_CITARGET => true,
            AlcdefFormat::FIELD_MAGADJUST => true,
        ];

        return isset($fieldsWithPrecisionMax3[$fieldName]);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    private function isPrecision6($fieldName)
    {
        $fieldsWithPrecisionMax6 = [
            AlcdefFormat::FIELD_OBSLATITUDE => true,
            AlcdefFormat::FIELD_OBSLONGITUDE => true,
        ];

        return isset($fieldsWithPrecisionMax6[$fieldName]);
    }

    /**
     * @param string $compField
     * @param int $compIndex
     * @param mixed[] $compBody
     *
     * @return string
     */
    private function encodeCompField($compField, $compIndex, $compBody)
    {
        return
            AlcdefFormat::FIELD_COMP . $compField . $compIndex .
            self::DELIMITER_ALCDEF_KEY_VALUE .
            $this->encodeValue($compField, $compBody[$compField]) .
            self::NEW_LINE_WINDOWS;
    }

    /**
     * @param double[] $dataEntry
     *
     * @return string
     */
    private function encodeDataEntry($dataEntry)
    {
        return
            AlcdefFormat::FIELD_DATA . self::DELIMITER_ALCDEF_KEY_VALUE .
            $this->formatDoubleToMinPrecision(
                $dataEntry[AlcdefFormat::DATA_KEY_JD],
                self::DECIMAL_PRECISION_6
            ) . $this->delimiterDataValue .
            $this->formatDoubleToPrecision(
                $dataEntry[AlcdefFormat::DATA_KEY_MAG],
                self::DECIMAL_PRECISION_3
            ) . $this->delimiterDataValue .
            $this->formatDoubleToPrecision(
                $dataEntry[AlcdefFormat::DATA_KEY_MAGERR],
                self::DECIMAL_PRECISION_3
            ) . $this->delimiterDataValue .
            $this->formatDoubleToPrecision(
                $dataEntry[AlcdefFormat::DATA_KEY_AIRMASS],
                self::DECIMAL_PRECISION_3
            ) . $this->delimiterDataValue .
            self::NEW_LINE_WINDOWS;
    }

    /**
     * @param double $double
     * @param string $precision
     *
     * @return string
     */
    private function formatDoubleToMinPrecision($double, $precision)
    {
        $decimalCountActual = $this->countDecimals($double);

        if ($decimalCountActual < $precision) {
            return $this->formatDoubleToPrecision($double, $precision);
        }

        return $double;
    }

    /**
     * @param double $double
     *
     * @return int
     */
    private function countDecimals($double)
    {
        return max(
            self::DECIMAL_PRECISION_NONE,
            strlen(strrchr($double, self::DELIMITER_DECIMAL)) - self::OFFSET_ONE
        );
    }

    /**
     * @param double $value
     * @param int $precision
     *
     * @return string
     */
    private function formatDoubleToPrecision($value, $precision)
    {
        return $this->formatDoubleToPrecisionWithMetaFormat(
            self::META_FORMAT_DOUBLE_WITH_PRECISION,
            $value,
            $precision
        );
    }
}
