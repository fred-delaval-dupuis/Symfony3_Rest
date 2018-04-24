<?php

namespace OC\BlogBundle\Normalizer;


class NormalizerChain
{
    private $normalizers;

    public function __construct()
    {
        $this->normalizers = array();
    }

    public function addNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizers[] = $normalizer;
    }

    public function getNormalizers()
    {
        return $this->normalizers;
    }
}