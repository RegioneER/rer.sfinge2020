<?php

namespace Performer\PayERBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const CODICE_PORTALE = "codice_portale";
    const IV = "iv";
    const KEY = "key";
    const APP_DOMAIN = "app_domain";
    const CODICE_SERVIZIO = "codice_servizio";
    const URL_ACQUISTO_CARRELLO_MBD = "url_acquisto_carrello_mbd";
    const URL_INVIO_CARRELLO_MBD = "url_invio_carrello_mbd";
    const URL_ESITO_CARRELLO_MBD = "url_esito_carrello_mbd";

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('performer_pay_er');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode->children()
            ->scalarNode(self::CODICE_PORTALE)->isRequired()->end()
            ->scalarNode(self::IV)->isRequired()->end()
            ->scalarNode(self::KEY)->isRequired()->end()
            ->arrayNode('ebollo')->isRequired()->children()
                ->scalarNode(self::APP_DOMAIN)->defaultNull()->end()
                ->scalarNode(self::CODICE_SERVIZIO)->isRequired()->end()
                ->scalarNode(self::URL_ACQUISTO_CARRELLO_MBD)->isRequired()->end()
                ->scalarNode(self::URL_INVIO_CARRELLO_MBD)->isRequired()->end()
                ->scalarNode(self::URL_ESITO_CARRELLO_MBD)->isRequired()->end()
            ->end()->end();

        return $treeBuilder;
    }
}
