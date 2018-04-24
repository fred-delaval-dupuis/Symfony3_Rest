<?php

namespace OC\BlogBundle\DependencyInjection\Compiler;

use OC\BlogBundle\Normalizer\NormalizerChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExceptionNormalizerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if( ! $container->has(NormalizerChain::class)) {
            return;
        }

        $normalizerChainDefinition = $container->findDefinition('oc_blog.normalizer.chain');
        $normalizers =  $container->findTaggedServiceIds('oc_blog.normalizer');

        foreach($normalizers as $id => $tags) {
            $normalizerChainDefinition->addMethodCall('addNormalizer', array(new Reference($id)));
        }
    }
}