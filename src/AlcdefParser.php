<?php

namespace dnl_blkv\alcdef;

/**
 */
class AlcdefParser
{
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
    const FIELD_COMP = 'COMP';
    const FIELD_DATA = 'DATA';
    const FIELD_DELIMITER = 'DELIMITER';

    /**
     * Constants to split an ALCDEF unit into data and metadata.
     */
    const PATTERN_ALCDEF_METADATA_DATA = '@STARTMETADATA\\n(.*)\\nENDMETADATA\\n(.*)\\n@ms';
    const SUBMATCH_INDEX_METADATA = 1;
    const SUBMATCH_INDEX_DATA = 2;

    /**
     * Pattern to match the comparisons (*COMP{X}).
     */
    const PATTERN_COMPARISON = '@^COMP([A-Z]+)([0-9]+)$@';

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
    public function parse($alcdefString)
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
            if (!isset($this->alcdefArray[self::FIELD_COMP][$comparison[2]])) {
                $this->alcdefArray[self::FIELD_COMP][$comparison[2]] = [];
            }

            $this->alcdefArray[self::FIELD_COMP][$comparison[2]][$comparison[1]] =
                $field[self::ALCDEF_LINE_PART_INDEX_VALUE];
        } else {
            if ($field[self::ALCDEF_LINE_PART_INDEX_KEY] === self::FIELD_DELIMITER) {
                $this->setDelimiterDataValueFromLiteral($field[self::ALCDEF_LINE_PART_INDEX_VALUE]);
            }

            $this->alcdefArray[$field[self::ALCDEF_LINE_PART_INDEX_KEY]] = $field[self::ALCDEF_LINE_PART_INDEX_VALUE];
        }
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
            self::DATA_KEY_JD => $dataValues[0],
            self::DATA_KEY_MAG => $dataValues[1],
        ];

        if (isset($dataValues[2])) {
            $dataMap[self::DATA_KEY_MAGERR] = $dataValues[2];
        }

        if (isset($dataValues[3])) {
            $dataMap[self::DATA_KEY_AIRMASS] = $dataValues[3];
        }

        $this->alcdefArray[self::FIELD_DATA][] = $dataMap;
    }
}
