<?php

namespace dnl_blkv\alcdef\test;

use dnl_blkv\alcdef\AlcdefItem;
use PHPUnit\Framework\TestCase;

/**
 */
class AlcdefItemTest extends TestCase
{
    /**
     * Path to the very first ALCDEF file.
     */
    const PATH_ALCDEF = __DIR__ . '/ALCDEF_999_Zachia_20170614_142554_first_item.txt';

    /**
     * Values of the fields to test.
     */
    const ALCDEF_ZACHIA_BIBCODE = null;
    const ALCDEF_ZACHIA_CIBAND = 'NONE';
    const ALCDEF_ZACHIA_CICORRECTION = 'FALSE';
    const ALCDEF_ZACHIA_CITARGET = '+0.000';
    const ALCDEF_ZACHIA_OBJECTNAME = 'Zachia';
    const ALCDEF_ZACHIA_OBJECTNUMBER = 999;

    /**
     */
    public function testCanCreateFromString()
    {
        $item = new AlcdefItem(file_get_contents(self::PATH_ALCDEF));

        static::assertEquals(self::ALCDEF_ZACHIA_CIBAND, $item->getCiBand());
        static::assertEquals(self::ALCDEF_ZACHIA_CICORRECTION, $item->getCiCorrection());
        static::assertEquals(self::ALCDEF_ZACHIA_CITARGET, $item->getCiTarget());
        static::assertEquals(self::ALCDEF_ZACHIA_BIBCODE, $item->getBibCode());
        static::assertEquals(self::ALCDEF_ZACHIA_OBJECTNAME, $item->getObjectName());
        static::assertEquals(self::ALCDEF_ZACHIA_OBJECTNUMBER, $item->getObjectNumber());
    }
}
