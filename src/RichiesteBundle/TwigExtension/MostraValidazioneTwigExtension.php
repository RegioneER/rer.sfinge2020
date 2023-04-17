<?php

namespace RichiesteBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;
use Twig\TwigTest;
use Twig\Extension\AbstractExtension;
use RichiesteBundle\Entity\Richiesta;

class MostraValidazioneTwigExtension extends AbstractExtension {
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getName() {
        return 'base_mostra_validazione';
    }

    public function mostraValidazione($sezione, $id_proponente = null) {
        $esito = $this->trovaEsito($sezione, $id_proponente, null);

        return $this->container->get("templating")->render("RichiesteBundle:Richieste:mostraValidazione.html.twig", ["esito" => $esito]);
    }
    
    public function mostraValidazioneSezione($sezione, $id_proponente = null) {
        $esito = $this->trovaEsito($sezione, $id_proponente, null);

        return $this->container->get("templating")->render("RichiesteBundle:Richieste:mostraValidazioneSezione.html.twig", ["esito" => $esito]);
    }

    public function mostraValidazioneInLine($sezione, $path, $label, $id_proponente = null, $indice_istanza_fascicolo = null) {
        $esito = $this->trovaEsito($sezione, $id_proponente, $indice_istanza_fascicolo);

        return $this->container->get("templating")->render("RichiesteBundle:Richieste:mostraValidazioneInLine.html.twig", ["esito" => $esito, "path" => $path, "label" => $label]);
    }

    private function trovaEsito($sezione, $id_proponente = null, $indiceIstanzaFascicolo = null) {
        $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
        $em = $this->container->get("doctrine")->getManager();
        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        if (is_null($sezione) || is_null($id_richiesta)) {
            throw new \Exception("Occorre indicare una richiesta e una sezione");
        }

        $esito = new EsitoValidazione(true);
        switch ($sezione) {
            case 'documenti_richiesta':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDocumenti($id_richiesta);
                break;
            case 'documenti_richiesta_dropzone':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDocumentiDropzone($id_richiesta);
                break;
            case 'dati_progetto':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDatiProgetto($id_richiesta);
                break;
            case 'dati_proponenti':
                $esito = $this->container->get("gestore_proponenti")->getGestore()->validaProponenti($id_richiesta);
                break;
            case 'dati_proponente':
                $esito = $this->container->get("gestore_proponenti")->getGestore()->validaProponente($id_proponente);
                break;
            case 'dati_proponenti_pp':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaProponenti($id_richiesta);
                break;
            case 'dati_proponente_pp':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaProponente($id_proponente);
                break;
             case 'dati_protocollo_pp':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDatiProtocollo($id_richiesta);
                break;
            case 'dati_trasferimento_fondo_pp':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDatiTrasferimentoFondo($id_richiesta);
            break;
             case 'dati_pagamenti_pp':
                $esito = $this->container->get("gestore_pagamenti")->getGestore($richiesta->getProcedura())->validaPagamenti($richiesta);
                break;
            case 'documenti_proponente':
                $esito = $this->container->get("gestore_proponenti")->getGestore()->validaDocumentiProponente($id_proponente);
                break;
            case 'piano_costi':
                $esito = $this->container->get("gestore_piano_costo")->getGestore()->validaPianoDeiCosti($id_richiesta);
                break;
            case 'totale_piano_costi':
                $esito = $this->container->get("gestore_piano_costo")->getGestore()->validaTotalePianoDeiCosti($richiesta);
                break;
            case 'dati_marca_da_bollo':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDatiMarcaDaBollo($richiesta);
                break;
            case 'dati_generali':
                $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDatiGenerali($richiesta);
                break;
            case 'dati_questionario':
                $questionarioValido = $this->container->get("fascicolo.istanza")->validaIstanzaPagina($indiceIstanzaFascicolo);
                $esito = new EsitoValidazione();
                $esito->setEsito($questionarioValido->getEsito());
                if (!$questionarioValido->getEsito()) {
                    $esito->addMessaggioSezione("Il questionario non è completo in tutte le sue parti");
                }
                break;
            case 'stato_avanzamento':
                $esito = $this->container->get("gestore_fase_procedurale")->getGestore()->validaFaseProceduraleRichiesta($id_richiesta);
                break;
            case 'priorita':
                $esito = $this->container->get("gestore_priorita")->getGestore()->validaPriorita($id_richiesta);
                break;
            case 'dati_fornitori':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaFornitori($id_richiesta);
                break;
            case 'dati_interventi':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaInterventi($id_richiesta);
                break;    
            case 'dati_interventi_sede':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaInterventiPianoCosti($id_richiesta);
                break;    
            case 'valida_maggiorazione':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaMaggiorazione($id_richiesta);
                break;
            case 'valida_premialita':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaPremialita($id_richiesta);
                break;
            case 'valida_irap':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaIrap($id_richiesta);
                break;
            case 'valida_referenti':
                $esito = $this->container->get("gestore_proponenti")->getGestore()->validaReferenti($id_richiesta);
                break;
            case 'valida_sedi':
                $esito = $this->container->get("gestore_proponenti")->getGestore()->validaSedi($id_richiesta);
                break;
            case 'valida_impegni':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaImpegni($id_richiesta);
                break;
            case 'autodichiarazioni_autorizzazioni_richiesta':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaAutodicharazioni($id_richiesta);
                break;
            case 'dati_cup':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaDatiCup($id_richiesta);
                break;
            case 'monitoraggio_indicatori':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaMonitoraggioIndicatori($id_richiesta);
                break;
            case 'risorse_progetto':
                $esito = $this->container->get("gestore_risorse")->getGestore()->validaRisorseProgetto($id_richiesta);
                break;
            case 'monitoraggio_impegni':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaImpegni($id_richiesta);
                break;
            case 'indicatori_output_richiesta':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaIndicatoriOutput($richiesta);
                break;
            case 'gestione_procedura_aggiudicazione_pp':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaProceduraAggiudicazione($richiesta);
                break;
            case 'iter_progetto':
                $esito = $this->container->get("monitoraggio.iter_progetto")->getIstanza($richiesta)->validaInPresentazioneDomanda();
                break;
            case 'programma_richiesta':
                $esito = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaProgramma($richiesta);
                break;
            case 'documenti_programma':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDocumentiProgramma($id_richiesta);
                break;
            case 'obiettivi_realizzativi':
                $esito = $this->container->get("gestore_obiettivi_realizzativi")->getGestore($richiesta)->valida();
                break;
            case 'dichiarazioni_dsnh':
                $esito = $this->container->get("gestore_richieste")->getGestore()->validaDnsh();
                break;
            case 'dati_ambiti_prioritari_s3':
                $esito = $this->container->get("gestore_ambiti_tematici_s3")->getGestore()->validaAmbitiTematiciS3($id_richiesta);
                break;
            default:
                // code...
                break;
        }

        return $esito;
    }

    public function isProponenteCompleto($id_proponente) {
        $esito = $this->container->get("gestore_proponenti")->getGestore()->validaProponente($id_proponente);
        return $esito->getEsito();
    }

    public function isProponenteCompletoPP($id_proponente) {
        $esito = $this->container->get("gestore_richieste")->getGestore()->validaProponente($id_proponente);
        return $esito->getEsito();
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('mostra_validazione', [$this, 'mostraValidazione'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('mostra_validazione_in_line', [$this, 'mostraValidazioneInLine'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('mostra_validazione_sezione', [$this, 'mostraValidazioneSezione'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('proponente_completo', [$this, 'isProponenteCompleto'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('proponente_completo_pp', [$this, 'isProponenteCompletoPP'], ['is_safe' => ['html']]),
        ];
    }

    public function getTests() {
        return [
            new TwigTest('disabilitata', [$this,'isRichiestaDisabilitata']),
        ];
    }

    public function isRichiestaDisabilitata(Richiesta $richiesta): bool {
        /** @var GestoreRichiestaService $gestoreRichiesteFactory */
        $gestoreRichiesteFactory = $this->container->get('gestore_richieste');
        $gestore = $gestoreRichiesteFactory->getGestore($richiesta->getProcedura());
        $disabilitata = (bool) $gestore->isRichiestaDisabilitata();

        return $disabilitata;
    }
}
