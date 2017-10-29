<?php
namespace dnl_blkv\alcdef;

/**
 */
interface AlcdefDecoder
{
    /**
     * @param string $alcdefString
     *
     * @return AlcdefItem
     */
    public function decode($alcdefString);
}
