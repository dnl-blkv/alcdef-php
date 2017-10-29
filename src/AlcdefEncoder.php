<?php
namespace dnl_blkv\alcdef;

/**
 */
interface AlcdefEncoder
{
    /**
     * @param AlcdefItem $alcdefItem
     *
     * @return string
     */
    public function encode(AlcdefItem $alcdefItem);
}
