<?php

namespace dnl_blkv\alcdef\test;

use dnl_blkv\alcdef\AlcdefDecoder;
use PHPUnit\Framework\TestCase;

/**
 */
class AlcdefDecoderTest extends TestCase
{
    /**
     * Path to the very first ALCDEF file.
     */
    const PATH_ALCDEF = __DIR__ . '/ALCDEF_999_Zachia_20170614_142554_first_item.txt';

    /**
     */
    public function testCanCreateFromString()
    {
        $alcdefArrayExpected = [
            'REVISEDDATA' => false,
            'LCBLOCKID' => 19597,
            'OBJECTNUMBER' => 999,
            'OBJECTNAME' => 'Zachia',
            'MPCDESIG' => '1923 NW',
            'SESSIONDATE' => '1999-09-04',
            'SESSIONTIME' => '06:00:00',
            'CONTACTNAME' => 'B. D. Warner',
            'CONTACTINFO' => 'brian@MinorPlanetObserver.com',
            'OBSERVERS' => 'Warner, B.D.',
            'OBSLONGITUDE' => +0.000000,
            'OBSLATITUDE' => +0.000000,
            'OBJECTRA' => null,
            'OBJECTDEC' => null,
            'PHASE' => +13.54,
            'PABL' => +323.5,
            'PABB' => +12.9,
            'FILTER' => 'C',
            'MAGBAND' => 'R',
            'CICORRECTION' => false,
            'CIBAND' => 'NONE',
            'CITARGET' => +0.000,
            'DIFFERMAGS' => false,
            'MAGADJUST' => 0.0,
            'STANDARD' => 'INTERNAL',
            'LTCAPP' => 'NONE',
            'LTCDAYS' => -0.006465,
            'LTCTYPE' => 'NONE',
            'REDUCEDMAGS' => 'NONE',
            'UCORMAG' => -1.8189,
            'PUBLICATION' => null,
            'BIBCODE' => null,
            'DELIMITER' => 'PIPE',
            'COMP' => [
                2 => [
                    'NAME' => '210040.20 +001658.7',
                    'RA' => null,
                    'DEC' => null,
                    'MAG' => 13.37,
                    'CI' => 0.0,
                ],
                3 => [
                    'NAME' => '210007.89 +002249.0',
                    'RA' => null,
                    'DEC' => null,
                    'MAG' => 13.074,
                    'CI' => 0.0,
                ],
                4 => [
                    'NAME' => '210022.61 +002459.8',
                    'RA' => null,
                    'DEC' => null,
                    'MAG' => 12.977,
                    'CI' => 0.0,
                ],
            ],
            'DATA' => [
                ['JD' => 2451425.613960, 'MAG' => 13.192, 'MAGERR' => 0.008, 'AIRMASS' => 1.583],
                ['JD' => 2451425.618090, 'MAG' => 13.197, 'MAGERR' => 0.008, 'AIRMASS' => 1.555],
                ['JD' => 2451425.622220, 'MAG' => 13.198, 'MAGERR' => 0.008, 'AIRMASS' => 1.529],
                ['JD' => 2451425.626350, 'MAG' => 13.207, 'MAGERR' => 0.009, 'AIRMASS' => 1.504],
                ['JD' => 2451425.664870, 'MAG' => 13.286, 'MAGERR' => 0.008, 'AIRMASS' => 1.348],
                ['JD' => 2451425.668210, 'MAG' => 13.312, 'MAGERR' => 0.008, 'AIRMASS' => 1.339],
                ['JD' => 2451425.671530, 'MAG' => 13.311, 'MAGERR' => 0.008, 'AIRMASS' => 1.332],
                ['JD' => 2451425.674860, 'MAG' => 13.299, 'MAGERR' => 0.008, 'AIRMASS' => 1.325],
                ['JD' => 2451425.678190, 'MAG' => 13.313, 'MAGERR' => 0.008, 'AIRMASS' => 1.318],
                ['JD' => 2451425.681530, 'MAG' => 13.326, 'MAGERR' => 0.009, 'AIRMASS' => 1.312],
                ['JD' => 2451425.684850, 'MAG' => 13.329, 'MAGERR' => 0.008, 'AIRMASS' => 1.307],
                ['JD' => 2451425.688180, 'MAG' => 13.333, 'MAGERR' => 0.009, 'AIRMASS' => 1.303],
                ['JD' => 2451425.691520, 'MAG' => 13.356, 'MAGERR' => 0.009, 'AIRMASS' => 1.299],
                ['JD' => 2451425.694850, 'MAG' => 13.356, 'MAGERR' => 0.009, 'AIRMASS' => 1.295],
                ['JD' => 2451425.698170, 'MAG' => 13.359, 'MAGERR' => 0.009, 'AIRMASS' => 1.292],
                ['JD' => 2451425.701510, 'MAG' => 13.356, 'MAGERR' => 0.010, 'AIRMASS' => 1.290],
                ['JD' => 2451425.704840, 'MAG' => 13.354, 'MAGERR' => 0.011, 'AIRMASS' => 1.289],
                ['JD' => 2451425.708170, 'MAG' => 13.373, 'MAGERR' => 0.011, 'AIRMASS' => 1.288],
                ['JD' => 2451425.711490, 'MAG' => 13.366, 'MAGERR' => 0.011, 'AIRMASS' => 1.287],
                ['JD' => 2451425.714830, 'MAG' => 13.371, 'MAGERR' => 0.011, 'AIRMASS' => 1.287],
                ['JD' => 2451425.718160, 'MAG' => 13.379, 'MAGERR' => 0.010, 'AIRMASS' => 1.288],
                ['JD' => 2451425.721520, 'MAG' => 13.369, 'MAGERR' => 0.014, 'AIRMASS' => 1.289],
                ['JD' => 2451425.724850, 'MAG' => 13.391, 'MAGERR' => 0.010, 'AIRMASS' => 1.291],
                ['JD' => 2451425.728170, 'MAG' => 13.378, 'MAGERR' => 0.010, 'AIRMASS' => 1.293],
                ['JD' => 2451425.734840, 'MAG' => 13.376, 'MAGERR' => 0.010, 'AIRMASS' => 1.299],
                ['JD' => 2451425.741490, 'MAG' => 13.374, 'MAGERR' => 0.011, 'AIRMASS' => 1.308],
                ['JD' => 2451425.744830, 'MAG' => 13.376, 'MAGERR' => 0.042, 'AIRMASS' => 1.314],
                ['JD' => 2451425.798100, 'MAG' => 13.318, 'MAGERR' => 0.017, 'AIRMASS' => 1.497],
                ['JD' => 2451425.801440, 'MAG' => 13.332, 'MAGERR' => 0.010, 'AIRMASS' => 1.517],
                ['JD' => 2451425.808090, 'MAG' => 13.291, 'MAGERR' => 0.021, 'AIRMASS' => 1.559],
                ['JD' => 2451425.811420, 'MAG' => 13.310, 'MAGERR' => 0.011, 'AIRMASS' => 1.582],
                ['JD' => 2451425.814760, 'MAG' => 13.292, 'MAGERR' => 0.009, 'AIRMASS' => 1.606],
                ['JD' => 2451425.821410, 'MAG' => 13.278, 'MAGERR' => 0.012, 'AIRMASS' => 1.659],
                ['JD' => 2451425.824750, 'MAG' => 13.289, 'MAGERR' => 0.009, 'AIRMASS' => 1.688],
                ['JD' => 2451425.828090, 'MAG' => 13.274, 'MAGERR' => 0.009, 'AIRMASS' => 1.719],
            ],
        ];
        $decoder = new AlcdefDecoder();
        $alcdefArrayActual = $decoder->decode(file_get_contents(self::PATH_ALCDEF));

        static::assertEquals($alcdefArrayExpected, $alcdefArrayActual);
    }
}
