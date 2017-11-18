<?php
namespace dnl_blkv\alcdef\test;

use dnl_blkv\alcdef\SimpleAlcdefCodec;
use PHPUnit\Framework\TestCase;

/**
 */
class SimpleAlcdefCodecTest extends TestCase
{
    /**
     * Path to to the ALCDEF and expected JSON.
     */
    const PATH_ZACHIA_ALCDEF = __DIR__ . '/ALCDEF_999_Zachia_20170614_142554_first_item.txt';
    const PATH_ZACHIA_JSON = __DIR__ . '/ALCDEF_999_Zachia_20170614_142554_first_item_json.json';

    /**
     */
    public function testCanDecodeAlcdef()
    {
        $codec = new SimpleAlcdefCodec();
        $alcdefOriginal = file_get_contents(self::PATH_ZACHIA_ALCDEF);
        $jsonActual = json_encode($codec->decode($alcdefOriginal));

        static::assertJsonStringEqualsJsonFile(self::PATH_ZACHIA_JSON, $jsonActual);
    }

    /**
     * @depends testCanDecodeAlcdef
     */
    public function testCanEncodeAlcdef()
    {
        $codec = new SimpleAlcdefCodec();
        $jsonOriginal = file_get_contents(self::PATH_ZACHIA_JSON);
        $alcdefActual = $codec->encode(json_decode($jsonOriginal, true));

        static::assertStringEqualsFile(self::PATH_ZACHIA_ALCDEF, $alcdefActual);
    }
}
