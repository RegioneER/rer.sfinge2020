<?php

namespace AttuazioneControlloBundle\TwigExtension;

use BaseBundle\Controller\BaseController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Service\IGestoreIterProgetto;

class MostraValidazioneAttuazioneTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getName() {
        return 'mostra_validazione_attuazione';
    }

    public function mostraValidazioneAttuazione($sezione, $path, $label, $pagamento, $messaggiSezione = true) {

        $esito = $this->trovaEsito($sezione, $pagamento);

        return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneAttuazione.html.twig", array("esito" => $esito, "path" => $path, "label" => $label, "messaggiSezione" => $messaggiSezione));
    }

    public function mostraValidazioneRicercatore($sezione, $ricercatore, $returnEsito = false) {

        $esito = $this->trovaEsitoRicercatore($sezione, $ricercatore);
        if ($returnEsito) {
            return $esito;
        }
        return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneAttuazione.html.twig", array("esito" => $esito, "path" => null, "label" => null));
    }

    public function mostraValidazioneInLineAttuazione($sezione, $path, $label, $pagamento = null, $indice_istanza_fascicolo = null) {

        $esito = $this->trovaEsito($sezione, $pagamento, $indice_istanza_fascicolo);

        return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneInLineAttuazione.html.twig", array("esito" => $esito, "path" => $path, "label" => $label));
    }

    private function trovaEsitoRicercatore($sezione, $ricercatore) {

        $esito = new EsitoValidazione(true);
        $gestore_pagamenti = $this->container->get("gestore_pagamenti")->getGestore($ricercatore->getPagamento()->getProcedura());
        switch ($sezione) {

            case 'valida_documenti_ricercatore':
                $esito = $gestore_pagamenti->validaDocumentiRicercatore($ricercatore);
                break;
        }

        return $esito;
    }

    private function trovaEsito($sezione, $pagamento, $indiceIstanzaFascicolo = null) {

        $esito = new EsitoValidazione(true);
        $gestore_pagamenti = $this->container->get("gestore_pagamenti")->getGestore($pagamento->getProcedura());/** @var \AttuazioneControlloBundle\Service\IGestorePagamenti $gestore_pagamenti */
        $gestore_relazione = $this->container->get("gestore_relazione_tecnica")->getGestore($pagamento->getProcedura());
        $gestore_giustificativi = $this->container->get("gestore_giustificativi")->getGestore($pagamento->getProcedura());
        switch ($sezione) {

            case 'dati_generali_pagamento':
                $esito = $gestore_pagamenti->validaDatiGenerali($pagamento);
                break;
            case 'documenti_pagamento':
                $esito = $gestore_pagamenti->validaDocumenti($pagamento);
                break;
            case 'giustificativi_pagamento':
                $esito = $gestore_pagamenti->validaGiustificativi($pagamento);
                break;
            case 'giustificativi_pagamento_at':
                $esito = $gestore_pagamenti->validaGiustificativiPP($pagamento);
                break;
            case 'dati_bancari_pagamento':
                $esito = $gestore_pagamenti->validaDatiBancari($pagamento);
                break;
            case 'dati_questionario':
                if (!is_null($indiceIstanzaFascicolo)) {
                    $questionarioValido = $this->container->get("fascicolo.istanza")->validaIstanzaPagina($indiceIstanzaFascicolo);
                    $esito = new EsitoValidazione();
                    $esito->setEsito($questionarioValido->getEsito());
                    if (!$questionarioValido->getEsito()) {
                        $esito->addMessaggio("Il questionario non è completo in tutte le sue parti");
                        $esito->addMessaggioSezione("Il questionario non è completo in tutte le sue parti");
                    }
                } else {
                    $esito = new EsitoValidazione(false);
                    $esito->addMessaggio("Il questionario non è completo in tutte le sue parti");
                    $esito->addMessaggioSezione("Il questionario non è completo in tutte le sue parti");
                }
                break;
            case 'relazione_tecnica':
                $esito = $gestore_relazione->validaRelazioneTecnica($pagamento);
                break;
            case 'autore_relazione_tecnica':
                $esito = $gestore_relazione->validaAutore($pagamento);
                break;
            case 'or_relazione_tecnica':
                $esito = $gestore_relazione->validaOr($pagamento);
                break;
            case 'collaborazioni_laboratori_relazione_tecnica':
                $esito = $gestore_relazione->validaCollaborazioniLaboratori($pagamento);
                break;
            case 'consulenze_specializzate_relazione_tecnica':
                $esito = $gestore_relazione->validaConsulenzeSpecialistiche($pagamento);
                break;
            case 'attrezzature_strumentazioni_relazione_tecnica':
                $esito = $gestore_relazione->validaAttrezzatureStrumentazioni($pagamento);
                break;
            case 'brevetti':
                $esito = $gestore_relazione->validaBrevetti($pagamento);
                break;
            case 'documenti_personale_pagamento':
                $esito = $gestore_giustificativi->validaDocumentiPersonale($pagamento);
                break;
            case 'elenco_ricercatori':
                $esito = $gestore_pagamenti->validaRicercatori($pagamento);
                break;
            case 'date_progetto':
                $esito = $gestore_pagamenti->validaDateProgetto($pagamento);
                break;
            case 'gestione_durc':
                $esito = $gestore_pagamenti->validaDurc($pagamento);
                break;
            case 'avanzamento':
                $esito = $gestore_pagamenti->validaGiustificativi($pagamento);
                break;
            case 'attivita_realizzate':
                $esito = $gestore_relazione->validaAttivitaRealizzate($pagamento);
                break;
            case 'or_5_generale':
                $esito = $gestore_relazione->validaDiffusioneRisultatiGenerale($pagamento);
                break;
            case 'or_5_attivita':
                $esito = $gestore_relazione->validaDiffusioneRisultatiElencoAttivita($pagamento);
                break;
            case 'or_5_materiale':
                $esito = $gestore_relazione->validaDiffusioneRisultatiElencoMateriale($pagamento);
                break;
            case 'avanzamento_multi':
                $esito = $gestore_giustificativi->validaRendiconto($pagamento);
                break;
            case 'documenti_dichiarazioni_rendicontazione':
                $esito = $gestore_pagamenti->validaDocumentiDichiarazioniRendicontazione($pagamento);
                break;
            case 'documenti_generali_pagamento':
                $esito = $gestore_pagamenti->validaDocumentiGeneraliPagamento($pagamento);
                break;
            case 'gestione_antimafia':
                $esito = $gestore_pagamenti->validaAntimafia($pagamento);
                break;
            case 'gestione_autodichiarazioni_autorizzazioni':
                $esito = $gestore_pagamenti->validaAutodichiarazioniAutorizzazioni($pagamento);
                break;
            case 'incremento_occupazionale':
                $gestore_incremento_occupazionale = $this->container->get("gestore_incremento_occupazionale")->getGestore($pagamento->getProcedura());
                $esito = $gestore_incremento_occupazionale->validaIncrementoOccupazionale($pagamento);
                break;
            case 'relazione_finale_a_saldo':
                $esito = $gestore_pagamenti->validaRelazioneFinale($pagamento);
                break;
            case 'monitoraggio_indicatori':
                $esito = $gestore_pagamenti->validaMonitoraggioIndicatori($pagamento);
                break;
            case 'monitoraggio_fasi_procedurali':
                $esito = $gestore_pagamenti->validaMonitoraggioFasiProcedurali($pagamento);
                break;
            case 'monitoraggio_impegni':
                $esito = $gestore_pagamenti->validaImpegni($pagamento);
                break;
            case 'monitoraggio_procedura_aggiudicazione':
                $esito = $gestore_pagamenti->validaProceduraAggiudicazione($pagamento);
                break;
            case 'documenti_anticipo_pagamento':
                $esito = $gestore_pagamenti->validaDocumentiAnticipoPagamento($pagamento);
                break;
            case 'gestione_contratti':
                $esito = $gestore_pagamenti->validaContratti($pagamento);
                break;
            case 'gestione_documenti_dropzone':
                $esito = $gestore_pagamenti->validaDocumentiDropzone($pagamento);
                break;
            default:
                # code...
                break;
        }

        return $esito;
    }

    public function mostraValidazioneDocumentiPagamentoProponente($sezione, $path, $label, $pagamento, $proponente) {
        $gestore_pagamenti = $this->container->get("gestore_pagamenti")->getGestore($pagamento->getProcedura());
        $esito = $gestore_pagamenti->validaDocumentoGeneralePagamento($pagamento, $proponente);

        return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneAttuazione.html.twig", array("esito" => $esito, "path" => $path, "label" => $label));
    }

    public function hasIterProgettoVisibili(Richiesta $richiesta): bool {
        /** @var IGestoreIterProgetto $iterService */
        $iterService = $this->container->get('monitoraggio.iter_progetto')->getIstanza($richiesta);

        return $iterService->hasSezioneRichiestaVisibile();
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('mostra_validazione_attuazione', array($this, 'mostraValidazioneAttuazione'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_validazione_in_line_attuazione', array($this, 'mostraValidazioneInLineAttuazione'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_validazione_ricercatore', array($this, 'mostraValidazioneRicercatore'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_validazione_documenti_generali_proponente', array($this, 'mostraValidazioneDocumentiPagamentoProponente'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('hasIterProgettoVisibili', array($this, 'hasIterProgettoVisibili'), array('is_safe' => array('html'))),
        );
    }

}
