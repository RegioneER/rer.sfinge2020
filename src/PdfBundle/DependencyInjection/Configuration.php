<?php

namespace PdfBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('pdf')->children();

        $rootNode
            ->scalarNode('webDir')->defaultValue('%kernel.root_dir%/../web')->end()
            ->arrayNode('defaultOptions')
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
