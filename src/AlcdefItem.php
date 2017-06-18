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
    const FIELD_BIBCODE = 'BIBCODE';
    const FIELD_CIBAND = 'CIBAND';
    const FIELD_CICORRECTION = 'CICORRECTION';
    const FIELD_CITARGET = 'CITARGET';
    const FIELD_COMMENT = 'COMMENT';
    const FIELD_COMP = 'COMP';
    const FIELD_CONTACTINFO = 'CONTACTINFO';
    const FIELD_CONTACTNAME = 'CONTACTNAME';
    const FIELD_DATA = 'DATA';
    const FIELD_DELIMITER = 'DELIMITER';
    const FIELD_DIFFERMAGS = 'DIFFERMAGS';
    const FIELD_FILTER = 'FILTER';
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
     * Pattern to match the comparisons.
     */
    const PATTERN_COMPARISON = '@^COMP([A-Z]+)([0-9]+)$@';

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
            self::DATA_KEY_MAGERR => null,
            self::DATA_KEY_AIRMASS => null,
        ];

        if (isset($dataValues[2])) {
            $dataMap[self::DATA_KEY_MAGERR] = $dataValues[2];
        }

        if (isset($dataValues[3])) {
            $dataMap[self::DATA_KEY_AIRMASS] = $dataValues[3];
        }

        $this->alcdefArray[self::FIELD_DATA][] = $dataMap;
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
    public function getComment()
    {
        return $this->getFieldByName(self::FIELD_COMMENT);
    }

    /**
     * @return string
     */
    public function getComp()
    {
        return $this->getFieldByName(self::FIELD_COMP);
    }

    /**
     * @return string
     */
    public function getContactInfo()
    {
        return $this->getFieldByName(self::FIELD_CONTACTINFO);
    }

    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->getFieldByName(self::FIELD_CONTACTNAME);
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->getFieldByName(self::FIELD_DATA);
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->getFieldByName(self::FIELD_DELIMITER);
    }

    /**
     * @return string
     */
    public function getDifferMags()
    {
        return $this->getFieldByName(self::FIELD_DIFFERMAGS);
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->getFieldByName(self::FIELD_FILTER);
    }

    /**
     * @return string
     */
    public function getLtcApp()
    {
        return $this->getFieldByName(self::FIELD_LTCAPP);
    }

    /**
     * @return string
     */
    public function getLtcDays()
    {
        return $this->getFieldByName(self::FIELD_LTCDAYS);
    }

    /**
     * @return string
     */
    public function getLtcType()
    {
        return $this->getFieldByName(self::FIELD_LTCTYPE);
    }

    /**
     * @return string
     */
    public function getMagAdjust()
    {
        return $this->getFieldByName(self::FIELD_MAGADJUST);
    }

    /**
     * @return string
     */
    public function getMagBand()
    {
        return $this->getFieldByName(self::FIELD_MAGBAND);
    }

    /**
     * @return string
     */
    public function getMpcDesig()
    {
        return $this->getFieldByName(self::FIELD_MPCDESIG);
    }

    /**
     * @return string
     */
    public function getObjectDec()
    {
        return $this->getFieldByName(self::FIELD_OBJECTDEC);
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

    /**
     * @return string
     */
    public function getObjectRa()
    {
        return $this->getFieldByName(self::FIELD_OBJECTRA);
    }

    /**
     * @return string
     */
    public function getObservers()
    {
        return $this->getFieldByName(self::FIELD_OBSERVERS);
    }

    /**
     * @return string
     */
    public function getObsLatitude()
    {
        return $this->getFieldByName(self::FIELD_OBSLATITUDE);
    }

    /**
     * @return string
     */
    public function getObsLongitude()
    {
        return $this->getFieldByName(self::FIELD_OBSLONGITUDE);
    }

    /**
     * @return string
     */
    public function getPabb()
    {
        return $this->getFieldByName(self::FIELD_PABB);
    }

    /**
     * @return string
     */
    public function getPabl()
    {
        return $this->getFieldByName(self::FIELD_PABL);
    }

    /**
     * @return string
     */
    public function getPhase()
    {
        return $this->getFieldByName(self::FIELD_PHASE);
    }

    /**
     * @return string
     */
    public function getPublication()
    {
        return $this->getFieldByName(self::FIELD_PUBLICATION);
    }

    /**
     * @return string
     */
    public function getReducedMags()
    {
        return $this->getFieldByName(self::FIELD_REDUCEDMAGS);
    }

    /**
     * @return string
     */
    public function getRevisedData()
    {
        return $this->getFieldByName(self::FIELD_REVISEDDATA);
    }

    /**
     * @return string
     */
    public function getSessionDate()
    {
        return $this->getFieldByName(self::FIELD_SESSIONDATE);
    }

    /**
     * @return string
     */
    public function getSessionTime()
    {
        return $this->getFieldByName(self::FIELD_SESSIONTIME);
    }

    /**
     * @return string
     */
    public function getStandard()
    {
        return $this->getFieldByName(self::FIELD_STANDARD);
    }

    /**
     * @return string
     */
    public function getUCorMag()
    {
        return $this->getFieldByName(self::FIELD_UCORMAG);
    }
}
