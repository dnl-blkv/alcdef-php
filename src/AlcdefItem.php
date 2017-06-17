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
     * Delimiters to split ALCDEF document into lines.
     */
    const DELIMITER_LINES = self::NEW_LINE_UNIX;

    /**
     * ALCDEF field names.
     */
    const FIELD_DATA = 'DATA';
    const FIELD_BIBCODE = 'BIBCODE';
    const FIELD_CIBAND = 'CIBAND';
    const FIELD_CICORRECTION = 'CICORRECTION';
    const FIELD_CITARGET = 'CITARGET';
    const FIELD_OBJECTNAME = 'OBJECTNAME';
    const FIELD_OBJECTNUMBER = 'OBJECTNUMBER';

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
    const PART_COUNT_ALCDEF_LINE = 2;
    const ALCDEF_LINE_PART_INDEX_KEY = 0;
    const ALCDEF_LINE_PART_INDEX_VALUE = 1;

    /**
     * @var mixed[]
     */
    private $alcdefArray = [
        self::FIELD_DATA => [],
    ];

    /**
     * @param string $alcdefString
     */
    public function __construct($alcdefString)
    {
        $this->loadAlcdefArray($alcdefString);
    }

    /**
     * @param string $alcdefString
     */
    private function loadAlcdefArray($alcdefString)
    {
        $alcdefString = $this->normalizeNewlines($alcdefString);
        preg_match(self::PATTERN_ALCDEF_METADATA_DATA, $alcdefString, $alcdefParts);
        $this->loadMetadata($alcdefParts[self::SUBMATCH_INDEX_METADATA]);
        $this->loadData($alcdefParts[self::SUBMATCH_INDEX_DATA]);
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
        $field = explode(self::DELIMITER_ALCDEF_KEY_VALUE, $line, self::PART_COUNT_ALCDEF_LINE);
        $this->alcdefArray[$field[self::ALCDEF_LINE_PART_INDEX_KEY]] = $field[self::ALCDEF_LINE_PART_INDEX_VALUE];
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
        $this->alcdefArray[self::FIELD_DATA][] = $field[self::SUBMATCH_INDEX_METADATA];
    }

    /**
     * @return string
     */
    public function getBibCode()
    {
        return $this->getFieldByName(self::FIELD_BIBCODE);
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    private function getFieldByName($name)
    {
        if (isset($this->alcdefArray[$name])) {
            return $this->alcdefArray[$name];
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getCiBand()
    {
        return $this->getFieldByName(self::FIELD_CIBAND);
    }

    /**
     * @return string
     */
    public function getCiCorrection()
    {
        return $this->getFieldByName(self::FIELD_CICORRECTION);
    }

    /**
     * @return string
     */
    public function getCiTarget()
    {
        return $this->getFieldByName(self::FIELD_CITARGET);
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return $this->getFieldByName(self::FIELD_OBJECTNAME);
    }

    /**
     * @return string
     */
    public function getObjectNumber()
    {
        return $this->getFieldByName(self::FIELD_OBJECTNUMBER);
    }
}
