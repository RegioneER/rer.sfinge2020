<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use Symfony\Component\HttpFoundation\Response;
use MonitoraggioBundle\Entity\VoceSpesa;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto;
use RichiesteBundle\Entity\Proponente;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AttuazioneControlloBundle\Service\Istruttoria\AGestoreVariazioni;
use RichiesteBundle\Entity\VocePianoCosto;
use Symfony\Component\Form\FormInterface;
use AttuazioneControlloBundle\Form\Istruttoria\VariazionePianoCostiBaseType;

class GestoreVariazioniPianoCostiBase extends AGestoreVariazioni implements IGestoreVariazioniPianoCosti {
    /**
     * @var VariazionePianoCosti
     */
    protected $variazione;

    public function __construct(VariazionePianoCosti $variazione, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->container = $container;
    }

    protected function validaEsito(FormInterface &$form): void {
        $pulsanti = $form->get("pulsanti");
        if ($pulsanti->has("pulsante_valida") && $pulsanti->get("pulsante_valida")->isClicked()) {
            if (true == $this->variazione->getEsitoIstruttoria() && is_null($this->variazione->getContributoAmmesso())) {
                $form->addError(new \Symfony\Component\Form\FormError('In caso di variazione ammessa è necessario inserire il contributo ammesso nella sezione del totale piano costi'));
            }
        }
    }

    protected function applicaVariazione(): void {
        $ammesso = $this->variazione->getCostoAmmessoVariato();
        $this->variazione->setCostoAmmesso($ammesso);

        //impostiamo i dati di monitoraggio solo se progetto por fesr
        if($this->variazione->getRichiesta()->getFlagPor() == 1) {
        // Rigenero le VOCI DI SPESA del MONITORAGGIO
            $this->aggiornaFinanziamentoProgetto();
        }
    }

    public function pianoCostiVariazione($annualita, Proponente $proponente = null): Response {
        $voci_piano_costo = $this->variazione->getVociPianoCosto()
        ->filter(function (VariazioneVocePianoCosto $voceVariazione) use ($proponente) {
            $voce = $voceVariazione->getVocePianoCosto();
            return \is_null($proponente) || $voce->getProponente() == $proponente;
        });

        $opzioni['annualita'] = $annualita;
        $opzioni['url_indietro'] = $this->generateUrl("riepilogo_istruttoria_variazione", ['id_variazione' => $this->variazione->getId()]);
        $opzioni["disabled"] = is_null($this->variazione->getEsitoIstruttoria()) ? false : true;

        foreach ($voci_piano_costo as $voce) {
            $importo_approvato = $voce->{"getImportoApprovatoAnno" . $annualita}();
            if (is_null($importo_approvato)) {
                $importo_variazione = $voce->{"getImportoVariazioneAnno" . $annualita}();
                $voce->{"setImportoApprovatoAnno" . $annualita}($importo_variazione);
            }
        }
        $formModel = ['voci_piano_costo' => $voci_piano_costo];

        $form = $this->createForm(VariazionePianoCostiBaseType::class, $formModel, $opzioni);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->flush();
                $this->addFlash('success', "Modifiche salvate correttamente");
                return $this->redirect($this->generateUrl("riepilogo_istruttoria_variazione", ["id_variazione" => $this->variazione->getId()]));
            } catch (\Exception $e) {
                $this->addFlash('error', "Errore nel salvataggio delle informazioni");
            }
        }

        $dati['onKeyUp'] = 'calcolaTotaleSezione';
        $dati["form"] = $form->createView();
        $dati["annualita"] = $opzioni['annualita'];
        $dati["proponente"] = $proponente;
        $dati["menu"] = "piano_costi";
        $dati["variazione"] = $this->variazione;

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_istruttoria_variazioni"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Piano costi variazione");

        return $this->render("AttuazioneControlloBundle:Istruttoria\Variazioni:pianoCosti.html.twig", $dati);
    }

    public function totaliPianoCosti(): Response {
        $dati = [];
        $proponente = $this->variazione->getRichiesta()->getMandatario();
        $annualita_piano_costi = $this->container->get("gestore_piano_costo")->getGestore($this->variazione->getProcedura())->getAnnualita($proponente->getId());

        $somme_var = ["variato" => 0, "approvato" => 0];
        $totali_var = $this->getEm()->getRepository(VariazionePianoCosti::class)->getTotaliVariazione($this->variazione->getId());
        foreach ($totali_var as $chiave => $valore) {
            if (preg_match("/^variato/", $chiave)) {
                $somme_var["variato"] += $valore;
            } elseif (preg_match("/^approvato/", $chiave)) {
                $somme_var["approvato"] += $valore;
            }
        }

        $request = $this->getCurrentRequest();
        $opzioni["disabled"] = is_null($this->variazione->getEsitoIstruttoria()) ? false : true;
        $opzioni['url_indietro'] = $this->generateUrl("riepilogo_istruttoria_variazione", ['id_variazione' => $this->variazione->getId()]);

        $form = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\VariazionePianoCostiTotaleType", $this->variazione, $opzioni);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->flush();
                $this->addFlash('success', "Modifiche salvate correttamente");
                return $this->redirect($this->generateUrl("riepilogo_istruttoria_variazione", ["id_variazione" => $this->variazione->getId()]));
            } catch (\Exception $e) {
                $this->addFlash('error', "Errore nel salvataggio delle informazioni");
            }
        }

        $dati["totali_variazione"] = $totali_var;
        $dati["somme_variazione"] = $somme_var;
        $dati["annualita_piano_costi_variazione"] = $annualita_piano_costi;
        $dati["variazione"] = $this->variazione;
        $dati["form"] = $form->createView();

        $dati["annualita_piano_costi"] = $annualita_piano_costi;
        $dati["menu"] = "piano_costi";

        return $this->render("AttuazioneControlloBundle:Istruttoria\Variazioni:totaliPianoCosti.html.twig", $dati);
    }

    public function ordina(\Doctrine\Common\Collections\Collection $array, $oggettoInterno, $campo) {
        $valori = $array->getValues();
        usort($valori, function ($a, $b) use ($oggettoInterno, $campo) {
            $oggettoInterno = 'get' . $oggettoInterno;
            $campo = 'get' . $campo;
            return $a->$oggettoInterno()->getPianoCosto()->$campo() > $b->$oggettoInterno()->getPianoCosto()->$campo();
        });
        return $valori;
    }

    /**
     * Metodo richiamato contestualmente all'ESITO FINALE delle VALIDAZIONE della VARIAZIONE
     * serve per aggiornare automaticamente le VOCI DI SPESA; ai fini del monitoraggio
     */
    protected function aggiornaVociSpesa() {
        $em = $this->getEm();
        $variazioniVociPianoCosto = $this->variazione->getVociPianoCosto();      //VariazioniVociPianoCosto (N elementi)

        foreach ($variazioniVociPianoCosto as $variazioniVociPianoCosto) {
            // DESTINAZIONE
            $voceSpesaMon = new VoceSpesa();

            // RICHIESTA
            $voceSpesaMon->setRichiesta($this->variazione->getRichiesta());

            // IMPORTO VOCE
            $importoVoce = $variazioniVociPianoCosto->sommaImporti();
            if (is_null($importoVoce)) {
                $totaleVocePianoCosto = 0.00;
            } else {
                $totaleVocePianoCosto = $importoVoce;
            }
        }

        $tipoVoceSpesa = $variazioniVociPianoCosto->getVocePianoCosto()->getPianoCosto()->getTipoVoceSpesa();       // TipoVoceSpesa
        $codiceTipoVoceSpesa = $tipoVoceSpesa->getCodice();     // PROG, SUOLO, MURARIE, OPERA BENI, TOTALE

        $tc37VoceSpesa = $em->getRepository("MonitoraggioBundle\Entity\TC37VoceSpesa")->findBy(["voce_spesa" => $codiceTipoVoceSpesa]);

        if (count($tc37VoceSpesa) > 0) {
            $voceSpesaMon->setTipoVoceSpesa($tc37VoceSpesa[0]);      // TC37 - VOCI SPESA
            $voceSpesaMon->setImporto($totaleVocePianoCosto);

            // setto la DESTINAZIONE nella richiesta
            $richiesta = $this->variazione->getRichiesta();

            // RECUPERO il PRECEDENTE
            $monVociSpesa = $richiesta->getMonVociSpesa();

            foreach ($monVociSpesa as $monVoceSpesa) {
                $richiesta->removeMonVoceSpesa($monVociSpesa);
            }

            $richiesta->addMonVoceSpesa($voceSpesaMon);
        }

        return $richiesta;
    }
}
