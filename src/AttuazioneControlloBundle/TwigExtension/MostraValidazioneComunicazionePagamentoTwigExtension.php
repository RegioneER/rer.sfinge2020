<?php

namespace AttuazioneControlloBundle\TwigExtension;

use AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;

class MostraValidazioneComunicazionePagamentoTwigExtension extends \Twig_Extension {

    private $container;

    /**
     * MostraValidazioneComunicazionePagamentoTwigExtension constructor.
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'base_mostra_validazione_integrazione';
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @param string $sezione
     * @param $path
     * @param string $label
     * @param null $proponente
     * @return false|string
     * @throws \Twig\Error\Error
     */
    public function mostraValidazione(ComunicazionePagamento $comunicazionePagamento, string $sezione, $path, string $label, $proponente = null){
        $esito = $this->trovaEsito($comunicazionePagamento, $sezione, $proponente);
        return $this->container->get("templating")->render("IstruttorieBundle:RispostaComunicazionePagamento:mostraValidazioneComunicazionePagamento.html.twig",
            array("esito" => $esito, "path" => $path, "label" => $label));
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @param string $sezione
     * @param null $proponente
     * @return EsitoValidazione
     */
    private function trovaEsito(ComunicazionePagamento $comunicazionePagamento, string $sezione, $proponente = null){
        $bundle = $this->container->get('request_stack')->getCurrentRequest()->getSession()->get("gestore_comunicazione_pagamento_bundle");

        $esito = new EsitoValidazione(true);
        switch ($sezione) {
            case 'nota_risposta':
                $esito = $this->container->get("gestore_comunicazione_pagamento")->getGestore($bundle)->validaNotaRisposta($comunicazionePagamento);
                break;
            case 'documenti_richiesta':
                $esito = $this->container->get("gestore_comunicazione_pagamento")->getGestore($bundle)->validaDocumenti($comunicazionePagamento, $proponente);
                break;
            default:
                # code...
                break;
        }

        return $esito;
    }

    /**
     * @return array|\Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('mostra_validazione_risposta_integrazione', array($this, 'mostraValidazione'),
                array('is_safe' => array('html'))),
        );
    }
}
