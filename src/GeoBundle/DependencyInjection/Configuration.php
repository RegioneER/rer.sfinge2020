<?php

namespace GeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('geo');

        $rootNode
            ->children()
                ->scalarNode('istat_comuni_csv_link')->end()
            ->end() // twitter
        ->end();

        return $treeBuilder;
    }
}
