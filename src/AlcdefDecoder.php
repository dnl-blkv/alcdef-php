<?php
namespace dnl_blkv\alcdef;

/**
 */
interface AlcdefDecoder
{
    /**
     * @param string $alcdefString
     *
     * @return mixed[]
     */
    public function decode($alcdefString);
}
