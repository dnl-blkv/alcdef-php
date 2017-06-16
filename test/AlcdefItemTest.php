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
    const PATH_ALCDEF_ZACHIA = __DIR__ . '/ALCDEF_999_Zachia_20170614_142554_first_item.txt';

    /**
     * Object Name for test.
     */
    const ALCDEF_OBJECT_NAME_ZACHIA = 'Zachia';

    /**
     */
    public function testCanCreateFromString()
    {
        $string = file_get_contents(self::PATH_ALCDEF_ZACHIA);
        $item = new AlcdefItem($string);
        static::assertEquals(self::ALCDEF_OBJECT_NAME_ZACHIA, $item->getObjectName());
    }
}
