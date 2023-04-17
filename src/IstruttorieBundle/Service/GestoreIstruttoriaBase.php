<?php

namespace IstruttorieBundle\Service;

use IstruttorieBundle\Entity\DocumentoIstruttoria;
use MonitoraggioBundle\Entity\TC47StatoProgetto;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use ProtocollazioneBundle\Entity\EmailProtocollo;
use IstruttorieBundle\Entity\NucleoIstruttoria;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use IstruttorieBundle\Entity\DocumentoNucleoIstruttoria;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use MonitoraggioBundle\Entity\TC24RuoloSoggetto;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use MonitoraggioBundle\Entity\RichiestaPianoCosti;
use MonitoraggioBundle\Entity\VoceSpesa;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\Constraints\DateTime;
use DocumentoBundle\Component\ResponseException;
use IstruttorieBundle\Entity\EsitoIstruttoria;
use IstruttorieBundle\Entity\FaseIstruttoria;
use IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria;
use Symfony\Component\HttpFoundation\Response;
use MonitoraggioBundle\Service\IGestoreImpegni;
use PdfBundle\Wrapper\PdfWrapper;
use SfingeBundle\Entity\Procedura;

class GestoreIstruttoriaBase extends AGestoreIstruttoria {

    /**
     * @param $id_richiesta
     * @return GestoreResponse
     * @throws SfingeException
     */
    public function riepilogoRichiesta($id_richiesta) {
        $istruttoria = $this->aggiornaIstruttoriaRichiesta($id_richiesta);
        $twig = 'IstruttorieBundle:Istruttoria:riepilogoRichiesta.html.twig';
        $dati = [
            'istruttoria' => $istruttoria,
            'menu' => 'riepilogo',
        ];

        $response = $this->render($twig, $dati);
        return new GestoreResponse($response);
    }

    /**
     * @return IstruttoriaRichiesta
     */
    public function aggiornaIstruttoriaRichiesta($id_richiesta, $opzioni = array()) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $procedura = $richiesta->getProcedura();
        $istruttoria = $richiesta->getIstruttoria();
        if (\is_null($istruttoria)) {
            $istruttoria = new IstruttoriaRichiesta();
            $istruttoria->setSospesa(false);
            $istruttoria->setRichiesta($richiesta);
            $richiesta->setIstruttoria($istruttoria);

            $this->getEm()->persist($istruttoria);
        }

        $fase = $istruttoria->getFase();
        if (!is_null($fase)) {
            // verifico completezza
            $fase_completa = $this->isFaseCompleta($istruttoria);

            // eventualmente aggiorno
            if ($fase_completa && $this->isFaseAvanzabile($istruttoria)) {
                $this->avanzaFaseIstruttoriaRichiesta($istruttoria);
            }
            if ($procedura->getId() == 71) {
                $this->forzaAggiornamentoCL($fase, $istruttoria);
            }
        } else {
            $this->avanzaFaseIstruttoriaRichiesta($istruttoria);
        }

        try {
            $this->getEm()->flush();
        } catch (\Exception $e) {
            throw new SfingeException("Errore nell'aggiornamento dell'istruttoria: " . $e->getMessage());
        }

        return $istruttoria;
    }

    public function riepilogoProponenti($id_richiesta) {
        $istruttoria = $this->aggiornaIstruttoriaRichiesta($id_richiesta);

        $twig = 'IstruttorieBundle:Istruttoria:riepilogoProponenti.html.twig';

        $dati['istruttoria'] = $istruttoria;
        $dati['menu'] = 'proponenti';

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function avanzaFaseIstruttoriaRichiesta($istruttoria_richiesta) {
        $fase_successiva = $this->getEm()->getRepository('IstruttorieBundle:FaseIstruttoria')->findFaseSuccessiva($istruttoria_richiesta);

        if (!is_null($fase_successiva)) {
            $istruttoria_richiesta->setFase($fase_successiva);
            $this->operazioniAvanzamentoFase($istruttoria_richiesta, $fase_successiva);
        }
    }

    public function operazioniAvanzamentoFase($istruttoria_richiesta, $fase) {
        if (!is_null($fase->getChecklist())) {
            foreach ($fase->getChecklist() as $checklist) {
                $gestore = $this->container->get('gestore_checklist')->getGestore($istruttoria_richiesta->getRichiesta()->getProcedura());
                $gestore->genera($istruttoria_richiesta, $checklist);
            }
        }
    }

    public function esitoFinaleIstruttoria($id_richiesta, $extra = array()) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $istruttoria = $richiesta->getIstruttoria();
        if (is_null($istruttoria)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $options['url_indietro'] = $this->generateUrl('elenco_richieste_inviate');
        $options['disabled'] = !$this->isGranted('ROLE_ISTRUTTORE_SUPERVISORE') || !is_null($istruttoria->getEsito()) || !$this->isEsitoFinaleEmettibile($istruttoria) || $istruttoria->getSospesa();
        $options['scelte_esito'] = $this->getEm()->getRepository("IstruttorieBundle\Entity\EsitoIstruttoria")->findBy(array('codice' => $this->getScelteEsitoFinale()));

        $form = $this->createForm("IstruttorieBundle\Form\EsitoFinaleType", $istruttoria, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($istruttoria->isSospesa()) {
                throw new SfingeException('Impossibile emettere esito, richiesta di integrazione in corso');
            }

            if ($form->isValid()) {
                if ($istruttoria->getEsito()->getEsitoPositivo()) {
                    $esito = $this->isEsitoFinalePositivoEmettibile($istruttoria);
                    if (!$esito->getEsito()) {
                        foreach ($esito->getMessaggi() as $messaggio) {
                            $this->addFlash('error', $messaggio);
                        }

                        return new GestoreResponse($this->redirect($this->generateUrl('esito_finale_istruttoria', array('id_richiesta' => $id_richiesta))));
                    }
                }
                if ($istruttoria->getEsito()->getCodice() == EsitoIstruttoria::NON_ISTRUIBILE) {
                    $this->applicaIstruttoriaNonIstruibile($istruttoria);
                }

                $em = $this->getEm();
                $this->creaLogIstruttoria($istruttoria, 'esito_finale');
                try {
                    $em->flush();
                    $this->addFlash('success', 'Esito finale istruttoria salvato correttamente');

                    return new GestoreResponse($this->redirect($this->generateUrl('esito_finale_istruttoria', array('id_richiesta' => $id_richiesta))));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Errore nel salvataggio delle informazioni');
                }
            }
        }

        $twig = isset($extra["twig"]) ? $extra["twig"] : 'IstruttorieBundle:Istruttoria:esitoFinaleIstruttoria.html.twig';

        $dati = array();
        $dati['istruttoria'] = $istruttoria;
        $dati['menu'] = 'esito';
        $dati['form'] = $form->createView();
        if (array_key_exists('url_genera_pdf', $options)) {
            $dati['url_genera_pdf'] = $options['url_genera_pdf'];
        }

        if (isset($extra)) {
            $dati = array_merge($dati, $extra);
        }

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function getScelteEsitoFinale() {
        return array('AMMESSO', 'NON_AMMESSO', 'SOSPESO', EsitoIstruttoria::NON_ISTRUIBILE);
    }

    public function isEsitoFinaleEmettibile($istruttoria_richiesta) {
        return true;
    }

    public function applicaIstruttoriaNonIstruibile(IstruttoriaRichiesta $istruttoria): void {
        //Istanzio le checklist non attive
        while ($fase_successiva = $this->getEm()->getRepository('IstruttorieBundle:FaseIstruttoria')->findFaseSuccessiva($istruttoria)) {
            $istruttoria->setFase($fase_successiva);
            $this->operazioniAvanzamentoFase($istruttoria, $fase_successiva);
        }

        foreach ($istruttoria->getValutazioniChecklist() as $checklist) {
            $checklist->setValidata(true);
            $checklist->setValutatore($this->getUser());
            $checklist->setDataValidazione(new \DateTime());
            $checklist->setAmmissibile(false);
        }

        $this->aggiornaIstruttoriaRichiesta($istruttoria->getRichiesta());

        try {
            $this->getEm()->flush();
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addError('Errore durate il salvataggio delle informazioni');
        }
    }

    public function datiCup($id_richiesta) {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $esisteCup = false;
        $istruttoria = $richiesta->getIstruttoria();
        if (!is_null($istruttoria->getCodiceCup())) {
            $esisteCup = true;
        }
        if (is_null($istruttoria)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $options = array();
        $options['url_indietro'] = $this->generateUrl('elenco_richieste_inviate');
        // richiesto di sbloccare sezione anche in presenza di richiesta integrazione
        $disabled = !$this->isGranted('ROLE_ISTRUTTORE') || $esisteCup;
        //$options['disabled'] = !$this->isGranted('ROLE_ISTRUTTORE') || $esisteCup; //|| $istruttoria->getSospesa(); // || !is_null($istruttoria->getEsito());
        $options['selezioni'] = $this->getSelezioniCup($id_richiesta, $esisteCup);
        $options['user'] = $this->getUser();

        $form = $this->createForm("IstruttorieBundle\Form\DatiCupType", $istruttoria, $options);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            $gestore_istruttoria = $this->container->get('gestore_istruttoria')->getGestore($richiesta->getProcedura());
            $gestore_istruttoria->creaLogIstruttoria($istruttoria, 'dati_cup');

            try {
                $em->persist($richiesta);
                $em->persist($istruttoria);
                $em->flush();
                $this->addFlash('success', 'Dati cup salvati correttamente');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore nel salvataggio delle informazioni');
            }
        }

        $twig = 'IstruttorieBundle:Istruttoria:datiCup.html.twig';

        $dati = array();
        $dati['istruttoria'] = $istruttoria;
        $dati['menu'] = 'cup';
        $dati['esiste_cup'] = $esisteCup;
        $dati['disabled'] = $disabled;
        $dati['form'] = $form->createView();

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function getSelezioniCup($id_richiesta, $esisteCup) {

        $selezioni = array();
        $selezioni["cup_natura"] = array();
        $selezioni["cup_tipologia"] = array();
        $selezioni["cup_settore"] = array();
        $selezioni["cup_sottosettore"] = array();
        $selezioni["cup_categoria"] = array();
        $selezioni["cup_tipi_copertura_finanziaria"] = array();

        if ($esisteCup) {
            // Recuperare le info da DB
            $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
            if (is_null($richiesta)) {
                throw new SfingeException('Risorsa non trovata');
            }

            $istruttoriaRichiesta = $richiesta->getIstruttoria();

            $tipiCoperturaFinanziaria = $istruttoriaRichiesta->getCupTipiCoperturaFinanziaria();

            array_push($selezioni["cup_natura"], $istruttoriaRichiesta->getCupNatura());
            array_push($selezioni["cup_tipologia"], $istruttoriaRichiesta->getCupTipologia());
            array_push($selezioni["cup_settore"], $istruttoriaRichiesta->getCupSettore());
            array_push($selezioni["cup_sottosettore"], $istruttoriaRichiesta->getCupSottosettore());
            array_push($selezioni["cup_categoria"], $istruttoriaRichiesta->getCupCategoria());

            foreach ($tipiCoperturaFinanziaria as $tipoCoperturaFinanziaria) {
                array_push($selezioni["cup_tipi_copertura_finanziaria"], $tipoCoperturaFinanziaria);
            }
        } else {

            $nature = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupNatura")->findAll();
            $cup_tipi_copertura_finanziaria = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria")->findAll();

            $selezioni = array();
            $selezioni["cup_natura"] = $nature;
            $selezioni["cup_tipologia"] = array();
            $selezioni["cup_settore"] = array();
            $selezioni["cup_sottosettore"] = array();
            $selezioni["cup_categoria"] = array();
            $selezioni["cup_tipi_copertura_finanziaria"] = $cup_tipi_copertura_finanziaria;
        }

        return $selezioni;
    }

    /**
     * @param IstruttoriaRichiesta $istruttoria_richiesta
     * @return bool
     */
    public function isFaseAvanzabile($istruttoria_richiesta) {
        // Purtroppo è stato fatto questo accrocchio perchè per la seconda finestra del bando rivitalizzazione centri storici
        // hanno modificato le checklist.
        if ($istruttoria_richiesta->getRichiesta()->getProcedura()->getId() == 95 &&
                ($istruttoria_richiesta->getFase()->getStep() == 2 || $istruttoria_richiesta->getFase()->getStep() == 4)) {
            return false;
        }
        return true;
    }

    public function creaLogIstruttoria($istruttoria, $oggetto) {
        $log_istruttoria = new \IstruttorieBundle\Entity\IstruttoriaLog();
        $log_istruttoria->setIstruttoriaRichiesta($istruttoria);
        $log_istruttoria->setOggetto($oggetto);
        $log_istruttoria->setUtente($this->getUser());
        $log_istruttoria->setData(new \DateTime());

        $istruttoria->addIstruttoriaLog($log_istruttoria);
    }

    public function creaLogIstruttoriaAtc($istruttoria, $oggetto) {
        $log_istruttoria = new \IstruttorieBundle\Entity\IstruttoriaAtcLog();
        $log_istruttoria->setIstruttoriaRichiesta($istruttoria);
        $log_istruttoria->setOggetto($oggetto);
        $log_istruttoria->setUtente($this->getUser());
        $log_istruttoria->setData(new \DateTime());
        $log_istruttoria->setAmmissibilitaAtto($istruttoria->getAmmissibilitaAtto());
        $log_istruttoria->setConcessione($istruttoria->getConcessione());
        $log_istruttoria->setContributoAmmesso($istruttoria->getContributoAmmesso());
        $log_istruttoria->setDataContributo($istruttoria->getDataContributo());
        $log_istruttoria->setImpegnoAmmesso($istruttoria->getImpegnoAmmesso());
        $log_istruttoria->setDataImpegno($istruttoria->getDataImpegno());
        $log_istruttoria->setAttoModificaConcessioneAtc($istruttoria->getAttoModificaConcessioneAtc());

        $istruttoria->addIstruttoriaAtcLog($log_istruttoria);
    }

    public function avanzamentoATC($id_richiesta) {
        $em = $this->getEm();
        $richiesta = $em->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $istruttoria = $richiesta->getIstruttoria();
        if (is_null($istruttoria)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $email_pec = $richiesta->getMandatario()->getSoggetto()->getEmailPec();

        $options = array();

        $options["url_indietro"] = $this->generateUrl('elenco_richieste_inviate');
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE") || is_null($istruttoria->getEsito()) || !$istruttoria->getEsito()->getEsitoPositivo() || $istruttoria->getValidazioneAtc() || is_null($email_pec);
        $options["atti"] = $em->getRepository("SfingeBundle\Entity\Atto")->findBy(array("procedura" => $richiesta->getProcedura()));
        $options["invalidabile"] = false;

        if (is_null($email_pec)) {
            $this->addFlash('warning', "Impossibile effettuare l'avanzamento in attuazione e controllo perchè non è disponibile l'indirizzo PEC del soggetto.");
        }

        if (!is_null($istruttoria->getEsito()) && !$istruttoria->getEsito()->getEsitoPositivo()) {
            $this->addFlash('warning', "Impossibile effettuare l'avanzamento in attuazione e controllo perchè l'istruttoria ha avuto esito negativo.");
        }

        if (!is_null($istruttoria->getValidazioneAtc())) {
            $options["invalidabile"] = true;
        }

        $form = $this->createForm("IstruttorieBundle\Form\AvanzamentoATCType", $istruttoria, $options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $passaggio_atc = $istruttoria->getConcessione() &&
                    $istruttoria->getAmmissibilitaAtto() &&
                    \is_null($istruttoria->getRichiesta()->getAttuazioneControllo());

            /*
             * Uso la request perchè il form è disabilitato quando la richiesta è validata e l'isClicked non funziona
             * per verificare se clicco su invalida
             */
            $b = $request->request->get('avanzamento_atc');
            $controllo_click_invalida = isset($b['pulsanti']) && isset($b['pulsanti']['pulsante_invalida']);
            $redirect_url = $this->generateUrl('avanzamento_atc', array("id_richiesta" => $id_richiesta));

            if ($form->get("pulsanti")->has("pulsante_valida") && $form->get("pulsanti")->get("pulsante_valida")->isClicked()) {

                $validazione = $this->validaATC($form);

                if ($validazione->getEsito()) {
                    $istruttoria->setValidazioneAtc(true);
                    $istruttoria->setUtenteValidatoreAtc($this->getUser());
                    $istruttoria->setDataValidazioneAtc(new \DateTime());

                    $messaggio = $passaggio_atc ? 'La richiesta è passata in attuazione e controllo' : 'I dati sono stati salvati correttamente';
                    //30/12/2020 ma non c'è motivo !!!
                    //$redirect_url = $this->generateUrl('elenco_richieste_inviate');
                    $oggettoLog = 'ATC_VALIDA';
                }
            } elseif ($controllo_click_invalida) {
                $messaggio = "I dati sono stati invalidati correttamente";
                $oggettoLog = 'ATC_INVALIDA';
            } else {
                $messaggio = "I dati sono stati salvati correttamente";
                $oggettoLog = 'ATC_SALVA';
            }

            if ($form->isValid()) {
                $connection = $em->getConnection();
                try {
                    $connection->beginTransaction();

                    if ($passaggio_atc &&
                            $form->get("pulsanti")->has("pulsante_valida") &&
                            $form->get("pulsanti")->get("pulsante_valida")->isClicked() &&
                            \is_null($richiesta->getAttuazioneControllo())) {

                        $atc = $this->generaATC($istruttoria);
                        $richiesta->setAttuazioneControllo($atc);
                        $richiesta = $this->aggiungiInformazioniMonitoraggio($richiesta);
                        $em->persist($richiesta);
                    }
                    if ($controllo_click_invalida) {
                        $istruttoria->setValidazioneAtc(null);
                    }

                    $this->creaLogIstruttoriaAtc($istruttoria, $oggettoLog);

                    $em->flush();
                    $connection->commit();
                    $this->addFlash('success', $messaggio);

                    return new GestoreResponse($this->redirect($redirect_url));
                } catch (SfingeException $e) {
                    if ($connection->isTransactionActive()) {
                        $connection->rollback();
                    }
                    $this->addError($e->getMessage());
                } catch (\Exception $e) {
                    if ($connection->isTransactionActive()) {
                        $connection->rollback();
                    }
                    $this->addFlash('error', $e->getCode() == '300' ? "Email pec non inviata correttamente. Si prega di riprovare o contattare l'assistenza" : 'Errore nel salvataggio delle informazioni');
                }
            }
        }

        $twig = 'IstruttorieBundle:Istruttoria:avanzamentoATC.html.twig';

        $dati = array();
        $dati['istruttoria'] = $istruttoria;
        $dati['menu'] = 'atc';
        $dati['form'] = $form->createView();
        $dati['disabled'] = $options['disabled'];

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function aggiungiInformazioniMonitoraggio(Richiesta $richiesta): Richiesta {
        if (!$richiesta->getFlagPor()) {
            return $richiesta;
        }

        $this->popolaVociSpesa($richiesta);
        $this->popolaLocalizzazioneGeografica($richiesta);
        $this->popolaStatoInizialeAttuazioneProgetto($richiesta);
        $this->popolaSoggettiCollegati($richiesta);
        $richiesta = $this->popolaRichiestaStrumentoAttuativo($richiesta);
        $richiesta = $this->popolaFinanziamento($richiesta);

        $programma = $this->creaProgramma($richiesta);
        $richiesta->addMonProgrammi($programma);
        $classificazioni = $this->creaClassificazioni($programma);
        foreach ($classificazioni as $classificazione) {
            $programma->addClassificazioni($classificazione);
        }
        $livelloGerarchicoPerAsse = $this->creaLivelloGerarchicoPerAsse($programma);
        $programma->addMonLivelliGerarchici($livelloGerarchicoPerAsse);
        $livelliGerarchicoObiettivoSpecifico = $this->creaLivelloGerarchicoPerObiettiviSpecifici($programma);
        if ($livelliGerarchicoObiettivoSpecifico->isEmpty()) {
            throw new SfingeException("Non sono stati definite le azioni per il bando: impossibile proseguire. Se si è autorizzati è possbile inserire i valori nella sezione atti amministrativi.");
        }
        foreach ($livelliGerarchicoObiettivoSpecifico as $l) {
            $programma->addMonLivelliGerarchici($l);
        }

        /** @var IGestoreImpegni $impegniService */
        $impegniService = $this->container->get('monitoraggio.impegni')->getGestore($richiesta);
        $impegniService->impegnoNuovoProgetto();

        $this->popolaIndicatoriOutput($richiesta);
        $this->popolaIndicatoriRisultato($richiesta);
        $istruttoria = $richiesta->getIstruttoria();
        $richiesta->setMonTipoOperazione($istruttoria->getCupTipologia()->getTc5TipoOperazione());
        $procedura = $richiesta->getProcedura();
        switch ($procedura->getMonTipoBeneficiario()) {
            case Procedura::MON_TIPO_PRG_PUBBLICO:
                $richiesta->setMonPrgPubblico(true);
                break;
            case Procedura::MON_TIPO_PRG_PRIVATO:
                $richiesta->setMonPrgPubblico(false);
                break;
            default:
                $isPubblico = $istruttoria->getTipologiaSoggetto() == 'PUBBLICO';
                $richiesta->setMonPrgPubblico($isPubblico);
                break;
        }

        return $richiesta;
    }

    public function validaATC($form) {
        $istruttoria = $form->getData();
        $esito = new EsitoValidazione(true);

        if ($form->isValid()) {
            if (is_null($istruttoria->getAttoAmmissibilitaAtc())) {
                $form->get('atto_ammissibilita_atc')->addError(new \Symfony\Component\Form\FormError('Atto di ammissibilità obbligatorio'));
            }

            if (is_null($istruttoria->getAttoConcessioneAtc())) {
                $form->get('atto_concessione_atc')->addError(new \Symfony\Component\Form\FormError('Atto di concessione obbligatorio'));
            }
        }

        return $esito;
    }

    protected function invioEmailPassaggioAtc($istruttoria) {
        $email_atc_config = $this->getEmailATCConfig($istruttoria);
        $to = array();
        $to[] = $email_atc_config['to'];
        $subject = 'Sfinge2020: richiesta in attuazione e controllo';
        $parametriView = array('istruttoria' => $istruttoria);
        $renderViewTwig = 'IstruttorieBundle:Email:passaggioAtc.email.html.twig';
        $noHtmlViewTwig = 'IstruttorieBundle:Email:passaggioAtc.email.twig';

        try {
            $esito = $this->container->get('messaggi.email')->inviaEmail($to, $email_atc_config['tipo'], $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig, $indirizzoAggiuntivo = null);
            if (!$esito->res) {
                throw new \Exception($esito->error);
            }

            return true;
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());

            return false;
        }
    }

    protected function popolaSoggettiCollegati(Richiesta $richiesta) {
        $ruoloRepository = $this->getEm()->getRepository('MonitoraggioBundle:TC24RuoloSoggetto');

        // Il "programmatore" (RER) è il codice tipo resp procedura e denom resp procedura della PA00
        $regione = $this->getEm()->getRepository('SoggettoBundle:Soggetto')->findOneBy(array("denominazione" => "Regione Emilia-Romagna", "forma_giuridica" => 42));
        if (\is_null($regione)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $ruoloProgrammatore = $ruoloRepository->findOneBy(array("cod_ruolo_sog" => TC24RuoloSoggetto::PROGRAMMATORE));
        $programmatore = new SoggettiCollegati($richiesta, $regione);
        $programmatore->setTc24RuoloSoggetto($ruoloProgrammatore);
        $programmatore->setCodUniIpa(SoggettiCollegati::COD_UNI_IPA_ER);
        $richiesta->addMonSoggettiCorrelati($programmatore);

        $ruoloSoggettoBeneficiario = $ruoloRepository->findOneBy(array("cod_ruolo_sog" => TC24RuoloSoggetto::BENEFICIARIO));
        $soggetto = $richiesta->getSoggetto();
        $beneficiario = new SoggettiCollegati($richiesta, $soggetto);
        $beneficiario->setTc24RuoloSoggetto($ruoloSoggettoBeneficiario);
        $richiesta->addMonSoggettiCorrelati($beneficiario);
    }

    /**
     * @param Richiesta $richiesta
     */
    protected function popolaLocalizzazioneGeografica(Richiesta $richiesta) {
        $localizzazioneGeograficaMon = new LocalizzazioneGeografica();
        $localizzazioneGeograficaMon->setRichiesta($richiesta);

        $mandatario = $richiesta->getMandatario();
        if ($mandatario->getSedeLegaleComeOperativa() || $mandatario->getSedi()->isEmpty()) {
            $soggetto = $mandatario->getSoggetto();
            $comune = $soggetto->getComune();

            $localizzazioneGeograficaTC16 = $comune->getTc16LocalizzazioneGeografica();

            if (!is_null($localizzazioneGeograficaTC16)) {
                $localizzazioneGeograficaMon->setLocalizzazione($localizzazioneGeograficaTC16);
            } else {
                $altro = $this->getEm()->getRepository('MonitoraggioBundle:TC16LocalizzazioneGeografica')->findOneById(10808);
                $localizzazioneGeograficaMon->setLocalizzazione($altro);
            }

            $localizzazioneGeograficaMon->setIndirizzo($soggetto->getVia() . ", " . $soggetto->getCivico());
            $localizzazioneGeograficaMon->setCap($soggetto->getCap());
        } else {
            $sediOperative = $mandatario->getSedi()->first();
            $sede = $sediOperative->getSede();

            $indirizzo = $sede->getIndirizzo();
            $comune = $indirizzo->getComune();

            $localizzazioneGeograficaTC16 = $comune->getTc16LocalizzazioneGeografica();
            
            $localizzazioneGeograficaMon->setLocalizzazione($localizzazioneGeograficaTC16);

            $localizzazioneGeograficaMon->setIndirizzo($indirizzo->getVia() . ", " . $indirizzo->getNumeroCivico());
            $localizzazioneGeograficaMon->setCap($indirizzo->getCap());
        }
        $richiesta->addMonLocalizzazioneGeografica($localizzazioneGeograficaMon);
    }

    /**
     * @param Richiesta $richiesta
     */
    protected function popolaStatoInizialeAttuazioneProgetto(Richiesta $richiesta) {
        $statoAttuazioneProgettoMon = new RichiestaStatoAttuazioneProgetto();
        $statoAttuazioneProgettoMon->setRichiesta($richiesta);

        // la data deve essere aggiornata ad ogni rilevazione (FINE BIMESTRE PRECEDENTE) (una sola volta)
        $dataRiferimento = new \DateTime('last day of previous month');
        $statoAttuazioneProgettoMon->setDataRiferimento($dataRiferimento);

        $statoInizialeProgetto = $this->getEm()->getRepository("MonitoraggioBundle:TC47StatoProgetto")
                ->findOneBy(array(
            "stato_progetto" => TC47StatoProgetto::CODICE_IN_CORSO_ESECUZIONE
        ));
        $statoAttuazioneProgettoMon->setStatoProgetto($statoInizialeProgetto);

        $richiesta->addMonStatoProgetti($statoAttuazioneProgettoMon);
    }

    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     *
     * Metodo richiamato contestualmente al passaggio della richiesta in istruttoria, tramite il pulsante VALIDA
     * serve per popolare automaticamente L'IMPORTO REALIZZATO e DA REALIZZARE suddiviso per ANNO; utile ai fini del monitoraggio
     */
    protected function popolaPianoCosti(Richiesta $richiesta) {

        // DESTINAZIONE
        $pianoCostiMon = new RichiestaPianoCosti();

        // RICHIESTA
        $pianoCostiMon->setRichiesta($richiesta);

        $importoDaRealizzare = $richiesta->getIstruttoria()->getCostoAmmesso();

        $pianoCostiMon->setAnnoPiano(date("Y"));  // data_avvio di ATTUAZIONE_CONTROLLO_RICHIESTE è VUOTA,
        // Impostare per TUTTI I PROGETTI la DATA IMPEGNO (in fase di sviluppo); imposto ANNO_CORRENTE

        $pianoCostiMon->setImportoRealizzato(0.00);  // 0.00 inizialmente

        $pianoCostiMon->setImportoDaRealizzare($importoDaRealizzare);   // ISTRUTTORIE_RICHIESTE --> COSTO AMMESSO
        // setto la DESTINAZIONE nella richiesta
        $richiesta->addMonPianoCosti($pianoCostiMon);
    }

    /**
     * @param Richiesta $richiesta
     */
    protected function popolaVociSpesa(Richiesta $richiesta) {

        $vociPianoCosto = $richiesta->getVociPianoCosto();
        $bufferVociSpesa = array();
        $importiVoceSpesa = array();

        foreach ($vociPianoCosto as $vocePianoCosto) {
            $importo = 0.0;
            $istruttoriaVocePianoCosto = $vocePianoCosto->getIstruttoria();
            if (!\is_null($istruttoriaVocePianoCosto)) {
                $importo = $istruttoriaVocePianoCosto->sommaImporti();
            }

            $tipoVoceSpesa = $vocePianoCosto->getPianoCosto()->getMonVoceSpesa();
            if (\is_null($tipoVoceSpesa)) {
                continue;
            }
            $codiceVoceSpesa = $tipoVoceSpesa->getVoceSpesa();
            if (!\array_key_exists($codiceVoceSpesa, $bufferVociSpesa)) {
                $bufferVociSpesa[$codiceVoceSpesa] = $tipoVoceSpesa;
                $importiVoceSpesa[$codiceVoceSpesa] = 0.0;
            }

            $importiVoceSpesa[$codiceVoceSpesa] += $importo;
        }
        foreach ($bufferVociSpesa as $codice => $tipoVoceSpesa) {
            $voceSpesaMon = new VoceSpesa();
            $voceSpesaMon->setRichiesta($richiesta);
            $voceSpesaMon->setTipoVoceSpesa($tipoVoceSpesa);
            $voceSpesaMon->setImporto($importiVoceSpesa[$codice]);

            $richiesta->addMonVoceSpesa($voceSpesaMon);
        }
    }

    public function getEmailATCConfig($istruttoria) {
        return array('to' => $istruttoria->getRichiesta()->getMandatario()->getSoggetto()->getEmailPec(), 'tipo' => 'pec');
    }

    public function isEsitoFinalePositivoEmettibile($istruttoria_richiesta) {
        $esito = new \RichiesteBundle\Utility\EsitoValidazione();
        $esito->setEsito(true);

        $this->controlliPianoCosto($istruttoria_richiesta, $esito);

        return $esito;
    }

    protected function controlliPianoCosto($istruttoria_richiesta, $esito) {
        $richiesta = $istruttoria_richiesta->getRichiesta();

        $annualita = $this->container->get('gestore_piano_costo')->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());

        if (count($annualita) == 0) {
            return;
        }

        $voci_piano_costo = $richiesta->getVociPianoCosto();
        $istruttoria_voce_piano_costo = $voci_piano_costo[0]->getIstruttoria();

        if (is_null($istruttoria_voce_piano_costo)) {
            $esito->setEsito(false);
            $esito->addMessaggio('Prima di dare esito positivo è necessario istruire tutte le annualità del piano costi');

            return;
        }

        for ($i = 1; $i <= count($annualita); ++$i) {
            $nome_metodo = 'getImportoAmmissibileAnno' . $i;
            if (is_null($istruttoria_voce_piano_costo->$nome_metodo())) {
                $esito->setEsito(false);
                $esito->addMessaggio('Prima di dare esito positivo è necessario istruire tutte le annualità del piano costi');
                break;
            }
        }
    }

    public function creaIntegrazione($id_valutazione_checklist) {
        $valutazione_checklist = $this->getEm()->getRepository("IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria")->find($id_valutazione_checklist);
        $istruttoria = $valutazione_checklist->getIstruttoria();
        $procedura = $istruttoria->getProcedura();
        $responsabile = $procedura->getResponsabile()->getPersona();

        if ($istruttoria->getSospesa()) {
            $this->addFlash('error', "Impossibile richiedere un'integrazione quando l'istruttoria è sospesa");

            return new GestoreResponse($this->redirect($this->generateUrl('valuta_checklist_istruttoria', array('id_valutazione_checklist' => $id_valutazione_checklist))));
        }

        if (!is_null($valutazione_checklist->getIstruttoria()->getEsito())) {
            $this->addFlash('error', "Impossibile richiedere un'integrazione quando è stato già emesso l'esito");

            return new GestoreResponse($this->redirect($this->generateUrl('valuta_checklist_istruttoria', array('id_valutazione_checklist' => $id_valutazione_checklist))));
        }

        $integrazione_istruttoria = new \IstruttorieBundle\Entity\IntegrazioneIstruttoria();
        $integrazione_istruttoria->setIstruttoria($istruttoria);
        $integrazione_istruttoria->setValutazioneChecklist($valutazione_checklist);

        $testo_default = "Buongiorno, in allegato l’elenco delle integrazioni richieste" . PHP_EOL . "Il Responsabile del procedimento " . $responsabile->getNome() . " " . $responsabile->getCognome();
        $integrazione_istruttoria->setTestoEmail($testo_default);

        $istruttoria->setSospesa(true);
        $this->getEm()->persist($integrazione_istruttoria);

        $risposta = new \IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria();

        $risposta->setIntegrazione($integrazione_istruttoria);
        $risposta->setPresaVisione(false);

        $this->container->get('sfinge.stati')->avanzaStato($integrazione_istruttoria, \BaseBundle\Entity\StatoIntegrazione::INT_INSERITA);
        $this->container->get('sfinge.stati')->avanzaStato($risposta, \BaseBundle\Entity\StatoIntegrazione::INT_INSERITA);

        $richiesta = $istruttoria->getRichiesta();

        $tipologie_documenti_richiesta = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('procedura' => $richiesta->getProcedura(), 'tipologia' => 'richiesta'));

        foreach ($tipologie_documenti_richiesta as $tipologia_documento_richiesta) {
            $integrazione_documento = new \IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento();
            $integrazione_documento->setIntegrazione($integrazione_istruttoria);
            $integrazione_documento->setTipologiaDocumento($tipologia_documento_richiesta);
            $integrazione_istruttoria->addTipologiaDocumento($integrazione_documento);
        }

        $tipologie_documenti_proponente = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('procedura' => $richiesta->getProcedura(), 'tipologia' => 'proponente'));
        if (count($tipologie_documenti_proponente) > 0) {
            foreach ($richiesta->getProponenti() as $proponente) {
                foreach ($tipologie_documenti_proponente as $tipologia_documento_richiesta) {
                    $integrazione_documento = new \IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento();
                    $integrazione_documento->setIntegrazione($integrazione_istruttoria);
                    $integrazione_documento->setTipologiaDocumento($tipologia_documento_richiesta);
                    $integrazione_documento->setProponente($proponente);
                    $integrazione_istruttoria->addTipologiaDocumento($integrazione_documento);
                }
            }
        }

        /**
         * richiesta possibilità di caricare un qualsiasi documento.
         */
        $tipologiaIntegrazioneGenerico = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array('codice' => 'INTEGRAZIONE_GENERICO'));
        $integrazione_documento = new \IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento();
        $integrazione_documento->setIntegrazione($integrazione_istruttoria);
        $integrazione_documento->setTipologiaDocumento($tipologiaIntegrazioneGenerico);
        $integrazione_istruttoria->addTipologiaDocumento($integrazione_documento);

        $integrazione_istruttoria->setData(new \DateTime());

        $form_options = array('url_indietro' => $this->generateUrl('valuta_checklist_istruttoria', array('id_valutazione_checklist' => $id_valutazione_checklist)));

        $form = $this->createForm("IstruttorieBundle\Form\IntegrazioneType", $integrazione_istruttoria, $form_options);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            foreach ($integrazione_istruttoria->getTipologieDocumenti() as $tipologia_documento) {
                if (!$tipologia_documento->getSelezionato() && !is_null($tipologia_documento->getNota())) {
                    $form->addError(new \Symfony\Component\Form\FormError('Non è possibile specificare una nota per un documento non selezionato'));
                    break;
                } elseif (!$tipologia_documento->getSelezionato()) {
                    $integrazione_istruttoria->getTipologieDocumenti()->removeElement($tipologia_documento);
                }
            }

            $validazione = $this->validaIntegrazione($integrazione_istruttoria);
            if (!$validazione->getEsito()) {
                foreach ($validazione->getMessaggi() as $messaggio) {
                    $form->addError(new \Symfony\Component\Form\FormError($messaggio));
                }
            }

            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->beginTransaction();
                    if ($form->get("pulsanti")->get("pulsante_invio")->isClicked()) {
                        $this->generaPdfIntegrazione($integrazione_istruttoria);
                        $this->container->get('sfinge.stati')->avanzaStato($integrazione_istruttoria, \BaseBundle\Entity\StatoIntegrazione::INT_INVIATA_PA);
                        $em->flush();

                        if ($this->container->getParameter('stacca_protocollo_al_volo')) {
                            $richiestaProtocollo = $this->container->get('docerinitprotocollazione')->setTabProtocollazione($integrazione_istruttoria->getId(), 'INTEGRAZIONE');

                            /*
                             * schedulo un invio email per protocollazione in uscita tramite egrammata
                             * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                             * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno
                             * l'overwrite del metodo creaIntegrazione
                             */
                            /*                             * ********************************************************************** * */
                            if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                            }
                            /*                             * ********************************************************************** * */
                        }
                        $em->commit();
                        $this->addFlash('success', "Integrazione inviata con successo");
                        return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_integrazione_istruttoria_pa', array('id_integrazione_istruttoria' => $integrazione_istruttoria->getId()))));
                    } else {
                        $em->flush();
                        $em->commit();
                        $this->addFlash('success', "Integrazione salvata con successo");
                        return new GestoreResponse($this->redirect($this->generateUrl('gestione_integrazione_istruttoria_pa', array('id_integrazione_istruttoria' => $integrazione_istruttoria->getId()))));
                    }
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati = array();
        $dati['form'] = $form->createView();

        $twig = 'IstruttorieBundle:Integrazione:creaIntegrazione.html.twig';

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    public function gestioneIntegrazione($id_integrazione) {
        $integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione);
        $istruttoria = $integrazione_istruttoria->getIstruttoria();

        $richiesta = $istruttoria->getRichiesta();

        $tipologie_documenti_richiesta = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('procedura' => $richiesta->getProcedura(), 'tipologia' => 'richiesta'));


        $documenti_indicizzati = array();

        if (!is_null($integrazione_istruttoria->getTipologieDocumenti())) {
            foreach ($integrazione_istruttoria->getTipologieDocumenti() as $documento) {
                $documenti_indicizzati[$documento->getTipologiaDocumento()->getCodice()] = $documento;
            }
        }

        foreach ($tipologie_documenti_richiesta as $tipologia_documento_richiesta) {
            $integrazione_documento = new \IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento();
            $integrazione_documento->setIntegrazione($integrazione_istruttoria);
            $integrazione_documento->setTipologiaDocumento($tipologia_documento_richiesta);
            // $integrazione_istruttoria->addTipologiaDocumento($integrazione_documento);
            if (isset($documenti_indicizzati[$tipologia_documento_richiesta->getCodice()])) {
                $integrazione_documento->setSelezionato(true);
                $integrazione_documento->setNota($documenti_indicizzati[$tipologia_documento_richiesta->getCodice()]->getNota());
            }
            $integrazione_istruttoria->addTipologieDocumentiEstesi($integrazione_documento);
        }

        $tipologie_documenti_proponente = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('procedura' => $richiesta->getProcedura(), 'tipologia' => 'proponente'));
        if (count($tipologie_documenti_proponente) > 0) {
            foreach ($richiesta->getProponenti() as $proponente) {
                foreach ($tipologie_documenti_proponente as $tipologia_documento_richiesta) {
                    $integrazione_documento = new \IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento();
                    $integrazione_documento->setIntegrazione($integrazione_istruttoria);
                    $integrazione_documento->setTipologiaDocumento($tipologia_documento_richiesta);
                    // $integrazione_istruttoria->addTipologiaDocumento($integrazione_documento);
                    $integrazione_documento->setProponente($proponente);
                    if (isset($documenti_indicizzati[$tipologia_documento_richiesta->getCodice()])) {
                        $integrazione_documento->setSelezionato(true);
                        $integrazione_documento->setNota($documenti_indicizzati[$tipologia_documento_richiesta->getCodice()]->getNota());
                    }
                    $integrazione_istruttoria->addTipologieDocumentiEstesi($integrazione_documento);
                }
            }
        }

        /**
         * richiesta possibilità di caricare un qualsiasi documento.
         */
        $tipologiaIntegrazioneGenerico = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(array('codice' => 'INTEGRAZIONE_GENERICO'));
        $integrazione_documento = new \IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento();
        $integrazione_documento->setIntegrazione($integrazione_istruttoria);
        $integrazione_documento->setTipologiaDocumento($tipologiaIntegrazioneGenerico);
        //$integrazione_istruttoria->addTipologiaDocumento($integrazione_documento);
        if (isset($documenti_indicizzati['INTEGRAZIONE_GENERICO'])) {
            $integrazione_documento->setSelezionato(true);
            $integrazione_documento->setNota($documenti_indicizzati['INTEGRAZIONE_GENERICO']->getNota());
        }
        $integrazione_istruttoria->addTipologieDocumentiEstesi($integrazione_documento);

        $request = $this->getCurrentRequest();
        $referer = $request->server->get('HTTP_REFERER');
        $indietro = $this->generateUrl('gestione_integrazione_istruttoria_pa', ['id_integrazione_istruttoria' => $integrazione_istruttoria->getId()]);
        $form_options = [
            'url_indietro' => $referer || substr_compare($referer, $indietro, -strlen($indietro)) === 0 ?
            $this->generateUrl('elenco_comunicazioni', ['id_istruttoria' => $istruttoria->getId()]) :
            $indietro
        ];

        $form = $this->createForm("IstruttorieBundle\Form\IntegrazioneGestioneType", $integrazione_istruttoria, $form_options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            foreach ($form->get("tipologie_documenti_estesi")->all() as $documento) {
                $tipologia_documento = $documento->getData();
                if (!$tipologia_documento->getSelezionato() && !is_null($tipologia_documento->getNota())) {
                    $form->addError(new \Symfony\Component\Form\FormError('Non è possibile specificare una nota per un documento non selezionato'));
                    break;
                }
            }

            $validazione = $this->validaIntegrazione($integrazione_istruttoria);
            if (!$validazione->getEsito()) {
                foreach ($validazione->getMessaggi() as $messaggio) {
                    $form->addError(new \Symfony\Component\Form\FormError($messaggio));
                }
            }

            if ($form->isValid()) {

                $em = $this->getEm();
                foreach ($form->get("tipologie_documenti_estesi")->all() as $documento) {
                    $tipologia_documento_esteso = $documento->getData();

                    if (isset($documenti_indicizzati[$tipologia_documento_esteso->getTipologiaDocumento()->getCodice()])) {
                        $tipologia_documento_nuovo = $documenti_indicizzati[$tipologia_documento_esteso->getTipologiaDocumento()->getCodice()];
                        $tipologia_documento_nuovo->setNota($tipologia_documento_esteso->getNota());
                        if (!$tipologia_documento_esteso->getSelezionato()) {
                            $em->remove($tipologia_documento_nuovo);
                        }
                    } elseif ($tipologia_documento_esteso->getSelezionato()) {
                        $tipologia_documento_nuovo = new \IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento();
                        $tipologia_documento_nuovo->setSelezionato(true);
                        $tipologia_documento_nuovo->setIntegrazione($integrazione_istruttoria);
                        $tipologia_documento_nuovo->setTipologiaDocumento($tipologia_documento_esteso->getTipologiaDocumento());
                        $integrazione_istruttoria->addTipologiaDocumento($tipologia_documento_nuovo);
                        $tipologia_documento_nuovo->setNota($tipologia_documento_esteso->getNota());
                    }
                }

                try {
                    $em->beginTransaction();
                    if ($form->get("pulsanti")->get("pulsante_invio")->isClicked()) {
                        $this->generaPdfIntegrazione($integrazione_istruttoria);
                        $this->container->get('sfinge.stati')->avanzaStato($integrazione_istruttoria, \BaseBundle\Entity\StatoIntegrazione::INT_INVIATA_PA);
                        $em->flush();

                        if ($this->container->getParameter('stacca_protocollo_al_volo')) {
                            $richiestaProtocollo = $this->container->get('docerinitprotocollazione')->setTabProtocollazione($integrazione_istruttoria->getId(), 'INTEGRAZIONE');

                            /*
                             * schedulo un invio email per protocollazione in uscita tramite egrammata
                             * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                             * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno
                             * l'overwrite del metodo creaIntegrazione
                             */
                            /*                             * ********************************************************************** * */
                            if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                            }
                            /*                             * ********************************************************************** * */
                        }
                        $em->commit();
                        $this->addFlash('success', "Integrazione inviata con successo");
                        return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_integrazione_istruttoria_pa', array('id_integrazione_istruttoria' => $integrazione_istruttoria->getId()))));
                    } else {
                        $em->flush();
                        $em->commit();
                        $this->addFlash('success', "Integrazione salvata con successo");
                        // return new GestoreResponse($this->redirect($this->generateUrl('gestione_integrazione_istruttoria_pa', array('id_integrazione_istruttoria' => $integrazione_istruttoria->getId()))));
                    }
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati = array();
        $dati['form'] = $form->createView();
        $dati['integrazione'] = $integrazione_istruttoria;

        $twig = 'IstruttorieBundle:Integrazione:gestioneIntegrazione.html.twig';

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    /**
     * @param int $id_integrazione_istruttoria
     * @return GestoreResponse
     */
    public function eliminaIntegrazioneIstruttoria(int $id_integrazione_istruttoria) {
        $em = $this->getEm();
        $integrazione_istruttoria = $em->getRepository("IstruttorieBundle:IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);

        if (!$integrazione_istruttoria->isEliminabile()) {
            return new GestoreResponse($this->addErrorRedirect('Integrazione non eliminabile perchè già protocollata', 'elenco_comunicazioni', array('id_istruttoria' => $integrazione_istruttoria->getIstruttoria()->getId())));
        }

        try {
            $em->remove($integrazione_istruttoria);
            foreach ($integrazione_istruttoria->getTipologieDocumenti() as $documento) {
                $em->remove($documento);
            }
            $em->remove($integrazione_istruttoria->getRisposta());

            // Tolgo la sospensione dell'istruttoria
            $integrazione_istruttoria->getIstruttoria()->setSospesa(false);
            $em->flush();

            return new GestoreResponse($this->addSuccesRedirect('Integrazione eliminata correttamente', 'elenco_comunicazioni', array('id_istruttoria' => $integrazione_istruttoria->getIstruttoria()->getId())));
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    public function validaIntegrazione($integrazione) {
        $esito = new \RichiesteBundle\Utility\EsitoValidazione();
        $esito->setEsito(true);

        if ($integrazione->getTipologieDocumenti()->count() == 0 && is_null($integrazione->getTesto())) {
            $esito->setEsito(false);
            $esito->addMessaggio('Deve essere selezionato almeno un documento o inserita una nota nella richiesta di integrazione');
        }

        return $esito;
    }

    public function inviaEmailIntegrazione($integrazione_istruttoria) {
        $email = $integrazione_istruttoria->getIstruttoria()->getRichiesta()->getMandatario()->getSoggetto()->getEmailPec();

        if (is_null($email)) {
            $esito = new \stdClass();
            $esito->res = false;
            $esito->error = 'Indirizzo pec non disponibile';

            return $esito;
        }

        $subject = '';
        $renderViewTwig = 'IstruttorieBundle:Integrazione:integrazione.email.html.twig';
        $parametriView = array('integrazione_istruttoria' => $integrazione_istruttoria);
        $noHtmlViewTwig = 'IstruttorieBundle:Integrazione:integrazione.email.twig';

        $esito = $this->container->get('messaggi.email')->inviaEmail(array($email), 'pec', $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig);
    }

    public function generaPdfIntegrazione($integrazione_istruttoria, $facsimile = false) {
        $pdf = $this->container->get('pdf');

        $dati['integrazione_istruttoria'] = $integrazione_istruttoria;
        $dati['richiesta'] = $integrazione_istruttoria->getIstruttoria()->getRichiesta();
        $dati['facsimile'] = $facsimile;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($integrazione_istruttoria->getIstruttoria()->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
        $twig = 'IstruttorieBundle:Integrazione:pdfIntegrazione.html.twig';
        $pdf->load($twig, $dati);

        $data = $pdf->binaryData();
        $data_corrente = new \DateTime();
        if ($facsimile == false) {
            $tipoDocumento = $this->getEm()->getRepository('DocumentoBundle:TipologiaDocumento')->findOneByCodice('RICHIESTA_INTEGRAZIONE_RICHIESTA');
            $documentoRichiesta = $this->container->get('documenti')->caricaDaByteArray($data, "Integrazione_{$integrazione_istruttoria->getIstruttoria()->getId()}_{$data_corrente->format('Y-m-d')}.pdf", $tipoDocumento);
            $integrazione_istruttoria->setDocumento($documentoRichiesta);
        } else {
            return $pdf->download("Integrazione_{$integrazione_istruttoria->getIstruttoria()->getId()}_{$data_corrente->format('Y-m-d')}.pdf");
        }
    }

    public function esitoComunicazioneIstruttoria($istruttoria) {

        $em = $this->getEm();
        $procedura = $istruttoria->getProcedura();
        $request = $this->getCurrentRequest();
        $responsabile = $procedura->getResponsabile()->getPersona();
        /* Se l'esito non esiste lo creo */
        $comunicazioni_esiti = $istruttoria->getComunicazioniEsiti();

        if (is_null($istruttoria->getEsito())) {
            $this->addFlash("error", "Impossibile inviare una comunicazione se non è stato emesso un esito");
            return new GestoreResponse($this->redirect($this->generateUrl("elenco_comunicazioni", array("id_istruttoria" => $istruttoria->getId()))));
        }
        if (!is_null($istruttoria->getEsito()) && $istruttoria->getEsito()->getCodice() == 'SOSPESO') {
            $this->addFlash("error", "Impossibile inviare una comunicazione se l'istruttoria è sospesa");
            return new GestoreResponse($this->redirect($this->generateUrl("elenco_comunicazioni", array("id_istruttoria" => $istruttoria->getId()))));
        }

        if (count($comunicazioni_esiti) == 0) {
            $comunicazione_esito = new \IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria();

            /*             * * TESTO DELL'EMAIL DI DEFAULT ** */
            $testo_default = 'Con la presente si trasmette la documentazione allegata. '
                    . PHP_EOL . 'Il responsabile del procedimento ' . $responsabile->getNome() . " " . $responsabile->getCognome();
            $comunicazione_esito->setTestoEmail($testo_default);
            $comunicazione_esito->setData(new \DateTime());
            $comunicazione_esito->setRispondibile(false);
            /*             * ******************************** */

            $comunicazione_esito->setIstruttoria($istruttoria);
            $this->container->get("sfinge.stati")->avanzaStato($comunicazione_esito, \BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria::ESI_INSERITA);
            $em->persist($comunicazione_esito);
            $em->flush();
        } else {
            // se è stato settato $esito_istruttoria_pagamento è una PersistantCollection... per cui:
            $comunicazione_esito = $comunicazioni_esiti[0];
        }

        $indietro = $this->generateUrl("elenco_comunicazioni", array("id_istruttoria" => $istruttoria->getId()));

        $documento_comunicazione_esito = new \IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoriaDocumento();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();
        $documento_comunicazione_esito->setDocumentoFile($documento_file);
        $documento_comunicazione_esito->setComunicazione($comunicazione_esito);

        $documenti_caricati = $comunicazione_esito->getDocumentiComunicazione();

        $listaTipi = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findByTipologia('esito_istruttoria');

        // Se lo stato è inviato/protocollato
        $disabilita_azioni = ($comunicazione_esito->getStato() != \BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria::ESI_INSERITA);

        if ($disabilita_azioni) {
            $msg = $comunicazione_esito->getStato()->getDescrizione();
            if (($comunicazione_esito->getProtocolloEsitoIstruttoria() != '-') && !is_null($comunicazione_esito->getDataProtocolloEsitoIstruttoria())) {
                $msg .= ' [Protocollo N° ' . $comunicazione_esito->getProtocolloEsitoIstruttoria() . ' del ' . date_format($comunicazione_esito->getDataProtocolloEsitoIstruttoria(), 'd/m/Y') . ']';
            }
            $this->addFlash("success", $msg);
        }

        if (count($listaTipi) > 0) {

            $opzioni_form_documenti["lista_tipi"] = $listaTipi;
            $opzioni_form_documenti["url_indietro"] = $indietro;
            $opzioni_form_documenti["disabled"] = $disabilita_azioni;
            $form_documenti = $this->createForm('IstruttorieBundle\Form\ComunicazioneEsitoIstruttoriaDocumentoType', $documento_comunicazione_esito, $opzioni_form_documenti);

            $opzioni_form_esito["url_indietro"] = $indietro;
            $opzioni_form_esito["disabled"] = $disabilita_azioni;
            $form_esito = $this->createForm('IstruttorieBundle\Form\ComunicazioneEsitoIstruttoriaType', $comunicazione_esito, $opzioni_form_esito);

            if ($request->isMethod('POST')) {

                $form_documenti->handleRequest($request);
                $form_esito->handleRequest($request);

                if ($form_documenti->isSubmitted() && $form_documenti->isValid()) {
                    try {
                        $this->container->get("documenti")->carica($documento_file);
                        $em->persist($documento_comunicazione_esito);
                        $em->flush();
                        $this->addFlash("success", "Documento caricato con successo.");
                        //return new GestoreResponse($this->redirect($this->generateUrl('esito_finale_istruttoria_pagamenti', array("id_pagamento" => $pagamento->getId()))));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', "Errore durante il caricamento del documento. Si invita a riprovare. Se il problema persiste contattare l'assistenza");
                    }
                }

                if ($form_esito->isSubmitted() && $form_esito->isValid()) {

                    try {

                        // SALVATAGGIO INFORMAZIONI
                        $em->persist($comunicazione_esito);
                        $em->flush();

                        if ($form_esito->get("pulsanti")->get("pulsante_invio")->isClicked()) {

                            // INVIO

                            $em->beginTransaction();
                            $comunicazione_esito->setDataInvio(new \DateTime());
                            $this->generaPdfComunicazioneEsito($comunicazione_esito);
                            $em->flush();

                            $this->container->get("sfinge.stati")->avanzaStato($comunicazione_esito, \BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria::ESI_INVIATA_PA);
                            $em->flush();

                            if ($this->container->getParameter("stacca_protocollo_al_volo")) {

                                $richiestaProtocollo = $this->container->get("docerinitprotocollazione")->setTabProtocollazioneEsitoIstruttoriaRichiesta($istruttoria, $comunicazione_esito);
                                $em->flush();

                                /**
                                 * schedulo un invio email per protocollazione in uscita tramite egrammata
                                 * l'email verrà mandata solo dopo che lo stato della richiestaProtocollo evolverà in POST_PROTOCOLLAZIONE
                                 * Questo blocco andrà riportato negli eventuali GestoriIstruttoriaBando scritti ad hoc che faranno 
                                 * l'overwrite del metodo creaIntegrazione 
                                 */
                                /*                                 * *********************************************************************** * */
                                if (!$this->schedulaEmailProtocollo($richiestaProtocollo)) {
                                    throw new \Exception('Errore durante la schedulazione dell\'EmailProtocollo');
                                }
                                /*                                 * *********************************************************************** * */
                            }

                            $em->commit();
                            $this->addFlash("success", "Comunicazione esito istruttoria inviata con successo.");

                            // IN CASO DI INVIO RICREO I FORM DISABILITANDO

                            $disabilita_azioni = ($comunicazione_esito->getStato() != \BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria::ESI_INSERITA);

                            $opzioni_form_documenti["lista_tipi"] = $listaTipi;
                            $opzioni_form_documenti["url_indietro"] = $indietro;
                            $opzioni_form_documenti["disabled"] = $disabilita_azioni;
                            $form_documenti = $this->createForm('IstruttorieBundle\Form\ComunicazioneEsitoIstruttoriaDocumentoType', $documento_comunicazione_esito, $opzioni_form_documenti);

                            $opzioni_form_esito["url_indietro"] = $indietro;
                            $opzioni_form_esito["disabled"] = $disabilita_azioni;
                            $form_esito = $this->createForm('IstruttorieBundle\Form\ComunicazioneEsitoIstruttoriaType', $comunicazione_esito, $opzioni_form_esito);
                        }

                        if ($form_esito->get("pulsanti")->get("pulsante_submit")->isClicked()) {
                            // SALVA
                            $this->addFlash("success", "Comunicazione esito istruttoria salvata con successo.");
                        }
                    } catch (ResponseException $e) {
                        if ($form_esito->get("pulsanti")->get("pulsante_invio")->isClicked()) {
                            $em->rollback();
                        }
                        $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                    }
                }
            }

            $form_documenti_view = $form_documenti->createView();
            $form_esito_view = $form_esito->createView();
        } else {
            $form_documenti_view = null;
            $form_esito_view = null;
        }

        $dati = array(
            "istruttoria" => $istruttoria,
            "comunicazione" => $comunicazione_esito,
            "menu" => 'comunicazioni',
            "documenti" => $documenti_caricati,
            //"proponente" => $proponente,
            "form_documenti" => $form_documenti_view,
            "form_esito" => $form_esito_view,
            "url_indietro" => $indietro,
            "disabilita_azioni" => $disabilita_azioni,
            "documenti_richiesti" => $listaTipi
        );

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Riepilogo richiesta", $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $istruttoria->getRichiesta()->getId())));

        $response = $this->render("IstruttorieBundle:Istruttoria:comunicazioneEsito.html.twig", $dati);

        return new GestoreResponse($response);
        //return new GestoreResponse($response);
    }

    public function eliminaDocumentoComunicazioneEsito($id_documento_esito_istruttoria, $istruttoria, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoriaDocumento")->find($id_documento_esito_istruttoria);

        try {
            $this->container->get("documenti")->cancella($documento->getDocumentoFile(), 0);
            $em->remove($documento);
            $em->flush();
            $this->addFlash("success", "Documento eliminato correttamente");
        } catch (ResponseException $e) {
            $this->addFlash('error', "Errore nell'eliminazione del documento");
        }

        return $this->redirect($this->generateUrl('comunicazione_esito', array("id_istruttoria" => $istruttoria->getId())));
    }

    public function generaATC($istruttoria_richiesta) {
        $data_limite_accettazione = new \DateTime();
        $data_limite_accettazione->modify("+600 days");
        $atc = new \AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta();
        $atc->setDataLimiteAccettazione($data_limite_accettazione);

        return $atc;
    }

    public function eliminaDocumentoIstruttoria($id_documento, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("IstruttorieBundle\Entity\DocumentoIstruttoria")->find($id_documento);
        $id_richiesta = $documento->getRichiesta()->getId();
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();

            return new GestoreResponse($this->addSuccesRedirect('Documento eliminato correttamente', 'documenti_richiesta_istruttoria', array('id_richiesta' => $id_richiesta)));
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * Schedula l'invio di una email tramite egrammata creando un oggetto EmailProtocollo associato alla richiesta protocollo.
     *
     * N.B. Ogni classe figlia di RichiestaProtocollo per cui viene scedulata un invio email DEVE implementare la EmailSendableInterface
     *
     * @param type $richiestaProtocollo
     *
     * @return bool
     */
    protected function schedulaEmailProtocollo($richiestaProtocollo) {
        /* @var $egrammataService \ProtocollazioneBundle\Service\EGrammataWsService */
        $egrammataService = $this->container->get('egrammata_ws');

        return $egrammataService->creaEmailProtocollo($richiestaProtocollo);
    }

    public function generaPdfIstruttoriaRichiesta($id_richiesta, $opzioni = array()) {
        if (!array_key_exists('twig', $opzioni)) {
            throw new \Exception('Funzionalità non attiva per questo bando');
        }

        $richiesta = $this->getEm()->find('RichiesteBundle\Entity\Richiesta', $id_richiesta);
        if (is_null($richiesta)) {
            throw new \Symfony\Component\Translation\Exception\NotFoundResourceException('Richiesta non trovata');
        }

        $pdf = $this->container->get('pdf');
        $dati = $opzioni;
        $dati['richiesta'] = $richiesta;
        $dati['facsimile'] = false;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($integrazione_istruttoria->getIstruttoria()->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf->load($opzioni['twig'], $dati);

        //return $this->render($opzioni['twig'],$dati);

        $date = new \DateTime();
        $data = $date->format('d-m-Y');

        return $pdf->download('Fascicolo Istruttorio' . $richiesta->getId() . ' ' . $data);
    }

    public function getProponenteMandatario($id_richiesta) {
        $richiesta = $this->getEm()->find('RichiesteBundle\Entity\Richiesta', $id_richiesta);
        $proponenti = $richiesta->getProponenti();
        foreach ($proponenti as $proponente) {
            if ($proponente->getMandatario() == true) {
                return $proponente;
            }
        }
    }

    public function getSezioniCheckList($id_checklist) {
        return $this->getEm()->getRepository('IstruttorieBundle:ChecklistIstruttoria')->getSezioniDaCheckList($id_checklist);
    }

    public function getElementiChecklist($id_checklist) {
        return $this->getEm()->getRepository('IstruttorieBundle:ChecklistIstruttoria')->getDistinctElementiDaCheckList($id_checklist);
    }

    public function nucleoIstruttoria($id_richiesta) {
        $dati = array();
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException('Risorsa non trovata');
        }

        $istruttoria = $richiesta->getIstruttoria();
        if (is_null($istruttoria)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $tipologiaDocumenti = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array('tipologia' => 'verbale_nucleo_istruttoria', 'codice' => 'VERBALE_NUCLEO_ISTRUTTORIA'));

        $nucleo = $istruttoria->getNucleoIstruttoria();
        if (is_null($nucleo)) {
            $nucleo = new NucleoIstruttoria();
            $nucleo->setIstruttoriaRichiesta($istruttoria);
        }

        $documentoFile = new DocumentoFile();
        $documento = new DocumentoNucleoIstruttoria();
        $documento->setDocumentoFile($documentoFile);

        $documentiCaricati = $nucleo->getDocumentiNucleoIstruttoria();
        $dati['documentiCaricati'] = $documentiCaricati;

        $options['lista_tipi'] = $tipologiaDocumenti;

        $options['url_indietro'] = $this->generateUrl('elenco_richieste_inviate');
        //TODO: Da rivedere criteri protezione accesso
        $options['disabled'] = !$this->isGranted('ROLE_ISTRUTTORE_SUPERVISORE') || !is_null($istruttoria->getEsito());
        $options['disabled'] = false;

        $formDati = $this->createForm("IstruttorieBundle\Form\NucleoIstruttoriaType", $nucleo, $options);
        $formDoc = $this->createForm("IstruttorieBundle\Form\DocumentoNucleoIstruttoriaType", $documento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $formDati->handleRequest($request);
            $formDoc->handleRequest($request);
            $isDoc = $formDoc->isSubmitted();
            $form = $isDoc ? $formDoc : $formDati;

            if (($formDati->isSubmitted() || $formDoc->isSubmitted()) && $form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->beginTransaction();
                    if ($isDoc) {
                        //Doc
                        $this->container->get('documenti')->carica($documentoFile, 0);
                        $documento->setNucleoIstruttoria($nucleo);
                        $em->persist($documento);
                    } else {
                        //Dati
                        $em->persist($nucleo);
                    }

                    $em->flush();
                    $em->commit();
                    $msg = 'Dati salvati correttamente';
                    $this->addFlash('success', $msg);
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException($e->getMessage());
                }
            }
        }

        $dati['menu'] = 'nucleo';
        $dati['formDoc'] = $formDoc->createView();
        $dati['formDati'] = $formDati->createView();
        $dati['istruttoria'] = $istruttoria;
        $dati['richiesta'] = $richiesta;

        return new GestoreResponse(
                $this->render('IstruttorieBundle:Istruttoria:nucleo.html.twig', $dati)
        );
    }

    public function eliminaDocumentoNucleoIstruttoria($id_richiesta, $id_documento, $opzioni = array()) {
        $em = $this->getEm();
        $documento = $em->getRepository("IstruttorieBundle\Entity\DocumentoNucleoIstruttoria")->find($id_documento);
        //$id_richiesta = $documento->getRichiesta()->getId();
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();

            return new GestoreResponse($this->addSuccesRedirect(
                            'Documento eliminato correttamente', 'nucleo', array('id_richiesta' => $id_richiesta)
            ));
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    public function generaPdfComunicazioneEsito($comunicazione) {
        $pdf = $this->container->get("pdf");
        $richiesta = $comunicazione->getIstruttoria()->getRichiesta();

        $dati['comunicazione'] = $comunicazione;
        $dati['facsimile'] = false;
        $dati['documenti'] = $comunicazione->getDocumentiComunicazione();
        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
        $twig = "IstruttorieBundle:Istruttoria:pdfComunicazioneEsito.html.twig";
        $pdf->load($twig, $dati);

        $data = $pdf->binaryData();

        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice("COMUNICAZIONE_ESITO_ISTRUTTORIA");
        $data_corrente = new \DateTime();
        $documentoRichiesta = $this->container->get("documenti")->caricaDaByteArray($data, "Comunicazione_esito_{$richiesta->getId()}_{$data_corrente->format('Y-m-d')}.pdf", $tipoDocumento);

        $comunicazione->setDocumento($documentoRichiesta);
    }

    public function generaFacsimileComunicazioneEsito($comunicazione) {

        $richiesta = $comunicazione->getIstruttoria()->getRichiesta();
        $download = true;
        $pdf = $this->container->get("pdf");
        $dati['comunicazione'] = $comunicazione;
        $dati['facsimile'] = true;
        $dati['documenti'] = $comunicazione->getDocumentiComunicazione();

        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
        $twig = "IstruttorieBundle:Istruttoria:pdfComunicazioneEsito.html.twig";
        $pdf->load($twig, $dati);
        //return $this->render($twig,$dati);

        if ($download) {
            $date = new \DateTime();
            $nome_file = "Comunicazione_esito_{$richiesta->getId()}_{$date->format('Y-m-d')}.pdf";
            $pdf->download($nome_file);
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    protected function recuperaTipiCopertura($tipi) {
        $codici = array();
        foreach ($tipi as $tipo) {
            $codici[] = $tipo->getCodice();
        }
        $res = $this->getEm()->getRepository("CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria")->findBy(array("codice" => $codici));
        return $res;
    }

    public function forzaAggiornamentoCL($fase, $istruttoria_richiesta) {
        return true;
    }

    /**
     * @param $id_richiesta
     * @return GestoreResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function aggiornaDocumentiRichiesta($id_richiesta) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();
        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($id_richiesta);
        /** @var Richiesta $richiesta */
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if ($richiesta->getProcedura()->isRichiestaFirmaDigitale()) {
            $domanda = $richiesta->getDocumentoRichiestaFirmato();
        } else {
            $domanda = $richiesta->getDocumentoRichiesta();
        }

        $documenti_proponenti = [];

        foreach ($richiesta->getProponenti() as $proponente) {
            $documenti_proponente = $em->getRepository('RichiesteBundle\Entity\DocumentoProponente')->findDocumentiCaricati($proponente->getId());
            if (count($documenti_proponente) > 0) {
                $documenti_proponenti[] = [
                    'proponente' => $proponente,
                    'documenti' => $documenti_proponente
                ];
            }
        }
        
        $documenti_programmi = [];
        $programmi = $richiesta->getProgrammi();
        if(count($programmi) > 0) {
            foreach ($programmi as $programma) {
                foreach ($programma->getDocumentiProgrammaLegge14() as $documento_programma) {
                    $documenti_programmi[] = $documento_programma;
                }
            }
        }
        

        $documenti_istruttoria = $em->getRepository("IstruttorieBundle\Entity\DocumentoIstruttoria")->findBy(['richiesta' => $richiesta]);
        $listaTipi = $this->getEm()->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->ricercaDocumentiIstruttoria();
        $documento_file = new DocumentoFile();
        $documento_istruttoria = new DocumentoIstruttoria();
        $opzioni_form['lista_tipi'] = $listaTipi;
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
        $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => 'Salva']);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                    $documento_istruttoria->setDocumentoFile($documento_file);
                    $documento_istruttoria->setRichiesta($richiesta);
                    $em->persist($documento_istruttoria);

                    $em->flush();
                    return new GestoreResponse($this->redirectToRoute("documenti_richiesta_istruttoria", ["id_richiesta" => $richiesta->getId()]));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $twig = 'IstruttorieBundle:Istruttoria:documentiRichiesta.html.twig';
        $dati = [
            'documenti' => $documenti_caricati,
            'documenti_istruttoria' => $documenti_istruttoria,
            'domanda' => $domanda,
            'form' => $form->createView(),
            'richiesta' => $richiesta,
            'menu' => 'documenti',
            'documenti_proponenti' => $documenti_proponenti,
            'documenti_programmi' => $documenti_programmi
        ];

        $response = $this->render($twig, $dati);
        return new GestoreResponse($response);
    }

    /**
     * @param $id_richiesta
     * @param array $opzioni
     * @throws \Exception
     */
    public function generaPdfChecklistIstruttoria($id_richiesta, $opzioni = []) {
        if (!array_key_exists('twig', $opzioni)) {
            throw new \Exception('Funzionalità non attiva per questo bando');
        }

        $richiesta = $this->getEm()->find('RichiesteBundle\Entity\Richiesta', $id_richiesta);

        if (is_null($richiesta)) {
            throw new NotFoundResourceException('Richiesta non trovata');
        }
        /** @var PdfWrapper $pdf */
        $pdf = $this->container->get('pdf');
        $dati = $opzioni;
        $dati['richiesta'] = $richiesta;
        $dati['facsimile'] = false;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $pdf->load($opzioni['twig'], $dati);
        
        //Togliere il commente sotto se vuoi la pagina e non il pdf....ottimo per la stesusa iniziale
	//return $this->render($opzioni['twig'], $dati);

        $date = new \DateTime();
        $data = $date->format('d-m-Y');

        $pdf->download('Istruttoria_' . $richiesta->getId() . ' ' . $data);

        // Un controller ritorna sempre una risposta
        return new Response();
    }

    /**
     * @return IstruttoriaRichiesta
     */
    public function sbloccaIstruttoriaRichiesta($id_richiesta) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $istruttoria = $richiesta->getIstruttoria();
        $istruttoria->setSospesa(false);
        try {
            $this->getEm()->persist($istruttoria);
            $this->getEm()->flush();
        } catch (\Exception $e) {
            $this->addFlash('error', "Impossibile effettuare l'operazione richiesta");
            return new GestoreResponse($this->redirect($this->generateUrl('riepilogo_richiesta', array('id_richiesta' => $id_richiesta))));
        }
        $this->addFlash('success', "Istruttoria sloccata con successo");
        return new GestoreResponse($this->redirect($this->generateUrl('riepilogo_richiesta', array('id_richiesta' => $id_richiesta))));
    }

    /**
     * @param IstruttoriaRichiesta $istruttoria
     * @return EsitoValidazione
     */
    public function isEsitoFinaleSbloccabile(IstruttoriaRichiesta $istruttoria): EsitoValidazione
    {
        $esito = new EsitoValidazione();
        $esito->setEsito(true);

        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            $esito->setEsito(false);
            $esito->addMessaggio('Non sei abilitato ad eseguire l’operazione.');
        }

        if (is_null($istruttoria->getEsito())) {
            $esito->setEsito(false);
            $esito->addMessaggio('L’esito finale non è validato, non serve eseguire lo sblocco.');
        }

        if (!is_null($istruttoria->getRichiesta()->getAttuazioneControllo())) {
            $esito->setEsito(false);
            $this->addFlash('error', "La richiesta di contributo è già in attuazione e controllo, pertanto non è possibile eseguire lo sblocco.");
        }

        return $esito;
    }

    /**
     * @param $id_richiesta
     * @return RedirectResponse
     */
    public function sbloccaEsitoFinaleIstruttoria($id_richiesta): RedirectResponse
    {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        $istruttoria = $richiesta->getIstruttoria();
        $esito = $this->isEsitoFinaleSbloccabile($istruttoria);

        if ($esito->getEsito()) {
            try {
                $em = $this->getEm();
                $this->creaLogIstruttoria($istruttoria, 'sblocca_esito_finale');
                $istruttoria->setEsito(null);
                $em->flush();
                $this->addFlash('success', 'Esito finale sbloccato correttamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore nello sblocco dell’istruttoria.');
            }
        } else {
            $this->addFlash('error', implode(' - ', $esito->getMessaggi()));
        }
        return $this->redirectToRoute("esito_finale_istruttoria", ["id_richiesta" => $id_richiesta]);
    }
}
