<?php

namespace OC\BlogBundle\Entity;


use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

abstract class AbstractReplaceable implements ReplaceableInterface
{
    function replaceFrom($object)
    {
        $reflector = new \ReflectionClass($this);

        $class = get_class($this);

        // if the parameter is not an instance of the same class, fail
        if( ! $object instanceof $class ) {
            throw new InvalidArgumentException("Argument must be of instance $class.");
        }

        // get all the public and protected methods
        $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);

        foreach($methods as $method) {
            // all getters except for getId
            $pattern = '~^(?:get(?!id$).*)~i';

            $getter = $method->getName();

            if( 0 === preg_match($pattern, $getter, $matches)) {
                continue;
            }

            // corresponding setter
            $setter = preg_replace('~^get~', 'set', $getter);

            $value = $object->$getter();

            // if value is null, there's no constraint on it, so we skip it
            if(null !== $value) {
                // if there is a value, but no setter, then fail
                if( ! method_exists($object, $setter)) {
                    throw new \Exception("Called a non-existent method $setter on object " . get_class($object));
                }

                $setterMethod = $reflector->getMethod($setter);

                $setterMethod->invoke($this, $value);
            }
        }
    }

}