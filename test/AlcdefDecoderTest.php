<?php

namespace dnl_blkv\alcdef\test;

use dnl_blkv\alcdef\AlcdefDecoder;
use PHPUnit\Framework\TestCase;

/**
 */
class AlcdefDecoderTest extends TestCase
{
    /**
     * Path to to the ALCDEF and expected JSON.
     */
    const PATH_ALCDEF_ORIGINAL = __DIR__ . '/ALCDEF_999_Zachia_20170614_142554_first_item.txt';
    const PATH_JSON_EXPECTED = __DIR__ . '/ALCDEF_999_Zachia_20170614_142554_first_item_json_expected.json';

    /**
     */
    public function testCanCreateFromString()
    {
        $alcdefArrayExpected = json_decode(file_get_contents(self::PATH_JSON_EXPECTED), true);
        $decoder = new AlcdefDecoder();
        $alcdefArrayActual = $decoder->decode(file_get_contents(self::PATH_ALCDEF_ORIGINAL));

        static::assertEquals($alcdefArrayExpected, $alcdefArrayActual);
    }
}
