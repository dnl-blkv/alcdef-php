<?php

namespace dnl_blkv\alcdef;

/**
 */
class AlcdefItem
{
    /**
     * Option value for json_encode meaning no options.
     */
    const JSON_ENCODE_OPTIONS_NONE = 0;

    /**
     * @var mixed[]
     */
    private $definition;

    /**
     * Constructor is private because we only want to instantiate from the factory methods.
     */
    private function __construct()
    {
    }

    /**
     * @param AlcdefDecoder $alcdefDecoder
     * @param string $alcdef
     *
     * @return static
     */
    public static function createFromAlcdef(AlcdefDecoder $alcdefDecoder, $alcdef)
    {
        $item = new static();
        $item->definition = $alcdefDecoder->decode($alcdef);

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
        $item->definition = json_decode($json, true);

        return $item;
    }

    /**
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = self::JSON_ENCODE_OPTIONS_NONE)
    {
        return json_encode($this->definition, $options);
    }

    /**
     * @param AlcdefEncoder $alcdefEncoder
     *
     * @return string
     */
    public function toAlcdef(AlcdefEncoder $alcdefEncoder)
    {
        return $alcdefEncoder->encode($this);
    }

    /**
     * @return string
     */
    public function getBibCode()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_BIBCODE);
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    private function getFieldByName($name)
    {
        if (isset($this->definition[$name])) {
            return $this->definition[$name];
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getCiBand()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_CIBAND);
    }

    /**
     * @return string
     */
    public function getCiCorrection()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_CICORRECTION);
    }

    /**
     * @return string
     */
    public function getCiTarget()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_CITARGET);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_COMMENT);
    }

    /**
     * @return string
     */
    public function getComp()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_COMP);
    }

    /**
     * @return string
     */
    public function getContactInfo()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_CONTACTINFO);
    }

    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_CONTACTNAME);
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_DATA);
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_DELIMITER);
    }

    /**
     * @return string
     */
    public function getDifferMags()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_DIFFERMAGS);
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_FILTER);
    }

    /**
     * @return string
     */
    public function getLtcApp()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_LTCAPP);
    }

    /**
     * @return string
     */
    public function getLtcDays()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_LTCDAYS);
    }

    /**
     * @return string
     */
    public function getLtcType()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_LTCTYPE);
    }

    /**
     * @return string
     */
    public function getMagAdjust()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_MAGADJUST);
    }

    /**
     * @return string
     */
    public function getMagBand()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_MAGBAND);
    }

    /**
     * @return string
     */
    public function getMpcDesig()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_MPCDESIG);
    }

    /**
     * @return string
     */
    public function getObjectDec()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_OBJECTDEC);
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_OBJECTNAME);
    }

    /**
     * @return string
     */
    public function getObjectNumber()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_OBJECTNUMBER);
    }

    /**
     * @return string
     */
    public function getObjectRa()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_OBJECTRA);
    }

    /**
     * @return string
     */
    public function getObservers()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_OBSERVERS);
    }

    /**
     * @return string
     */
    public function getObsLatitude()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_OBSLATITUDE);
    }

    /**
     * @return string
     */
    public function getObsLongitude()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_OBSLONGITUDE);
    }

    /**
     * @return string
     */
    public function getPabb()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_PABB);
    }

    /**
     * @return string
     */
    public function getPabl()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_PABL);
    }

    /**
     * @return string
     */
    public function getPhase()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_PHASE);
    }

    /**
     * @return string
     */
    public function getPublication()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_PUBLICATION);
    }

    /**
     * @return string
     */
    public function getReducedMags()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_REDUCEDMAGS);
    }

    /**
     * @return string
     */
    public function getRevisedData()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_REVISEDDATA);
    }

    /**
     * @return string
     */
    public function getSessionDate()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_SESSIONDATE);
    }

    /**
     * @return string
     */
    public function getSessionTime()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_SESSIONTIME);
    }

    /**
     * @return string
     */
    public function getStandard()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_STANDARD);
    }

    /**
     * @return string
     */
    public function getUCorMag()
    {
        return $this->getFieldByName(AlcdefFormat::FIELD_UCORMAG);
    }
}
