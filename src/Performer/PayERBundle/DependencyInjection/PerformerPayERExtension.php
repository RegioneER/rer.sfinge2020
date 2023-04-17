<?php

namespace Performer\PayERBundle\DependencyInjection;

use Performer\PayERBundle\Service\EBolloInterface;
use Performer\PayERBundle\Service\PayERInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class PerformerPayERExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $ebolloConfigs = $config['ebollo'];
        $ebollo = $container->getDefinition(EBolloInterface::class);
        $ebollo->replaceArgument(0, $ebolloConfigs[Configuration::CODICE_SERVIZIO]);
        $ebollo->replaceArgument(1, $ebolloConfigs[Configuration::URL_ACQUISTO_CARRELLO_MBD]);
        $ebollo->replaceArgument(2, $ebolloConfigs[Configuration::URL_INVIO_CARRELLO_MBD]);
        $ebollo->replaceArgument(3, $ebolloConfigs[Configuration::URL_ESITO_CARRELLO_MBD]);
        $ebollo->replaceArgument(4, $ebolloConfigs[Configuration::APP_DOMAIN]);

        $payER = $container->getDefinition(PayERInterface::class);
        $payER->replaceArgument(0, $config[Configuration::CODICE_PORTALE]);
        $payER->replaceArgument(1, $config[Configuration::IV]);
        $payER->replaceArgument(2, $config[Configuration::KEY]);
    }
}
