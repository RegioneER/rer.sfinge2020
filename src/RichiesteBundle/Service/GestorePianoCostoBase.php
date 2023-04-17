<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\VocePianoCosto;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\Pagamento;
use RichiesteBundle\Entity\Richiesta;

class GestorePianoCostoBase extends AGestorePianoCosto {

    const EPSILON = 0.01;
    const TOT = 'TOT';

    public function pianoDeiCostiProponente($id_proponente, $opzioni = array()) {
        // TODO: Implement pianoDeiCostiProponente() method.
    }

    public function totalePianoDeiCosti($richiesta, $opzioni = array(), $twig = null, $opzioni_twig = array(), $type = null) {
        $id_richiesta = $richiesta->getId();

        if (false == $this->esisteTotalePianoCosti($richiesta)) {
            return new GestoreResponse($this->addErrorRedirect('Non sono stati definiti i piani costo dei proponenti', 'dettaglio_richiesta', array('id_richiesta' => $richiesta->getId())));
        }

        $opzioni['url_indietro'] = $this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $id_richiesta));
        $opzioni['disabled'] = true;
        $opzioni['modalita_finanziamento_attiva'] = $richiesta->getProcedura()->getModalitaFinanziamentoAttiva();

        if (!array_key_exists('annualita', $opzioni)) {
            $opzioni['annualita'] = 1;
        }
        if (!array_key_exists('labels_anno', $opzioni)) {
            $opzioni['labels_anno'] = array(
                'importo_anno_1' => '',
            );
        }
        if (!array_key_exists('totale', $opzioni)) {
            $opzioni['totale'] = false;
        }
        if (is_null($twig)) {
            $twig = 'RichiesteBundle:Richieste:totalePianoCosto.html.twig';
        }

        if (is_null($type)) {
            $type = "RichiesteBundle\Form\PianoCostiBaseType";
        }

        $proponente = $this->calcolaPianoCostiTotale($richiesta);

        $form = $this->createForm($type, $proponente, $opzioni);

        $importi_anni = $this->calcolaImportiAnni($proponente, $opzioni['annualita']);
        $totale = array_sum($importi_anni);

        $dati['form'] = $form->createView();
        $dati['annualita'] = $opzioni['annualita'];
        $dati['labels_anno'] = $opzioni['labels_anno'];
        $dati['totale'] = $opzioni['totale'];
        $dati['importi_anni'] = $importi_anni;
        $dati['importo_totale'] = $totale;

        $dati = array_merge($dati, $opzioni_twig);

        //aggiungo il titolo della pagina e le info della breadcrumb
        $this->container->get('pagina')->setTitolo('Totale piano costi');
        $this->container->get('pagina')->setSottoTitolo('pagina per la visualizzazione del piano costi totale della domanda');
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco richieste', $this->generateUrl('elenco_richieste'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Dettaglio richiesta', $this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $id_richiesta)));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Totale piano costi');

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function aggiornaPianoDeiCostiProponente($id_proponente, $opzioni = array(), $twig = null, $opzioni_twig = array()) {
        /** @var Proponente $proponente */
        $proponente = $this->getEm()->getRepository('RichiesteBundle:Proponente')->find($id_proponente);
        $richiesta = $proponente->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $id_richiesta = $richiesta->getId();

        $request = $this->getCurrentRequest();

        $proponente->ordinaVociPianoCosto();

        if (!\array_key_exists('url_indietro', $opzioni)) {
            $opzioni['url_indietro'] = $this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $id_richiesta));
        }
        $opzioni['disabled'] = $this->container->get('gestore_richieste')->getGestore($procedura)->isRichiestaDisabilitata($id_richiesta);
        $opzioni['modalita_finanziamento_attiva'] = $procedura->getModalitaFinanziamentoAttiva();

        if (!array_key_exists('annualita', $opzioni)) {
            $opzioni['annualita'] = 1;
        }
        if (!array_key_exists('labels_anno', $opzioni)) {
            $opzioni['labels_anno'] = array(
                'importo_anno_1' => '',
            );
        }
        if (!array_key_exists('salva_contributo', $opzioni)) {
            $salvaContributo = false;
        } else {
            $salvaContributo = $opzioni['salva_contributo'];
            unset($opzioni['salva_contributo']);
        }
        if (!array_key_exists('totale', $opzioni)) {
            $opzioni['totale'] = false;
        }
        if (is_null($twig)) {
            $twig = 'RichiesteBundle:Richieste:pianoCosto.html.twig';
        }
        $type = 'RichiesteBundle\Form\PianoCostiBaseType';
        if (array_key_exists('type', $opzioni)) {
            $type = $opzioni['type'];
            unset($opzioni['type']);
        }

        $redirect_url = $this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $id_richiesta));

        if (array_key_exists('redirect_url', $opzioni)) {
            $redirect_url = $opzioni['redirect_url'];
            unset($opzioni['redirect_url']);
        }


        $form = $this->createForm($type, $proponente, $opzioni);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->beginTransaction();
                    $em->persist($proponente);
                    $em->flush();

                    if ($salvaContributo == true) {
                        $contributo = $this->calcolaContributo($richiesta);
                        $richiesta->setContributoRichiesta($contributo);
                        $em->persist($richiesta);
                    }

                    $em->flush();
                    $em->commit();
                    $this->addFlash('success', 'Modifiche salvate correttamente');

                    return new GestoreResponse($this->redirect($redirect_url));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', 'Errore nel salvataggio delle informazioni');
                }
            } else {
                if (count($form->getErrors()) != count($form->getErrors(true))) {
                    $error = new \Symfony\Component\Form\FormError('Sono presenti valori non corretti o non validi. È ammesso soltanto il separatore dei decimali.');
                    $form->addError($error);
                }
            }
        }

        if (!array_key_exists('onKeyUp', $opzioni_twig)) {
            $dati['onKeyUp'] = 'calcolaTotaleSezione';
        }

        $dati['form'] = $form->createView();
        $dati['annualita'] = $opzioni['annualita'];
        $dati['labels_anno'] = $opzioni['labels_anno'];
        $dati['totale'] = $opzioni['totale'];
        $dati['disabled'] = $opzioni['disabled'];
        $dati['denominazione_proponente'] = $proponente->getSoggetto()->getDenominazione();
        $dati['richiesta'] = $richiesta;
        $dati = array_merge($dati, $opzioni_twig);
        //aggiungo il titolo della pagina e le info della breadcrumb
        $this->container->get('pagina')->setTitolo('Piano costi');
        $this->container->get('pagina')->setSottoTitolo('pagina per la compilazione del piano costi della domanda');
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco richieste', $this->generateUrl('elenco_richieste'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Dettaglio richiesta', $this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $id_richiesta)));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Piano costi');

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function generaPianoDeiCostiProponente($id_proponente, $opzioni = array()) {

        $em = $this->getEm();
        /** @var Proponente $proponente */
        $proponente = $em->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
        $richiesta = $proponente->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $vociPiano = $em->getRepository("RichiesteBundle:PianoCosto")->getVociDaProcedura($procedura->getId());

        try {
            foreach ($vociPiano as $vocePiano) {
                $voce = new VocePianoCosto();
                $voce->setPianoCosto($vocePiano);
                $voce->setProponente($proponente);
                $voce->setRichiesta($richiesta);
                $proponente->addVociPianoCosto($voce);
                $richiesta->addVociPianoCosto($voce);
                $em->persist($voce);
            }
            $em->persist($proponente);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function validaPianoDeiCostiProponente($id_proponente, $opzioni = array()) {
        $em = $this->getEm();
        $proponente = $em->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
        $procedura = $proponente->getRichiesta()->getProcedura();
        if ($procedura->isProceduraParticolare()) {
            $esito = new EsitoValidazione();
            $pianoCosti = $proponente->getVociPianoCosto();
            foreach ($pianoCosti as $voce) {
                if ($voce->getImportoAnno1() == 0.00) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("L'importo non può essere 0.00");
                    return $esito;
                }
            }
            $esito->setEsito(true);
            return $esito;
        } else {
            throw new \Exception('Implementare il metodo validaPianoDeiCostiProponente nel relativo gestore piano costi');
        }
    }

    public function calcolaContributo(Richiesta $oggetto, array $opzioni = array()): float {
        throw new \LogicException('Metodo non implementato');
        // TODO: Implement calcolaContributo() method.
    }

    public function calcolaCostoTotale($id_richiesta, $opzioni = array()) {
        return $this->getEm()->getRepository("RichiesteBundle\Entity\VocePianoCosto")->getCostoTotaleRichiesta($id_richiesta);
    }

    /**
     * @return EsitoValidazione
     */
    public function validaPianoDeiCosti($id_richiesta, $opzioni = array()) {
        $em = $this->getEm();
        $esito = new EsitoValidazione(true);
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:richiesta")->find($id_richiesta);
        /* Se la procedura prevede un piano per ogni proponente
         * Si ciclano i proponenti e si verificano i piani di costo
         */
        if ($richiesta->getProcedura()->getMultiPianoCosto()) {
            foreach ($richiesta->getProponenti() as $proponente) {
                if (count($proponente->getVociPianoCosto()) == 0) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("Piano costi non definito per i proponente: " . $proponente->getSoggetto()->getDenominazione());
                    return $esito;
                }
                $esitoProponente = $this->validaPianoDeiCostiProponente($proponente);
                if (!$esitoProponente->getEsito()) {
                    foreach ($esitoProponente->getMessaggi() as $messaggio) {
                        $esito->addMessaggio($messaggio);
                    }
                    foreach ($esitoProponente->getMessaggiSezione() as $messaggio) {
                        $esito->addMessaggioSezione($messaggio);
                    }
                    $esito->setEsito(false);
                }
            }
        }
        /*
         * 	Altrimenti verifico solo il piano del mandatario	 
         */ else {
            $mandatario = $em->getRepository("RichiesteBundle:Richiesta")->getMandatarioRichiesta($id_richiesta);
            if (count($mandatario->getVociPianoCosto()) == 0) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Piano costi non definito");
                return $esito;
            }
            $esitoMandatario = $this->validaPianoDeiCostiProponente($mandatario);
            if (!$esitoMandatario->getEsito()) {
                foreach ($esitoMandatario->getMessaggi() as $messaggio) {
                    $esito->addMessaggio($messaggio);
                }
                foreach ($esitoMandatario->getMessaggiSezione() as $messaggio) {
                    $esito->addMessaggioSezione($messaggio);
                }
                $esito->setEsito(false);
            }
        }

        $messaggiSezioneMerge = array_unique($esito->getMessaggiSezione());
        $messaggiMerge = array_unique($esito->getMessaggi());

        $esito->setMessaggiSezione($messaggiSezioneMerge);
        $esito->setMessaggio($messaggiMerge);

        return $esito;
    }

    public function getPianiDeiCostiProponente($id_proponente) {
        // TODO: Implement getPianiDeiCostiProponente() method.
    }

    public function ordina(Collection $array, $oggettoInterno, $campo = null) {
        $iterator = $array->getIterator();
        $iterator->uasort(function ($a, $b) use ($oggettoInterno, $campo) {
            $oggettoInterno = "get$oggettoInterno";
            if ($campo) {
                $campo = "get$campo";
                return $a->$oggettoInterno()->$campo() - $b->$oggettoInterno()->$campo();
            } else {
                return $a->$oggettoInterno() - $b->$oggettoInterno();
            }
        });
        return new ArrayCollection(\iterator_to_array($iterator));
    }

    public function getSezioni($id_proponente) {
        $id_procedura = \is_null($id_proponente) ?
            $this->getProcedura()->getId() :
            $this->getEm()
                ->getRepository('RichiesteBundle:Proponente')
                ->find($id_proponente)
                ->getRichiesta()
                ->getProcedura()
                ->getId();
        return $this->getEm()->getRepository("RichiesteBundle:PianoCosto")->getSezioniDaProcedura($id_procedura);
    }

    public function getVociSpesa($id_proponente) {
        return $this->getEm()->getRepository("RichiesteBundle:PianoCosto")->getDistinctVociDaProcedura($this->getProcedura()->getId());
    }

    /**
     *
     * Rende un array multi livello in cui la prima chiave è la stringa del titolo delle voci spesa distinct per il piano costo relativa alla procedura
     * ogni elemento dell'array è un ulteriore array con chiave il titolo della sezione e il valore è l'importo
     *
     * array["titolo voce spesa"]["titolo sezione"] => importo
     *
     * @param $id_proponente
     * @param array $opzioni
     * @return array
     * @throws SfingeException
     */
    public function generaArrayVista($id_proponente, $opzioni = array()) {
        $proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);

        $richiesta = $proponente->getRichiesta();

        //prendo la distinct delle voci spesa
        $vociSpesa = $this->getVociSpesa($id_proponente);

        //prendo la distinct delle sezioni
        $sezioni = $this->getSezioni($id_proponente);

        //formo l'array
        $risultato = array();

        //formo le singole righe
        if (!isset($opzioni['pdc_annualita'])) {
            //if($pdc_annualita == 0){
            //formo l'intestazione
            foreach ($sezioni as $sezione) {
                $risultato["Voce spesa"][$sezione->getTitoloSezione()] = $sezione->getTitoloSezione();
            }
            $risultato["Voce spesa"]["Totale"] = "Totale";
            foreach ($vociSpesa as $voceSpesa) {
                $totale = 0.00;
                if (count($sezioni)) {
                    foreach ($sezioni as $sezione) {
                        //trovo il valore per quella voce spesa e quella sezione nelle voci pdc del proponente
                        foreach ($proponente->getVociPianoCosto() as $voceSpesaProponente) {
                            if ($voceSpesaProponente->getPianoCosto()->getIdentificativoPdf() == $voceSpesa->getIdentificativoPdf()) {
                                if ($voceSpesaProponente->getPianoCosto()->getSezionePianoCosto()->getTitoloSezione() == $sezione->getTitoloSezione()) {
                                    if ($opzioni["codice_voce"] == true && $voceSpesa->getCodice() != 'TOT') {
                                        if ($opzioni["formatta_importo"] == false) {
                                            $risultato[$voceSpesa->getCodice() . ") " . $voceSpesa->getTitolo()][$sezione->getTitoloSezione()] = $voceSpesaProponente->getImportoAnno1();
                                        } else {
                                            $risultato[$voceSpesa->getCodice() . ") " . $voceSpesa->getTitolo()][$sezione->getTitoloSezione()] = number_format($voceSpesaProponente->getImportoAnno1(), 2, ',', '.');
                                        }
                                    } else {
                                        if ($opzioni["formatta_importo"] == false) {
                                            $risultato[$voceSpesa->getTitolo()][$sezione->getTitoloSezione()] = $voceSpesaProponente->getImportoAnno1();
                                        } else {
                                            $risultato[$voceSpesa->getTitolo()][$sezione->getTitoloSezione()] = number_format($voceSpesaProponente->getImportoAnno1(), 2, ',', '.');
                                        }
                                    }
                                    $totale += $voceSpesaProponente->getImportoAnno1();
                                }
                            }
                        }
                    }
                    if ($opzioni["calcola_totale"] == true) {
                        if ($opzioni["formatta_importo"] == false) {
                            $risultato[$voceSpesa->getTitolo()]["Totale"] = number_format($totale, 2, ',', '');
                        } else {
                            $risultato[$voceSpesa->getTitolo()]["Totale"] = number_format($totale, 2, ',', '.');
                        }
                    }
                } else {
                    foreach ($proponente->getVociPianoCosto() as $voceSpesaProponente) {
                        if ($voceSpesaProponente->getPianoCosto()->getIdentificativoPdf() == $voceSpesa->getIdentificativoPdf()) {
                            if ($opzioni["formatta_importo"] == false) {
                                $risultato[$voceSpesa->getTitolo()]["Totale"] = $voceSpesaProponente->getImportoAnno1();
                            } else {
                                $risultato[$voceSpesa->getTitolo()]["Totale"] = number_format($voceSpesaProponente->getImportoAnno1(), 2, ',', '.');
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($sezioni as $sezione) {
                foreach ($vociSpesa as $voceSpesa) {
                    if (count($sezioni)) {
                        foreach ($sezioni as $sezione) {
                            foreach ($proponente->getVociPianoCosto() as $voceSpesaProponente) {
                                if ($voceSpesaProponente->getPianoCosto()->getIdentificativoPdf() == $voceSpesa->getIdentificativoPdf()) {
                                    if ($voceSpesaProponente->getPianoCosto()->getSezionePianoCosto()->getTitoloSezione() == $sezione->getTitoloSezione()) {
                                        for ($i = 1; $i <= $opzioni['pdc_annualita']; $i++) {
                                            $metodo = "getImportoAnno" . $i;
                                            if ($opzioni["codice_voce"] == true && $voceSpesa->getCodice() != 'TOT') {
                                                if ($opzioni["formatta_importo"] == false) {
                                                    $risultato[$sezione->getTitoloSezione()][$voceSpesa->getCodice() . ") " . $voceSpesa->getTitolo()]['ANNO' . $i] = $voceSpesaProponente->$metodo();
                                                } else {
                                                    $risultato[$sezione->getTitoloSezione()][$voceSpesa->getCodice() . ") " . $voceSpesa->getTitolo()]['ANNO' . $i] = number_format($voceSpesaProponente->$metodo(), 2, ',', '.');
                                                }
                                            } else {
                                                if ($opzioni["formatta_importo"] == false) {
                                                    $risultato[$sezione->getTitoloSezione()][$voceSpesa->getTitolo()]['ANNO' . $i] = $voceSpesaProponente->$metodo();
                                                } else {
                                                    $risultato[$sezione->getTitoloSezione()][$voceSpesa->getTitolo()]['ANNO' . $i] = number_format($voceSpesaProponente->$metodo(), 2, ',', '.');
                                                }
                                            }
                                        }
                                        if ($opzioni['interventi_sede'] == true) {
                                            if ($opzioni["codice_voce"] == true && $voceSpesa->getCodice() != 'TOT') {
                                                $risultato[$sezione->getTitoloSezione()][$voceSpesa->getCodice() . ") " . $voceSpesa->getTitolo()]['INTERVENTI'] = $this->getEm()->getRepository("RichiesteBundle:InterventoSede")->getInterventiDaProponenteVoce($proponente, $voceSpesa->getCodice(), $sezione->getCodice());
                                            } else {
                                                $risultato[$sezione->getTitoloSezione()][$voceSpesa->getTitolo()]['INTERVENTI'] = $this->getEm()->getRepository("RichiesteBundle:InterventoSede")->getInterventiDaProponenteVoce($proponente, $voceSpesa->getCodice(), $sezione->getCodice());
                                            }
                                        }
                                        if ($opzioni['interventi_richiesta'] == true) {
                                            if ($opzioni["codice_voce"] == true && $voceSpesa->getCodice() != 'TOT') {
                                                $risultato[$sezione->getTitoloSezione()][$voceSpesa->getCodice() . ") " . $voceSpesa->getTitolo()]['INTERVENTI'] = $this->getEm()->getRepository("RichiesteBundle:InterventoSede")->getInterventiDaRichiestaVoce($richiesta, $voceSpesa->getCodice());
                                            } else {
                                                $risultato[$sezione->getTitoloSezione()][$voceSpesa->getTitolo()]['INTERVENTI'] = $this->getEm()->getRepository("RichiesteBundle:InterventoSede")->getInterventiDaRichiestaVoce($richiesta, $voceSpesa->getCodice());
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        foreach ($proponente->getVociPianoCosto() as $voceSpesaProponente) {
                            if ($voceSpesaProponente->getPianoCosto()->getIdentificativoPdf() == $voceSpesa->getIdentificativoPdf()) {
                                if ($opzioni["formatta_importo"] == false) {
                                    $risultato[$sezione->getTitoloSezione()][$voceSpesa->getTitolo()]["Totale"] = $voceSpesaProponente->getImportoAnno1();
                                } else {
                                    $risultato[$sezione->getTitoloSezione()][$voceSpesa->getTitolo()]["Totale"] = number_format($voceSpesaProponente->getImportoAnno1(), 2, ',', '.');
                                }
                            }
                        }
                    }
                }
            }
        }
        return $risultato;
    }

    public function getAnnualita($id_proponente) {
        $proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
        $richiesta = $proponente->getRichiesta();
        if ($richiesta->isProceduraParticolare() == true) {
            return array("1" => $richiesta->getProcedura()->getAnnoProgrammazione());
        }
        throw new \Exception('Ridefinire la getAnnualita nel gestore piano costo specifico con le annualità corrette relative al bando');
    }

    public function calcolaImportiAnni($proponente, $anni) {
        $piano = $proponente->getVociPianoCosto();
        $importo_anno = array();

        for ($i = 1; $i <= $anni; $i++) {
            $importo_anno[$i] = 0;
        }

        foreach ($piano as $voce) {
            if ($voce->getPianoCosto()->getCodice() == "TOT") {
                for ($i = 1; $i <= $anni; $i++) {
                    $importo_anno[$i] += $voce->{"getImportoAnno" . $i}();
                }
            }
        }

        return $importo_anno;
    }

    public function calcolaImportiAnniRichiesta($richiesta) {

        $importo_anno = array();
        $anni = $this->getAnnualita($richiesta->getMandatario());

        for ($i = 1; $i <= count($anni); $i++) {
            $importo_anno[$i] = 0;
        }

        foreach ($richiesta->getProponenti() as $proponente) {
            $piano = $proponente->getVociPianoCosto();
            foreach ($piano as $voce) {
                if ($voce->getPianoCosto()->getCodice() == "TOT") {
                    for ($i = 1; $i <= count($anni); $i++) {
                        $importo_anno[$i] += $voce->{"getImportoAnno" . $i}();
                    }
                }
            }
        }

        return $importo_anno;
    }

    public function calcolaImportiSezioni($proponente) {
        $piano = $proponente->getVociPianoCosto();
        $importi_sezioni = array();

        foreach ($piano as $voce) {
            if ($voce->getPianoCosto()->getCodice() == "TOT") {
                $codice_sezione = $voce->getPianoCosto()->getSezionePianoCosto()->getCodice();
                if (!array_key_exists($codice_sezione, $importi_sezioni)) {
                    $importi_sezioni[$codice_sezione] = 0;
                }

                $importi_sezioni[$codice_sezione] += $voce->getImportoTotale();
            }
        }

        return $importi_sezioni;
    }

    protected function calcolaPianoCostiTotale($richiesta) {
        $res = new Proponente();
        $annualita = $this->getAnnualita($richiesta->getMandatario()->getId());
        $voci = new ArrayCollection();
        foreach ($richiesta->getProponenti() as $proponente) {
            foreach ($proponente->getVociPianoCosto() as $voce) {
                $piano = $voce->getPianoCosto();
                if (!isset($voci[$piano->getId()])) {
                    $voci[$piano->getId()] = new VocePianoCosto();
                    $voci[$piano->getId()]->setPianoCosto($piano);
                    $voci[$piano->getId()]->setRichiesta($richiesta);
                }

                for ($i = 1; $i <= count($annualita); $i++) {
                    $voci[$piano->getId()]->{"setImportoAnno" . $i}($voci[$piano->getId()]->{"getImportoAnno" . $i}() + $voce->{"getImportoAnno" . $i}());
                }
            }
        }

        $voci_piano_costo = $this->ordina($voci, 'PianoCosto', 'Ordinamento');
        $res->setVociPianoCosto($voci_piano_costo);
        $res->setRichiesta($richiesta);
        return $res;
    }

    public function validaTotalePianoDeiCosti($richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);

        return $esito;
    }

    public function esisteTotalePianoCosti($richiesta) {
        $esito = false;
        if ($richiesta->getProcedura()->getMultiPianoCosto()) {
            foreach ($richiesta->getProponenti() as $proponente) {
                if (count($proponente->getVociPianoCosto()) > 0) {
                    $esito = true;
                }
            }
        }
        return $esito;
    }

    /**
     * 
     * Questa funzione nasce perchè hanno fatto definire il piano costi su un'unica annualità
     * però poi vogliono in rendicontazione associate le spese alle singole annualità :|
     * Per cui in rendicontazione provo a leggere questa funzione che se riudefinita per bando (dove serve farlo)
     * tornerà l'array con le annualità richieste,
     * se non viene ridefinita torna null e allora la rendicontazione usa la getAnnualita() classica
     * 
     */
    public function getAnnualitaRendicontazione($id_proponente) {
        return null;
    }

    // usata in rendicontazione

    /**
     * @param Pagamento $pagamento
     */
    public function calcolaImportoSpeseGenerali($pagamento, $proponente, $codiceSezione = null) {
        throw new \Exception('Implementare nel gestore specifico per i bandi dove sono previste le spese generali');
    }

    /**
     * 
     * @param Pagamento $pagamento
     * @throws \Exception
     * return array
     */
    public function getVociSpeseGenerali($pagamento) {
        throw new \Exception('Implementare nel gestore specifico per i bandi dove sono previste le spese generali');
    }

    /**
     * @param VocePianoCosto[]|Collection $vociPianoCosto
     * @param string $codiceSezione
     * @return VocePianoCosto[]
     */
    protected function filtraSezione(iterable $vociPianoCosto, string $codiceSezione) {
        if ($vociPianoCosto instanceof Collection) {
            return $vociPianoCosto->filter(function (VocePianoCosto $voce) use ($codiceSezione) {
                    return $voce->getPianoCosto()->getSezionePianoCosto()->getCodice() == $codiceSezione;
                });
        }
        @trigger_error("L'utilizzo della funzione con array è deprecato passare una Collection", E_USER_DEPRECATED);

        return \array_reduce($vociPianoCosto, function ($carry, $value) use ($codiceSezione) {
            /** @var VocePianoCosto $value */
            $pianoCosto = $value->getPianoCosto();
            if (\is_null($pianoCosto)) {
                return $carry;
            }
            $sezione = $pianoCosto->getSezionePianoCosto();
            if (\is_null($sezione)) {
                return $carry;
            }
            if ($sezione->getCodice() == $codiceSezione) {
                $carry[] = $value;
            }
            return $carry;
        }, array());
    }

    protected function verificaTotale(array $voci, Proponente $proponente) {
        $res = true;
        foreach ($this->getAnnualita($proponente->getId()) as $anno => $value) {
            $res = $res && $this->verificaTotalePerAnno($voci, $anno);
        }
        return $res;
    }

    /**
     * @param VocePianoCosto[] $voci
     * @param int $anno
     * @return bool
     */
    protected function verificaTotalePerAnno(iterable $voci, $anno) {
        $totaleMemorizzato = 0.0;
        $totaleCalcolato = 0.0;
        /** @var VocePianoCosto $voce */
        foreach ($voci as $voce) {
            $codice = $voce->getPianoCosto()->getCodice();
            if ($codice == self::TOT) {
                $totaleMemorizzato = $voce->{'getImportoAnno' . $anno}();
            } else {
                $totaleCalcolato += $voce->{'getImportoAnno' . $anno}();
            }
        }
        if (abs($totaleCalcolato - $totaleMemorizzato) > self::EPSILON) {
            return false;
        }
        return true;
    }

    public function generaArrayVistaTotaleRichiestaSingolaAnnualita($id_richiesta, $opzioni = array()) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        //prendo la distinct delle voci spesa
        $vociSpesa = $this->getVociSpesa(null);

        //prendo la distinct delle sezioni
        $sezioni = $this->getSezioni(null);

        //formo l'array
        $risultato = array();

        //formo l'intestazione
        foreach ($sezioni as $sezione) {
            $risultato["Voce spesa"][$sezione->getTitoloSezione()] = $sezione->getTitoloSezione();
        }
        $risultato["Voce spesa"]["Totale"] = "Totale";
        foreach ($vociSpesa as $voceSpesa) {
            if (count($sezioni)) {
                foreach ($sezioni as $sezione) {
                    //trovo il valore per quella voce spesa e quella sezione nelle voci pdc del proponente
                    foreach ($richiesta->getVociPianoCosto() as $voceSpesaR) {
                        if ($voceSpesaR->getPianoCosto()->getIdentificativoPdf() == $voceSpesa->getIdentificativoPdf()) {
                            if ($voceSpesaR->getPianoCosto()->getSezionePianoCosto()->getTitoloSezione() == $sezione->getTitoloSezione()) {
                                if (!isset($risultato[$voceSpesa->getTitolo()][$sezione->getTitoloSezione()])) {
                                    if(array_key_exists('codice_voce', $opzioni) && $opzioni["codice_voce"] == true && $voceSpesa->getCodice() != 'TOT'){
                                        $risultato[$voceSpesa->getCodice() .') '. $voceSpesa->getTitolo()][$sezione->getTitoloSezione()] = 0.00;
                                    } else {
                                        $risultato[$voceSpesa->getTitolo()][$sezione->getTitoloSezione()] = 0.00;                                       
                                    }
                                }
                                if (array_key_exists('codice_voce', $opzioni) && $opzioni["codice_voce"] == true && $voceSpesa->getCodice() != 'TOT') {
                                    $risultato[$voceSpesa->getCodice() . ') ' . $voceSpesa->getTitolo()][$sezione->getTitoloSezione()] += $voceSpesaR->getImportoAnno1();
                                } else {
                                    $risultato[$voceSpesa->getTitolo()][$sezione->getTitoloSezione()] += $voceSpesaR->getImportoAnno1();
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($richiesta->getVociPianoCosto() as $voceSpesaR) {
                    if ($voceSpesaR->getPianoCosto()->getIdentificativoPdf() == $voceSpesa->getIdentificativoPdf()) {
                        if (!isset($risultato[$voceSpesa->getTitolo()]["Totale"])) {
                            $risultato[$voceSpesa->getTitolo()]["Totale"] = 0.00;
                        }
                        $risultato[$voceSpesa->getTitolo()]["Totale"] = $voceSpesaR->getImportoAnno1();
                    }
                }
            }
        }
        return $risultato;
    }

    protected function filtraVociByCodice(Collection $voci, string $codice): Collection {
        return $voci->filter(function (VocePianoCosto $voce) use ($codice) {
                return $voce->getPianoCosto()->getCodice() == $codice;
            });
    }

    protected static function filtraChiaviArray($pagliaio, $aghi) {
        $res = array();
        foreach ($pagliaio as $key => $value) {
            $inserisci = true;
            foreach ($aghi as $filtro) {
                if ($filtro == $key) {
                    $inserisci = false;
                    break;
                }
            }
            if ($inserisci) {
                $res = array_merge($res, array($key => $value));
            }
        }
        return $res;
    }

}
