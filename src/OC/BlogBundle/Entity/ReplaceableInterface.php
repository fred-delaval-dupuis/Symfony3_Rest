<?php

namespace OC\BlogBundle\Entity;


interface ReplaceableInterface
{
    function replaceFrom($object);
}