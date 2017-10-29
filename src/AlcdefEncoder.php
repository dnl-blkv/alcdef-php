<?php
namespace dnl_blkv\alcdef;

/**
 */
interface AlcdefEncoder
{
    /**
     * @param mixed[] $definition
     *
     * @return string
     */
    public function encode(array $definition);
}
