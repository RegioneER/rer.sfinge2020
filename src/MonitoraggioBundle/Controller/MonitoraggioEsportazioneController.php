<?php

/*
 * @author vbuscemi
 */

namespace MonitoraggioBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use BaseBundle\Exception\SfingeException;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Form\Entity\CaricamentoErroriIgrue;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use MonitoraggioBundle\Form\Entity\RicercaTavolaEsportata;
use MonitoraggioBundle\Form\Entity\RicercaEsportazioneProgetto;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use MonitoraggioBundle\Form\MonitoraggioEsportazioneRichiestaType;

/**
 * Description of MonitoraggioEsportazioneController.
 *
 * @Route("/esportazioni")
 */
class MonitoraggioEsportazioneController extends BaseController {
    const NOME_FILE_LOG_ESPORTAZIONE = 'esportazione_igrue';
    const CODICE_FILE_ERRORE_IGRUE = 'FROM_IGRUE';
    const CODICE_FILE_IMPORTAZIONE = 'IMPORTAZIONE_IGRUE';
    const NUM_ELEMENTI_PAGINA = 10;

    /**
     * @PaginaInfo(titolo="Gestione invii", sottoTitolo="Elenco")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={ "sort": "i.id", "direction": "asc", "page": "1"}, name="elenco_monitoraggio_esportazione")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function elencoEsportazioniAction() {
        \ini_set('memory_limit', '512M');
        $em = $this->getEm();
        $connection = $em->getConnection();

        $ctrl_disabled_esportazioni_in_corso = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findEsportazioneInCorso();

        $task = [];
        $form = $this->createForm('MonitoraggioBundle\Form\ElencoEsportazioniType', $task, ['ctrl_disabled_esportazioni_in_corso' => $ctrl_disabled_esportazioni_in_corso]);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && !$ctrl_disabled_esportazioni_in_corso) {
            try {
                $connection->beginTransaction();
                //Inizializziamo l'esportazione
                $esportazione = new MonitoraggioEsportazione();
                $em->persist($esportazione);
                $em->flush($esportazione);
                $fase_iniziale = new MonitoraggioEsportazioneLogFase($esportazione);
                $esportazione->addFasi($fase_iniziale);

                $em->persist($esportazione);
                $em->flush($esportazione);
                $connection->commit();

                $appDir = $this->get('kernel')->getRootDir();
                $logFile = $appDir . '/logs/' . self::NOME_FILE_LOG_ESPORTAZIONE . '_' . \date('Y-m-d') . '.log';
                $command = PHP_BINDIR . '/php -f ' . $appDir . '/console sfinge:monitoraggio:esportazione ' . $esportazione->getId() . ' -e ' .
                    $this->container->getParameter('kernel.environment');
                $command .= ' >> ' . $logFile . ' 2>> ' . $logFile . ' &';
                \shell_exec('echo "*** Inizio esportazione ID ' . $esportazione->getId() . ' in data ' . \date('d/m/Y H:i:s') . '" >> ' . $logFile);
                \shell_exec($command);

                $this->addSuccessRedirect('Operazione pianificata con successo', 'elenco_monitoraggio_esportazione');
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());
                $this->addError('Errore durante la creazione dell\'esportazione');
            }
        }
        $datiRicerca = new \MonitoraggioBundle\Form\Entity\RicercaEsportazione();
        $esportazioni = $this->container->get('ricerca')->ricerca($datiRicerca);

        $dati = [
            'form' => $form->createView(),
            'risultato' => $esportazioni,
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:elenco.html.twig', $dati);
    }

    /**
     * @PaginaInfo(titolo="Gestione esportazioni", sottoTitolo="Elenco")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/effettua_scarico/{esportazione_id}",  name="monitoraggio_esportazione_scarico")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function scaricoAction($esportazione_id) {
        $esportazione = $this->getEm()->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (is_null($esportazione)) {
            throw new SfingeException('Esportazione non trovata');
        }
        $this->checkCsrf('token', 'token');
        $this->scaricaMonitoraggio($esportazione_id);
        $this->addSuccess('Operazione avviata con successo');

        return $this->redirectToRoute('elenco_monitoraggio_esportazione');
    }

    /**
     * @param int $esportazioneId
     */
    protected function scaricaMonitoraggio($esportazioneId) {
        $appDir = $this->get('kernel')->getRootDir();
        $logFile = $appDir . '/logs/' . self::NOME_FILE_LOG_ESPORTAZIONE . '_' . \date('Y-m-d') . '.log';
        $command = PHP_BINDIR . '/php -f ' . $appDir . '/console sfinge:monitoraggio:scarico ' . $esportazioneId . ' -e ' .
            $this->container->getParameter('kernel.environment');
        $command .= ' >> ' . $logFile . ' 2>> ' . $logFile . ' &';
        \shell_exec('echo "*** Inizio Scarico ID ' . $esportazioneId . ' in data ' . \date('d/m/Y H:i:s') . '" >> ' . $logFile);
        \shell_exec($command);
    }

    /**
     * @Route("/gestione_configurazione/procedura/{esportazione_id}",  name="monitoraggio_esportazione_gestione_procedura_configurazione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @PaginaInfo(titolo="Configurazione esportazione", sottoTitolo="Elenco delle procedure esportabili")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function gestisciConfigurazioneProceduraAction($esportazione_id) {
        $em = $this->getEm();

        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);

        if (is_null($esportazione)) {
            throw new SfingeException("Esportazione $esportazione_id non trovata");
        }
        $formDisabled = false;
        if (MonitoraggioEsportazioneLogFase::STATO_INVIATO == $esportazione->getLastFase()->getFase()) {
            $formDisabled = true;
        }

        //Attenzione soft deletable disattivato!
        $em->getFilters()->disable('softdeleteable');

        $esportazioneMV = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->creaOggettoFormProcedura($esportazione);
        $form = $this->createForm('MonitoraggioBundle\Form\MonitoraggioEsportazioneProceduraType', $esportazioneMV, [
            'url_indietro' => $this->generateUrl('elenco_monitoraggio_esportazione'),
            'esportazioneInviata' => $formDisabled,
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('generaFile')->isClicked()) {
                return $this->redirectToRoute('monitoraggio_genera_file_igrue', ['esportazione_id' => $esportazione_id]);
            }
            $connection = $em->getConnection();
            $connection->beginTransaction();
            try {
                foreach ($esportazioneMV->getMonitoraggioConfigurazione() as $configurazione) {
                    $em->persist($configurazione);
                    $em->flush();
                }
                $connection->commit();
                if ($form->get('salvaAggiorna')->isClicked()) {
                    $this->scaricaMonitoraggio($esportazione->getId());
                }
                $this->addSuccess('Operazione effettuata con successo');
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->addError('Errore durante salvataggio dati');
            }
        }

        //Prendo numero errori per configurazione
        $dql = 'select conf.id id, count(errori) num_errori '
        . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneProcedura conf '
        . 'join conf.monitoraggio_esportazione esportazione '
        . 'join conf.monitoraggio_configurazione_esportazione_errori errori '
        . 'where esportazione in (:esportazione) group by conf';
        $numeroErroriRaw = $em->createQuery($dql)
        ->setParameter('esportazione', $esportazione)
        ->getResult();
        $numeroErrori = [];
        foreach ($numeroErroriRaw as $riga) {
            $numeroErrori[$riga['id']] = $riga['num_errori'];
        }
        $numeroErroriRaw = null;

        $viewdata = [
            'form' => $form->createView(),
            'esportazione' => $esportazione,
            'numero_errori' => $numeroErrori,
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:gestioneConfigurazioneProcedura.html.twig', $viewdata);
    }

    /**
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @PaginaInfo(titolo="Configurazione esportazione", sottoTitolo="Elenco dei trasferimenti esportabili")
     * @Route("/gestione_configurazione/trasferimento/{esportazione_id}",  name="monitoraggio_esportazione_gestione_trasferimento_configurazione")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function gestisciConfigurazioneTrasferimentoAction($esportazione_id) {
        $em = $this->getEm();

        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (is_null($esportazione)) {
            throw new SfingeException("Esportazione $esportazione_id non trovata");
        }
        $formDisabled = false;
        if (MonitoraggioEsportazioneLogFase::STATO_INVIATO == $esportazione->getLastFase()->getFase()) {
            $formDisabled = true;
        }

        $em->getFilters()->disable('softdeleteable');

        $esportazioneMV = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->creaOggettoFormTrasferimento($esportazione);

        $form = $this->createForm('MonitoraggioBundle\Form\MonitoraggioEsportazioneTrasferimentoType', $esportazioneMV, [
            'url_indietro' => $this->generateUrl('elenco_monitoraggio_esportazione'),
            'esportazioneInviata' => $formDisabled,
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('generaFile')->isClicked()) {
                return $this->redirectToRoute('monitoraggio_genera_file_igrue', ['esportazione_id' => $esportazione_id]);
            }
            try {
                $connection = $em->getConnection();
                $connection->beginTransaction();
                foreach ($esportazioneMV->getMonitoraggioConfigurazione() as $configurazione) {
                    $em->persist($configurazione);
                    $em->flush();
                }
                $connection->commit();
                if ($form->get('salvaAggiorna')->isClicked()) {
                    $this->scaricaMonitoraggio($esportazione->getId());
                }
                $this->addSuccess('Operazione effettuata con successo');
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }
        $viewdata = [
            'form' => $form->createView(),
            'esportazione' => $esportazione,
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:gestioneConfigurazioneTrasferimento.html.twig', $viewdata);
    }

    /** @Route("/gestione_configurazione/richiesta/pulici/{esportazione_id}",
     * name="monitoraggio_esportazione_gestione_richiesta_configurazione_pulisci")
     */
    public function gestisciConfigurazioneRichiestaPulisciAction($esportazione_id) {
        $em = $this->getEm();
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->find($esportazione_id);
        if (is_null($esportazione)) {
            throw new SfingeException("Esportazione $esportazione_id non trovata");
        }
        $ricercaData = new RicercaEsportazioneProgetto($esportazione);
        /** @var \BaseBundle\Service\RicercaService $ricercaService */
        $ricercaService = $this->container->get('ricerca');
        $ricercaService->pulisci($ricercaData);

        return $this->redirectToRoute('monitoraggio_esportazione_gestione_richiesta_configurazione', [
             'esportazione_id' => $esportazione_id,
         ]);
    }

    /**
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @PaginaInfo(titolo="Configurazione esportazione", sottoTitolo="Elenco delle richieste esportabili")
     * @Route("/gestione_configurazione/richiesta/{esportazione_id}/{sort}/{direction}/{page}",
     *     defaults={ "sort": "i.id", "direction": "asc", "page": "1"},
     *     name="monitoraggio_esportazione_gestione_richiesta_configurazione"
     * )
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function gestisciConfigurazioneRichiestaAction($esportazione_id) {
        $em = $this->getEm();
        /** @var MonitoraggioEsportazione $esportazione */
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->find($esportazione_id);
        if (\is_null($esportazione)) {
            throw new SfingeException("Esportazione $esportazione_id non trovata");
        }
        /** @var \BaseBundle\Service\RicercaService $ricercaService */
        $ricercaService = $this->container->get('ricerca');
        $ricercaData = new RicercaEsportazioneProgetto($esportazione);

        $risultatoRicerca = $ricercaService->ricerca($ricercaData, [
            'data_class' => RicercaEsportazioneProgetto::class,
        ]);
        /** @var SlidingPagination $risultato */
        $risultato = $risultatoRicerca['risultato'];

        $formDisabled = false;
        if (MonitoraggioEsportazioneLogFase::STATO_INVIATO == $esportazione->getLastFase()->getFase()) {
            $formDisabled = true;
        }

        $esportazioneMV = new MonitoraggioEsportazione();

        $configurazioni = new ArrayCollection($risultato->getItems());

        $esportazioneMV->setMonitoraggioConfigurazione($configurazioni);

        $form = $this->createForm(MonitoraggioEsportazioneRichiestaType::class, $esportazioneMV, [
            'url_indietro' => $this->generateUrl('elenco_monitoraggio_esportazione'),
            'esportazioneInviata' => $formDisabled,
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        $connection = $em->getConnection();
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('generaFile')->isClicked()) {
                return $this->redirectToRoute('monitoraggio_genera_file_igrue', ['esportazione_id' => $esportazione_id]);
            }
            try {
                $connection->beginTransaction();
                foreach ($esportazioneMV->getMonitoraggioConfigurazione() as $configurazione) {
                    $em->persist($configurazione);
                    $em->flush();
                }
                $connection->commit();
                if ($form->get('salvaAggiorna')->isClicked()) {
                    $this->scaricaMonitoraggio($esportazione->getId());
                }
                $this->addSuccess('Operazione effettuata con successo');
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }
        $viewdata = [
            'form' => $form->createView(),
            'esportazione' => $esportazione,
            'paginate' => $risultato,
            // 'numero_errori' => $numeroErrori,
            'risultato' => $risultatoRicerca,
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:gestioneConfigurazioneRichiesta.html.twig', $viewdata);
    }

    /**
     * @PaginaInfo(titolo="Gestione esportazioni", sottoTitolo="Visualizzazione errori di esportazione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/gestione_configurazione/mostra_errori/{configurazione_id}",  name="monitoraggio_esportazione_mostra_errori")
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     */
    public function mostraErroriValidazione($configurazione_id) {
        $em = $this->getEm();

        //Attenzione soft deletable disattivato!
        $em->getFilters()->disable('softdeleteable');

        $configurazione = $em->getRepository('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazione')->findOneById($configurazione_id);
        if (!$configurazione) {
            throw new \BaseBundle\Exception\SfingeException('Configurazione non trovata');
        }
        $esportazione_id = $configurazione->getMonitoraggioEsportazione()->getId();
        $url = '';
        switch (\get_class($configurazione)) {
            case 'MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta':
                $url = $this->generateUrl('monitoraggio_esportazione_gestione_richiesta_configurazione', ['esportazione_id' => $esportazione_id]);
                break;
            case 'MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura':
                $url = $this->generateUrl('monitoraggio_esportazione_gestione_procedura_configurazione', ['esportazione_id' => $esportazione_id]);
                break;
            case 'MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTrasferimento':
                $url = $this->generateUrl('monitoraggio_esportazione_gestione_trasferimento_configurazione', ['esportazione_id' => $esportazione_id]);
                break;
            case 'MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione':
            $url = $this->generateUrl('monitoraggio_esportazione_visualizza_errori_configurazione', ['configurazione_id' => $configurazione_id]);
            break;
            default:
                throw new SfingeException('Non è stato possibile risalire alla rotta ' . \get_class($configurazione));
        }
        $viewData = [
            'errori' => $em->getRepository('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneErrore')->findAllErrori($configurazione),
            'indietro' => $url,
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:mostraErrori.html.twig', $viewData);
    }

    /**
     * @PaginaInfo(titolo="Gestione esportazioni", sottoTitolo="Caricamento errori da SNM IGRUE")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/gestione_configurazione/carica_errori_igrue/{esportazione_id}",  name="monitoraggio_carica_errori_igrue")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function caricaErroriIgrueAction($esportazione_id) {
        $em = $this->getEm();
        \ini_set('max_execution_time', 60);
        /**
         * @var MonitoraggioEsportazione
         */
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) { /* @var MonitoraggioBundle\Entity\MonitoraggioEsportazione $esportazione */
            throw new SfingeException('Esportazione non trovata');
        }
        $form = $this->createForm('MonitoraggioBundle\Form\CaricamentoErroriIgrueType', $esportazione, [
            'tipologia_documento' => $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findOneByCodice(self::CODICE_FILE_ERRORE_IGRUE),
            'url_indietro' => $this->generateUrl('elenco_monitoraggio_esportazione'),
            'disabled' => !\is_null($esportazione->getDocumentoFromIgrue())
                || \in_array($esportazione->getLastFase()->getFase(), [
                        MonitoraggioEsportazioneLogFase::STATO_COMPLETATO,
                        MonitoraggioEsportazioneLogFase::STATO_IMPORTATO_ERRORI,
                ]),
        ]);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !\is_null($esportazione->getDocumentoFromIgrue())) {
            $documento = $esportazione->getDocumentoFromIgrue();
            $connection = $em->getConnection();
            try {
                $esportazione->setDocumentoFromIgrue($this->container->get('documenti')->carica($documento, false));
                /**
                 * @var MonitoraggioEsportazioneLogFase fase_completato
                 */
                $fase_completato = new MonitoraggioEsportazioneLogFase($esportazione,
                    MonitoraggioEsportazioneLogFase::STATO_IMPORTATO == $esportazione->getLastFase()->getFase() ?
                        MonitoraggioEsportazioneLogFase::STATO_IMPORTATO_ERRORI :
                        MonitoraggioEsportazioneLogFase::STATO_COMPLETATO);
                $esportazione->addFasi($fase_completato);
                $em->persist($fase_completato);
                $em->flush($fase_completato);
                $connection->beginTransaction();

                $this->get('gestore_esportazione_igrue')->importaFileRisposta($esportazione);
                $em->persist($esportazione);
                $fase_completato->setDataFine(new \DateTime());
                $em->flush();
                $connection->commit();
                $this->addSuccess('Operazione effettuata con successo');
            } catch (EsportazioneException $e) {
                $connection->rollBack();
                $em->remove($fase_completato);
                $em->flush($fase_completato);
                $em->remove($documento);
                $em->flush($documento);
                $this->container->get('monolog.logger.schema31')->error($e->getMessage(), ['esportazione_id' => $esportazione->getId()]);
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage(), ['esportazione_id' => $esportazione->getId()]);
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $em->remove($fase_completato);
                $em->flush($fase_completato);
                $em->remove($documento);
                $em->flush($documento);
                $this->addError("Errore durante l'importazione del file");
            }

            return $this->redirectToRoute('monitoraggio_carica_errori_igrue', ['esportazione_id' => $esportazione_id]);
        }
        $viewData = [
            'form' => $form->createView(),
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:caricamentoErroriIgrue.html.twig', $viewData);
    }

    /**
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @Route("/gestione_configurazione/genera_file_igrue/{esportazione_id}",  name="monitoraggio_genera_file_igrue")
     */
    public function generaFileIgrueAction($esportazione_id) {
        $task = [];
        $form = $this->createFormBuilder($task)
            ->add('save', 'BaseBundle\Form\SalvaIndietroType',
            [
                'label_salva' => 'Genera File',
                'url' => $this->generateUrl('monitoraggio_esportazione_gestione_procedura_configurazione', ['esportazione_id' => $esportazione_id]),
            ])
            ->getForm();

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
            if (\is_null($esportazione)) {
                throw new SfingeException('Esportazione non trovata');
            }

            if (!is_null($esportazione->getDocumentoToIgrue())) {
                return $this->container->get('documenti')->downloadDocumento($esportazione->getDocumentoToIgrue(), 'content.txt');
            }

            try {
                $nuovaFase = new MonitoraggioEsportazioneLogFase($esportazione);
                $nuovaFase->setDataInizio(new \DateTime())
                    ->setFase(MonitoraggioEsportazioneLogFase::STATO_INVIATO);
                $contenuto_file = $this->get('gestore_esportazione_igrue')->generaStreamFile($esportazione);
                $tipologia_documento = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findOneBy(['codice' => 'TO_IGRUE']);
                $nomeFile = 'Esportazione_' . $esportazione_id . '.txt';
                $documentoEsportazione = $this->container->get('documenti')->caricaDaByteArray($contenuto_file, $nomeFile, $tipologia_documento, false);
                $esportazione->setDocumentoToIgrue($documentoEsportazione);

                //Cambio Fase
                //$now = new \DateTime();
                // $lastFase = $esportazione->getLastFase();
                // $lastFase->setDataFine($now);
                // $em->persist($lastFase);
                $nuovaFase->setDataFine(new \DateTime());
                $esportazione->addFasi($nuovaFase);

                $em->persist($esportazione);
                $em->flush();
            } catch (\Exception $e) {
                throw new SfingeException('Errore nel salvataggio del file per IGRUE: ' . $e->getMessage());
            }

            return $this->container->get('documenti')->downloadDocumento($documentoEsportazione, 'content.txt');
        }

        $url = $this->generateUrl('monitoraggio_esportazione_gestione_procedura_configurazione', ['esportazione_id' => $esportazione_id]);

        $viewData = [
            'indietro' => $url,
            'form' => $form->createView(),
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:generaFileIgrue.html.twig', $viewData);
    }

    /**
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @Route("/gestione_configurazione/download_file_igrue/{esportazione_id}",  name="monitoraggio_download_file_igrue")
     */
    public function downloadFileIgrueAction($esportazione_id) {
        $em = $this->getEm();
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) {
            throw new SfingeException('Esportazione non trovata');
        }
        $documento = $esportazione->getDocumentoToIgrue();
        if (MonitoraggioEsportazioneLogFase::STATO_INVIATO != $esportazione->getLastFase()->getFase() || \is_null($documento)) {
            throw new SfingeException('Impossibile eseguire download di un esportazione non inviata');
        }

        return $this->container->get('documenti')->downloadDocumento($documento, 'content.txt');
    }

    /**
     * @PaginaInfo(titolo="Gestione esportazioni", sottoTitolo="Ritorno da IGRUE")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/gestione_configurazione/ritorno_da_igrue/{esportazione_id}",  name="monitoraggio_ritorno_da_igrue")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function ritornoDaIgrueAction($esportazione_id) {
        $em = $this->getEm();
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) {
            throw new SfingeException('Esportazione non trovata');
        }

        $viewData = [
            'esportazione' => $esportazione,
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:ritornoDaIgrue.html.twig', $viewData);
    }

    /**
     * @PaginaInfo(titolo="Gestione esportazioni", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/gestione_configurazione/termina_con_successo_igrue/{esportazione_id}",  name="monitoraggio_termina_con_successo_igrue")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function terminaConSuccessoIgrueAction($esportazione_id) {
        $em = $this->getEm();
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) {
            throw new SfingeException('Esportazione non trovata');
        }

        $connection = $em->getConnection();
        try {
            $connection->beginTransaction();
            $fase_completato = new MonitoraggioEsportazioneLogFase($esportazione, MonitoraggioEsportazioneLogFase::STATO_COMPLETATO);
            if (!$this->setAllflagIgrue($esportazione, false)) {
                throw new \Exception('Errore nell\'aggiornamento delle strutture');
            }

            $fase_completato->setDataFine(new \DateTime());
            $em->persist($fase_completato);
            $em->flush($fase_completato);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();

            return $this->addErrorRedirect('Errore nel salvataggio dei dati', 'elenco_monitoraggio_esportazione');
        }

        return $this->addSuccessRedirect('Operazione effettuata con successo', 'elenco_monitoraggio_esportazione');
    }

    /**
     * @PaginaInfo(titolo="Gestione esportazioni", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/gestione_configurazione/respinta_igrue/{esportazione_id}",  name="monitoraggio_respinta_igrue")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function respintaIgrueAction($esportazione_id) {
        $em = $this->getEm();
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) {
            throw new SfingeException('Esportazione non trovata');
        }

        $connection = $em->getConnection();
        try {
            $connection->beginTransaction();
            $fase_completato = new MonitoraggioEsportazioneLogFase($esportazione, MonitoraggioEsportazioneLogFase::STATO_RESPINTO);
            if (!$this->setAllflagIgrue($esportazione, true)) {
                throw new \Exception('Errore nell\'aggiornamento delle strutture');
            }

            $fase_completato->setDataFine(new \DateTime());
            $em->persist($fase_completato);
            $em->flush($fase_completato);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();

            return $this->addErrorRedirect('Errore nel salvataggio dei dati', 'elenco_monitoraggio_esportazione');
        }

        return $this->addSuccessRedirect('Operazione effettuata con successo', 'elenco_monitoraggio_esportazione');
    }

    /**
     * @return bool
     *
     * @param MonitoraggioEsportazione $esportazione
     * @param bool                     $flag
     */
    private function setAllflagIgrue($esportazione, $flag) {
        $em = $this->getEm();
        $strutture = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findAllStruttureByEsportazione($esportazione);
        while ($strutture->valid()) {
            $struttura = $strutture->current();
            $struttura[0]->setFlagErroreIgrue($flag);
            $em->persist($struttura[0]);
            $em->flush($struttura[0]);
            $em->detach($struttura[0]);
            $strutture->next();
        }

        return true;
    }

    /**
     * @PaginaInfo(titolo="Caricamento invii pregressi ad IGRUE", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/importazione",  name="monitoraggio_importazione_igrue")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function importaAction() {
        \ini_set('max_execution_time', 600);
        \ini_set('memory_limit', '512M');

        $em = $this->getEm();
        $tipologia = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findOneByCodice(self::CODICE_FILE_IMPORTAZIONE);
        if (\is_null($tipologia)) {
            throw new SfingeException('Tipologia documento non trovata');
        }
        $documento = new CaricamentoErroriIgrue($tipologia);
        $form = $this->createForm('MonitoraggioBundle\Form\ImportazioneIgrueType', $documento, [
            'tipologia_documento' => $tipologia,
            'url_indietro' => $this->generateUrl('elenco_monitoraggio_esportazione'),
        ]);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $logger = $this->container->get('monolog.logger.schema31');
            try {
                $documento = $this->container->get('documenti')->carica($documento->getFile(), false);
                $result = $this->container->get('gestore_importazione_igrue')->importaDocumento($documento);
                if (\count($result)) {
                    foreach ($result as $errore) {
                        $this->addError($errore);
                    }
                }
                $this->addSuccessRedirect('Operazione effettuata con successo', 'elenco_monitoraggio_esportazione');
            } catch (EsportazioneException $e) {
                $logger->error($e->getMessage());
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                throw $e;
                $logger->error($e->getMessage());
                $this->addError("Errore durante l'importazione del file");
            }
        }
        $mv = [
            'form' => $form->createView(),
        ];

        return $this->render('MonitoraggioBundle:Esportazioni:formImportazione.html.twig', $mv);
    }

    /**
     * @Route("/importazione_rest",  name="monitoraggio_importazione_rest_igrue")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function importaRestAction() {
        \ini_set('max_execution_time', 600);
        \ini_set('memory_limit', '512M');
        $request = $this->getCurrentRequest();
        if (!$request->isMethod('POST')) {
            return new Response(
                'Metodo ' . $request->getMethod() . ' non permesso',
                Response::HTTP_METHOD_NOT_ALLOWED,
                ['content-type' => 'text/plain']
            );
        }
        if (!$request->files->has('file')) {
            return new Response(
                'File mancante',
                Response::HTTP_PRECONDITION_FAILED,
                ['content-type' => 'text/plain']
            );
        }

        $em = $this->getEm();
        $documento = new DocumentoFile();
        try {
            $tipologia = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findOneByCodice(self::CODICE_FILE_IMPORTAZIONE);
            if (\is_null($tipologia)) {
                throw new EsportazioneException('Tipologia documento non trovata');
            }
            $documento->setFile($request->files->get('file'));
            $documento->setTipologiaDocumento($tipologia);
            $documento = $this->container->get('documenti')->carica($documento, true);
            $result = $this->container->get('gestore_importazione_igrue')->importaDocumento($documento);
        } catch (EsportazioneException $e) {
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());
            if ($em->isOpen()) {
                $em->remove($documento);
                $em->flush($documento);
            }

            return new JsonResponse(
                [$e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());
            if ($em->isOpen()) {
                $em->remove($documento);
                $em->flush($documento);
            }

            return new JsonResponse(
                ['Errore durante l\'operazione di importazione'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        if (0 == !\count($result)) {
            return new JsonResponse(
                $result,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } else {
            return new JsonResponse(
            'ok',
            Response::HTTP_OK
        );
        }
    }

    /**
     * @Route("/verifica_stato_esportazione/",  name="monitoraggio_verifica_stato_esportazione")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function verificaStatoEsportazioneAction() {
        $request = $this->getCurrentRequest();
        $em = $this->getEm();
        $statoInCorso = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findStatoInCorso();

        return new JsonResponse([
            'response' => $statoInCorso,
        ]);
    }

    /**
     * @PaginaInfo(titolo="Gestione esportazioni", sottoTitolo="Elenco")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco_pulisci/{sort}/{direction}/{page}", defaults={ "sort": "i.id", "direction": "asc", "page": "1"}, name="elenco_pulisci_monitoraggio_esportazione")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function elencoPulisciEsportazioniAction() {
        $datiRicerca = new \MonitoraggioBundle\Form\Entity\RicercaEsportazione();
        $this->container->get('ricerca')->pulisci($datiRicerca);

        return $this->redirectToRoute('elenco_monitoraggio_esportazione');
    }

    /**
     * @PaginaInfo(titolo="Cancella Importazione", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/cancella_importazione({esportazione_id}",  name="cancella_importazione")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function cancellaImportazione($esportazione_id) {
        $em = $this->getEm();
        $message = false;
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) {
            $message = 'Nessuna esportazione è associata all\'identificativo indicato';
        }

        if (MonitoraggioEsportazioneLogFase::STATO_IMPORTATO != $esportazione->getLastFase()->getFase()) {
            $message = 'La fase dell\'esportazione indicata non ne permette la cancellazione';
        }

        if (false !== $message) {
            return $this->addErrorRedirect($message, 'elenco_monitoraggio_esportazione');
        }

        try {
            $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->canellaEsportazioneImportata($esportazione);
        } catch (EsportazioneException $e) {
            return $this->addErrorRedirect($e->getMessage(), 'elenco_monitoraggio_esportazione');
        }

        return $this->addSuccessRedirect('Operazione effettuata con successo', 'elenco_monitoraggio_esportazione');
    }

    /**
     * @PaginaInfo(titolo="Visualizza errori", sottoTitolo="Visualizza gli errori presenti")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/visualizza_errori_esportazione/{esportazione_id}/{sort}/{direction}/{page}",
     *     defaults={ "sort": "i.id", "direction": "asc", "page": "1"},
     * name="monitoraggio_esportazione_visualizza_errori_configurazione"),
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     */
    public function visualizzaErroriConfigurazione($esportazione_id) {
        $em = $this->getEm();
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) {
            throw new SfingeException('Esportazione non trovata');
        }
        $ricerca = new RicercaTavolaEsportata($esportazione);
        $risultato = $this->container->get('ricerca')->ricerca($ricerca, [
           'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaTavolaEsportata',
       ]);
        return $this->render('MonitoraggioBundle:Esportazioni:visualizzaErroriEsportazione.html.twig', [
           'risultato' => $risultato,
            'esportazione' => $esportazione,
    ]);
    }

    /**
     * @PaginaInfo(titolo="Visualizza errori", sottoTitolo="Visualizza gli errori presenti")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/visualizza_errori_esportazione_pulisci/{esportazione_id}",  name="monitoraggio_esportazione_visualizza_errori_configurazione_pulisci")
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     */
    public function visualizzaErroriConfigurazionePulisci($esportazione_id) {
        $em = $this->getEm();
        $esportazione = $em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($esportazione_id);
        if (\is_null($esportazione)) {
            throw new SfingeException('Configurazione non trovata');
        }
        $ricerca = new RicercaTavolaEsportata($esportazione);
        $this->container->get('ricerca')->pulisci($ricerca);

        return $this->redirectToRoute('monitoraggio_esportazione_visualizza_errori_configurazione', [
            'esportazione_id' => $esportazione_id,
        ]);
    }
}
