<?php

namespace dnl_blkv\alcdef;

/**
 */
class AlcdefItem
{
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
     * @var mixed[]
     */
    private $alcdefArray;

    /**
     * Constructor is private because we only want to instantiate from the factory methods.
     */
    private function __construct()
    {
    }

    /**
     * @param string $alcdef
     *
     * @return static
     */
    public static function createFromAlcdef($alcdef)
    {
        $decoder = new AlcdefDecoder();
        $item = new static();
        $item->alcdefArray = $decoder->decode($alcdef);

        return $item;
    }

    /**
     * @param string $json
     *
     * @return static
     */
    public static function createFromJson($json)
    {
        $item = new static();
        $item->alcdefArray = json_decode($json, true);

        return $item;
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
