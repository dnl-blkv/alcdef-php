<?php
namespace dnl_blkv\alcdef;

/**
 */
abstract class AlcdefFormat
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
     * Fields and sub-fields for comparisons.
     */
    const SUBFIELD_COMP_CI = 'CI';
    const SUBFIELD_COMP_DEC = 'DEC';
    const SUBFIELD_COMP_MAG = 'MAG';
    const SUBFIELD_COMP_NAME = 'NAME';
    const SUBFIELD_COMP_RA = 'RA';

    /**
     * Keys found in each data line.
     */
    const DATA_KEY_JD = 'JD';
    const DATA_KEY_MAG = 'MAG';
    const DATA_KEY_MAGERR = 'MAGERR';
    const DATA_KEY_AIRMASS = 'AIRMASS';
}
