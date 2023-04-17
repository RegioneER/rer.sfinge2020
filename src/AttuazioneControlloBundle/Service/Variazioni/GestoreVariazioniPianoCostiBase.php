<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto;
use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\Collection;
use PaginaBundle\Services\Pagina;
use PdfBundle\Wrapper\PdfWrapper;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GestoreVariazioniPianoCostiBase extends GestoreVariazioniSpecifica implements IGestoreVariazioniPianoCosti {

    /** @var VariazionePianoCosti $variazione */
    protected $variazione;

    public function __construct(VariazionePianoCosti $variazione, IGestoreVariazioni $base, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->base = $base;
        $this->container = $container;
    }

    public function dettaglioVariazione(): Response {
        $richiesta = $this->variazione->getRichiesta();

        $dati = [
            "variazione" => $this->variazione,
            "avanzamenti" => $this->gestioneBarraAvanzamento(),
            "annualita" => $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId()),
        ];
        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_variazioni", ["id_richiesta" => $richiesta->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio variazione");

        return $this->render("AttuazioneControlloBundle:Variazioni:dettaglioVariazionePianoCosti.html.twig", $dati);
    }

    public function pianoCostiVariazione($annualita, Proponente $proponente = null): Response {
        if (is_null($proponente)) {
            $voci_piano_costo = $this->variazione->getAttuazioneControlloRichiesta()->getRichiesta()->getVociPianoCosto();
        } else {
            $voci_piano_costo = $proponente->getVociPianoCosto();
        }

        $this->generaVariazioniVociPianoCosto($voci_piano_costo, $annualita);

        $variazione_fantasma = new \stdClass();
        $variazione_fantasma->voci_piano_costo = $this->variazione->getVociPianoCostoProponente($proponente);

        $opzioni['annualita'] = $annualita;
        $opzioni['url_indietro'] = $this->generateUrl("dettaglio_variazione", ['id_variazione' => $this->variazione->getId()]);
        $opzioni["disabled"] = $this->variazione->isRichiestaDisabilitata();

        $form = $this->createForm(\AttuazioneControlloBundle\Form\VariazionePianoCostiBaseType::class, $variazione_fantasma, $opzioni);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            foreach ($form->get("voci_piano_costo")->all() as $form_voce) {
                $importo = $form_voce->getData()->getVocePianoCosto()->{"getImportoAnno" . $annualita}();
                $importo_variazione = $form_voce->getData()->{"getImportoVariazioneAnno" . $annualita}();

                if (is_array($annualita) && count($annualita) > 1) {
                    if (0 == $importo && (0 != $importo_variazione && !is_null($importo_variazione)) && !$this->isTraslazioneAnno($form_voce)) {
                        $form_voce->addError(new \Symfony\Component\Form\FormError("In caso di traslazione totali da annualità diverse non è possibile superare il costo presentato, verificare la voce: " . $form_voce->getData()->getVocePianoCosto()->getPianoCosto()->getTitolo()));
                        //break;
                    }
                }

                if (0 == $importo_variazione || is_null($importo_variazione)) {
                    $form_voce->getData()->{"setImportoVariazioneAnno" . $annualita}(0);
                }
            }
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl("dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        //aggiungo il titolo della pagina e le info della breadcrumb
        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_variazioni", ["id_richiesta" => $this->variazione->getRichiesta()->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio variazione", $this->generateUrl("dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Piano costi");

        $dati['onKeyUp'] = 'calcolaTotaleSezione';
        $dati["form"] = $form->createView();
        $dati["annualita"] = $opzioni['annualita'];
        $dati["variazione"] = $this->variazione;
        $dati["menu"] = "piano_costi";
        return $this->render("AttuazioneControlloBundle:Variazioni:pianoCosti.html.twig", $dati);
    }

    protected function generaVariazioniVociPianoCosto($voci_piano_costo, $annualita) {
        $ultima_variazione = $this->variazione->getAttuazioneControlloRichiesta()->getUltimaVariazioneApprovata();

        $variazioni_voci_piano_costo = [];
        foreach ($voci_piano_costo as $voce_piano_costo) {
            $variazione_voce = $this->getEm()->getRepository("AttuazioneControlloBundle:VariazioneVocePianoCosto")->findOneBy(["voce_piano_costo" => $voce_piano_costo, "variazione" => $this->variazione]);

            if (is_null($variazione_voce)) {
                $variazione_voce = new \AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto();

                $variazione_voce->setVocePianoCosto($voce_piano_costo);
                $variazione_voce->setVariazione($this->variazione);
                $this->variazione->addVocePianoCosto($variazione_voce);
            }

            if (is_null($variazione_voce->{"getImportoVariazioneAnno" . $annualita}())) {
                if (!is_null($ultima_variazione)) {
                    $variazione_voce_approvata = $ultima_variazione->getVariazioneVocePianoCosto($voce_piano_costo);
                    $importo = $variazione_voce_approvata->{"getImportoApprovatoAnno" . $annualita}();
                } else {
                    $importo = $voce_piano_costo->getIstruttoria()->{"getImportoAmmissibileAnno" . $annualita}();
                }

                $variazione_voce->{"setImportoVariazioneAnno" . $annualita}($importo);
            }

            $variazioni_voci_piano_costo[] = $variazione_voce;
        }

        return $variazioni_voci_piano_costo;
    }

    protected function isTraslazioneAnno($form_voce): bool {
        $importo = $form_voce->getData()->getVocePianoCosto()->getTotale();
        $importo_variazione = $form_voce->getData()->sommaImporti();

        return round($importo, 2) >= round($importo_variazione, 2);
    }

    protected function generaPdf(bool $facsimile = true): string {
        return $this->generaPdfVariazione("@AttuazioneControllo/Pdf/pdf_variazione.html.twig", [], $facsimile);
    }

    protected function generaPdfVariazione(string $twig, array $datiAggiuntivi = [], bool $facsimile = true): string {
        if ($this->variazione->isRichiestaDisabilitata()) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }

        $richiesta = $this->variazione->getRichiesta();
        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());
        $dati = [
            "variazione" => $this->variazione,
            "firmatario" => $this->variazione->getFirmatario(),
            "procedura" => $richiesta->getProcedura(),
            "capofila" => $richiesta->getMandatario()->getSoggetto(),
            "annualita" => $annualita,
            "pianoCosti" => $this->generaArrayVistaPianoCosto($annualita),
            'facsimile' => $facsimile,
        ];
        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
        $dati = \array_merge_recursive($dati, $datiAggiuntivi);

        /** @var PdfWrapper $pdfService */
        $pdfService = $this->container->get("pdf");
        $pdfService->load($twig, $dati);
        $pdf = $pdfService->binaryData();

        return $pdf;
    }

    /**
     * Rende un array multi livello in cui la prima chiave è la stringa del titolo delle voci spesa distinct per il piano costo relativa alla procedura
     * ogni elemento dell'array è un ulteriore array con chiave il titolo della sezione e il valore è l'importo
     *
     * array["titolo voce spesa"]["titolo sezione"] => importo
     *
     * @throws SfingeException
     * @param mixed $annualita
     */
    protected function generaArrayVistaPianoCosto($annualita): array {
        //formo l'array
        $risultato = [];

        foreach ($annualita as $keyAnnualita => $anno) {
            $metodo = "getImportoAnno" . $keyAnnualita;
            $metodoVariazione = "getImportoVariazioneAnno" . $keyAnnualita;
            $metodoNotaAnno = "getNotaAnno" . $keyAnnualita;
            $voci_piano_costo = $this->variazione->getVociPianoCosto();
            $voci_piano_costo_order = $this->ordina($voci_piano_costo, 'VocePianoCosto', 'Ordinamento');
            foreach ($voci_piano_costo_order as $variazioneVocePianoCosto) {
                $sezione = $variazioneVocePianoCosto->getVocePianoCosto()->getPianoCosto()->getSezionePianoCosto()->getTitoloSezione();
                $risultato[$keyAnnualita][$sezione][] = [
                    'TitoloVoceCosto' => $variazioneVocePianoCosto->getVocePianoCosto()->getPianoCosto()->getTitolo(),
                    'CodiceVoceCosto' => $variazioneVocePianoCosto->getVocePianoCosto()->getPianoCosto()->getCodice(),
                    'ImportoVoceCosto' => $variazioneVocePianoCosto->getVocePianoCosto()->getImportoAmmesso($keyAnnualita),
                    'ImportoVariazioneVoceCosto' => $variazioneVocePianoCosto->$metodoVariazione(),
                    'NotaVoceCosto' => $variazioneVocePianoCosto->$metodoNotaAnno(),];
            }
        }
        return $risultato;
    }

    protected function ordina(Collection $array, $oggettoInterno, $campo = null) {
        $valori = $array->getValues();
        \usort($valori, function ($a, $b) use ($oggettoInterno, $campo) {
            $oggettoInterno = 'get' . $oggettoInterno;
            if ($campo) {
                $campo = 'get' . $campo;
                return $a->$oggettoInterno()->$campo() > $b->$oggettoInterno()->$campo();
            } else {
                return $a->$oggettoInterno() > $b->$oggettoInterno();
            }
        });
        return $valori;
    }

    public function controllaValiditaVariazione(): EsitoValidazione {
        $parent = parent::controllaValiditaVariazione();
        $richiesta = $this->variazione->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $esitoPCEsterno = true;
        if ($procedura->isMultiPianoCosto() && count($richiesta->getProponenti()) > 1) {
            foreach ($richiesta->getProponenti() as $proponente) {
                $esitoPianoCosti = $this->validaPianoDeiCosti(null, $proponente);
                if($esitoPianoCosti->getEsito() == false) {
                    $esitoPCEsterno = false;
                }
                $esitoTMP = $parent->merge($esitoPianoCosti);
            }
        } else {
            $esitoPianoCosti = $this->validaPianoDeiCosti();
            if ($esitoPianoCosti->getEsito() == false) {
                $esitoPCEsterno = false;
            }
            $esitoTMP = $parent->merge($esitoPianoCosti);
        }
        $esito = $parent->merge($esitoTMP);
        if($esitoPCEsterno == false) {
            $esito->setEsito(false);
        }
        return $esito;
    }

    public function validaPianoDeiCosti(int $filtro_anno = null, Proponente $proponente = null): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Piano costi");

        $richiesta = $this->variazione->getRichiesta();

        $voci = $this->variazione->getVociPianoCostoProponente($proponente);

        if ($voci->isEmpty()) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Piano costi non definito");
            $esito->addMessaggio("Piano costi non definito");
        }

        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());
        $annualitaFiltrate = \array_filter(
                $annualita,
                function ($anno) use ($filtro_anno) {
            return \is_null($filtro_anno) || $anno == $filtro_anno;
        },
                \ARRAY_FILTER_USE_KEY
        );
        /** @var VariazioneVocePianoCosto $voce_piano_costo */
        foreach ($voci as $voce_piano_costo) {
            if (!$voce_piano_costo->verificaImporti($annualitaFiltrate)) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Piano costi non definito o non valido - Premere SALVA all'interno di ogni sezione");
                $esito->addMessaggio("Piano costi non definito o non valido");
                break;
            }
        }

        return $esito;
    }

    /**
     * Genero le voci piano costo variazione
     */
    protected function operazioniSpecificheInvioVariazione(): void {
        $voci = $this->variazione->getVociPianoCosto();
        foreach ($voci as $voce) {
            for ($i = 1; $i < 8; ++$i) {
                $metodoSet = "setImportoApprovatoAnno" . $i;
                $metodoGet = "getImportoVariazioneAnno" . $i;
                $voce->$metodoSet($voce->$metodoGet());
            }
        }
    }
    
    public function validaDocumenti(): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Documenti");
        
        
        return $esito;
    }

}
