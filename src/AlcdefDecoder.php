<?php

namespace dnl_blkv\alcdef;

use Exception;

/**
 */
class AlcdefDecoder
{
    /**
     * Error constants.
     */
    const ERROR_UNABLE_TO_DETERMINE_FIELD_TYPE = 'Unable to determine field type. Field name: %s.';

    /**
     * Newline values to use in our ALCDEF strings.
     */
    const PATTERN_NEW_LINE_ANY = '@\\r?\\n@';
    const NEW_LINE_UNIX = "\n";

    /**
     * Delimiters to split ALCDEF document into lines.
     */
    const DELIMITER_LINES = self::NEW_LINE_UNIX;

    /**
     * ALCDEF field names.
     */
    const FIELD_BIBCODE = 'BIBCODE';
    const FIELD_CIBAND = 'CIBAND';
    const FIELD_CICORRECTION = 'CICORRECTION';
    const FIELD_CITARGET = 'CITARGET';
    const FIELD_COMMENT = 'COMMENT';
    const FIELD_CONTACTINFO = 'CONTACTINFO';
    const FIELD_CONTACTNAME = 'CONTACTNAME';
    const FIELD_DATA = 'DATA';
    const FIELD_DELIMITER = 'DELIMITER';
    const FIELD_DIFFERMAGS = 'DIFFERMAGS';
    const FIELD_FILTER = 'FILTER';
    const FIELD_LCBLOCKID = 'LCBLOCKID';
    const FIELD_LTCAPP = 'LTCAPP';
    const FIELD_LTCDAYS = 'LTCDAYS';
    const FIELD_LTCTYPE = 'LTCTYPE';
    const FIELD_MAGADJUST = 'MAGADJUST';
    const FIELD_MAGBAND = 'MAGBAND';
    const FIELD_MPCDESIG = 'MPCDESIG';
    const FIELD_OBJECTDEC = 'OBJECTDEC';
    const FIELD_OBJECTNAME = 'OBJECTNAME';
    const FIELD_OBJECTNUMBER = 'OBJECTNUMBER';
    const FIELD_OBJECTRA = 'OBJECTRA';
    const FIELD_OBSERVERS = 'OBSERVERS';
    const FIELD_OBSLATITUDE = 'OBSLATITUDE';
    const FIELD_OBSLONGITUDE = 'OBSLONGITUDE';
    const FIELD_PABB = 'PABB';
    const FIELD_PABL = 'PABL';
    const FIELD_PHASE = 'PHASE';
    const FIELD_PUBLICATION = 'PUBLICATION';
    const FIELD_REDUCEDMAGS = 'REDUCEDMAGS';
    const FIELD_REVISEDDATA = 'REVISEDDATA';
    const FIELD_SESSIONDATE = 'SESSIONDATE';
    const FIELD_SESSIONTIME = 'SESSIONTIME';
    const FIELD_STANDARD = 'STANDARD';
    const FIELD_UCORMAG = 'UCORMAG';

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
     * Fields and sub-fields for comparisons.
     */
    const FIELD_COMP = 'COMP';
    const SUBFIELD_COMP_CI = 'CI';
    const SUBFIELD_COMP_DEC = 'DEC';
    const SUBFIELD_COMP_MAG = 'MAG';
    const SUBFIELD_COMP_NAME = 'NAME';
    const SUBFIELD_COMP_RA = 'RA';

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
     * Keys found in each data line.
     */
    const DATA_KEY_JD = 'JD';
    const DATA_KEY_MAG = 'MAG';
    const DATA_KEY_MAGERR = 'MAGERR';
    const DATA_KEY_AIRMASS = 'AIRMASS';

    /**
     * @var mixed[]
     */
    private $alcdefArray = [
        self::FIELD_DATA => [],
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

            if (!isset($this->alcdefArray[self::FIELD_COMP][$starNumber])) {
                $this->alcdefArray[self::FIELD_COMP][$starNumber] = [];
            }

            $parameter = $comparison[self::INDEX_COMPARISON_PARAMETER];
            $this->alcdefArray[self::FIELD_COMP][$starNumber][$parameter] = $this->castValue(
                $parameter,
                $field[self::ALCDEF_LINE_PART_INDEX_VALUE]
            );
        } else {
            if ($field[self::ALCDEF_LINE_PART_INDEX_KEY] === self::FIELD_DELIMITER) {
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
        if ($this->isString($field)) {
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
            self::FIELD_BIBCODE => true,
            self::FIELD_CIBAND => true,
            self::FIELD_COMMENT => true,
            self::SUBFIELD_COMP_DEC => true,
            self::SUBFIELD_COMP_NAME => true,
            self::SUBFIELD_COMP_RA => true,
            self::FIELD_CONTACTINFO => true,
            self::FIELD_CONTACTNAME => true,
            self::FIELD_DELIMITER => true,
            self::FIELD_FILTER => true,
            self::FIELD_LTCAPP => true,
            self::FIELD_LTCTYPE => true,
            self::FIELD_MAGBAND => true,
            self::FIELD_MPCDESIG => true,
            self::FIELD_OBJECTDEC => true,
            self::FIELD_OBJECTNAME => true,
            self::FIELD_OBJECTRA => true,
            self::FIELD_OBSERVERS => true,
            self::FIELD_PUBLICATION => true,
            self::FIELD_REDUCEDMAGS => true,
            self::FIELD_SESSIONDATE => true,
            self::FIELD_SESSIONTIME => true,
            self::FIELD_STANDARD => true,
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
            self::FIELD_CITARGET => true,
            self::SUBFIELD_COMP_CI => true,
            self::SUBFIELD_COMP_MAG => true,
            self::FIELD_LTCDAYS => true,
            self::FIELD_MAGADJUST => true,
            self::FIELD_OBSLATITUDE => true,
            self::FIELD_OBSLONGITUDE => true,
            self::FIELD_PABB => true,
            self::FIELD_PABL => true,
            self::FIELD_PHASE => true,
            self::FIELD_UCORMAG => true,
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
            self::FIELD_CICORRECTION => true,
            self::FIELD_DIFFERMAGS => true,
            self::FIELD_REVISEDDATA => true,
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
            self::FIELD_LCBLOCKID => true,
            self::FIELD_OBJECTNUMBER => true,
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
            self::DATA_KEY_JD => (double)$dataValues[self::INDEX_DATA_JD],
            self::DATA_KEY_MAG => (double)$dataValues[self::INDEX_DATA_MAG],
        ];

        if (isset($dataValues[self::INDEX_DATA_MAGERR])) {
            $dataMap[self::DATA_KEY_MAGERR] = (double)$dataValues[self::INDEX_DATA_MAGERR];
        }

        if (isset($dataValues[self::INDEX_DATA_AIRMASS])) {
            $dataMap[self::DATA_KEY_AIRMASS] = (double)$dataValues[self::INDEX_DATA_AIRMASS];
        }

        $this->alcdefArray[self::FIELD_DATA][] = $dataMap;
    }
}
