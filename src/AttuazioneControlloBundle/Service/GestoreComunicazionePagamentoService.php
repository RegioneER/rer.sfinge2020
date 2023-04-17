<?php

namespace AttuazioneControlloBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreComunicazionePagamentoService
{
    protected $container;

    /**
     * GestoreComunicazionePagamentoService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|null $bundle
     * @return GestoreComunicazionePagamentoBase|object
     */
    public function getGestore(string $bundle = null){

        $nomeClasse = is_null($bundle) ? "AttuazioneControlloBundle" : $bundle;
        
        $nomeClasse .= "\GestoriComunicazionePagamento\GestoreComunicazionePagamento";
        
        try {
            $gestoreBandoReflection = new \ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (\ReflectionException $ex){
            
        }

        return new GestoreComunicazionePagamentoBase($this->container);
    }
}
