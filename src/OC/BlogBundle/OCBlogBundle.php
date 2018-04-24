<?php

namespace OC\BlogBundle;

use OC\BlogBundle\DependencyInjection\Compiler\ExceptionNormalizerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OCBlogBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ExceptionNormalizerPass());
    }

}
