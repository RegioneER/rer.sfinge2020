<?php

namespace MonitoraggioBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Form\Entity\RicercaProgetto;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use MonitoraggioBundle\Form\Entity\RicercaProceduraAggiudicazione;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use BaseBundle\Form\CommonType;
use AttuazioneControlloBundle\Entity\PagamentoAmmesso;
use MonitoraggioBundle\Form\PagamentoAmmessoType;
use MonitoraggioBundle\Form\SezioneProceduraleType;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use MonitoraggioBundle\Form\AnagraficaProgettoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use MonitoraggioBundle\Form\Type\RichiestaLocalizzazioneGeograficaType;
use BaseBundle\Form\SalvaIndietroType;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use MonitoraggioBundle\Form\RichiestaIndicatoriType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use MonitoraggioBundle\Validator\Constraints\ControlloIGRUE;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;

/**
 * @author lfontana, vbuscemi
 * @Route("/progetti")
 */
class ProgettiController extends BaseController {
    /**
     * @PaginaInfo(titolo="Progetti", sottoTitolo="mostra l'elenco dei progetti")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={ "sort" : "i.id", "direction" : "asc", "page" : "1"}, name="monitoraggio_elenco_progetti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco progetti")})
     */
    public function elencoProgettiAction() {
        $datiRicerca = new RicercaProgetto();
        $risultato = $this->get('ricerca')->ricerca($datiRicerca);

        return $this->render('MonitoraggioBundle:Progetti:elenco.html.twig', $risultato);
    }

    /**
     * @Route("/elenco_pulisci/{sort}/{direction}/{page}", defaults={"sort" : "t.id", "direction" : "asc", "page" : "1"}, name="monitoraggio_elenco_progetti_pulisci")
     * @PaginaInfo(titolo="Progetti", sottoTitolo="mostra l'elenco dei progetti")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco progetti")})
     */
    public function elencoProgettiPulisciAction() {
        $datiRicerca = new RicercaProgetto();
        $this->get('ricerca')->pulisci($datiRicerca);

        return $this->redirectToRoute('monitoraggio_elenco_progetti');
    }

    /**
     * @PaginaInfo(titolo="Sezione procedurale", sottoTitolo="mostra le fasi dell'iter procedurale del progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/sezione_procedurale/{richiesta_id}", name="monitoraggio_sezione_procedurale")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="sezione procedurale")})
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     * @param string|null $richiesta_id
     */
    public function sezioneProceduraleAction($richiesta_id) {
        $em = $this->getEm();
        $richiesta = $this->getRichiesta($richiesta_id);
        $copiaIter = self::copyArrayCollection($richiesta->getMonIterProgetti());
        $copiaStato = self::copyArrayCollection($richiesta->getMonStatoProgetti());

        $options = [
            'url_indietro' => $this->generateUrl('monitoraggio_elenco_progetti'),
            'disabled' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ];
        $form = $this->createForm(SezioneProceduraleType::class, $richiesta, $options);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                self::eliminaElementiCancellati($copiaIter, $richiesta->getMonIterProgetti(), $em);
                self::eliminaElementiCancellati($copiaStato, $richiesta->getMonStatoProgetti(), $em);
                $em->flush();
                $this->addFlash('success', 'Salvataggio effettuato correttamente');
            } catch (\Exception $ex) {
                $this->get('monolog.logger.schema31')->error($ex->getTraceAsString());
                $this->addFlash('error', 'Errore durante il salvataggio delle informazioni');
                throw $ex;
            }
        }
        $dati = [
            'form' => $form->createView(),
            'page_view' => 'sezioneProcedurale',
            'richiesta' => $richiesta,
        ];

        return $this->render('MonitoraggioBundle:Progetti:sezioneProcedurale.html.twig', $dati);
    }

    protected static function copyArrayCollection(Collection $input): ArrayCollection {
        $res = new ArrayCollection();
        foreach ($input as $value) {
            $res->add($value);
        }

        return $res;
    }

    protected static function eliminaElementiCancellati(ArrayCollection $cache, Collection $currentValues, EntityManagerInterface $em): void {
        /** @var RichiestaLivelloGerarchico $value */
        foreach ($cache as $value) {
            if (false == $currentValues->contains($value)) {
                if ($value instanceof RichiestaLivelloGerarchico) {
                    /** @var ImpegniAmmessi $impegnoAmmesso */
                    foreach ($value->getImpegniAmmessi() as $impegnoAmmesso) {
                        $em->remove($impegnoAmmesso);
                        $value->removeImpegniAmmessi($impegnoAmmesso);
                    }
                    /** @var PagamentoAmmesso $pagamentoAmmesso */
                    foreach ($value->getPagamentiAmmessi() as $pagamentoAmmesso) {
                        $em->remove($pagamentoAmmesso);
                        $value->removePagamentiAmmessi($pagamentoAmmesso);
                    }
                }
                $em->remove($value);
            }
        }
    }

    /**
     * @PaginaInfo(titolo="Anagrafica progetto", sottoTitolo="mostra i dati anagrafici del progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/anagrafica_progetto/{richiesta_id}", name="anagrafica_progetto")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="anagrafica progetto")})
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     * @param string|null $richiesta_id
     */
    public function anagraficaProgettoAction($richiesta_id) {
        $em = $this->getEm();
        $richiesta = $this->getRichiesta($richiesta_id);
        $copiaProgetti = self::copyArrayCollection($richiesta->getMonProgrammi());
        $copiaStati = self::copyArrayCollection($richiesta->getMonStatoProgetti());
        $copiaStrumenti = self::copyArrayCollection($richiesta->getMonStrumentiAttuativi());

        $options = [
            'url_indietro' => $this->generateUrl('monitoraggio_elenco_progetti'),
            'disabled' => false,
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ];
        $form = $this->createForm(AnagraficaProgettoType::class, $richiesta, $options);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $connection = $em->getConnection();
            $connection->beginTransaction();
            try {
                self::eliminaElementiCancellati($copiaProgetti, $richiesta->getMonProgrammi(), $em);
                self::eliminaElementiCancellati($copiaStati, $richiesta->getMonStatoProgetti(), $em);
                self::eliminaElementiCancellati($copiaStrumenti, $richiesta->getMonStrumentiAttuativi(), $em);
                $em->persist($richiesta);
                $em->flush();
                $connection->commit();
                $this->addSuccess('Salvataggio effettuato con successo');
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->get('monolog.logger.schema31')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
                $richiesta = $em->getRepository('RichiesteBundle:Richiesta')->findOneById($richiesta_id);
            }
        }
        $dati = [
            'richiesta' => $richiesta,
            'page_view' => 'anagraficaProgetto',
            'form' => $form->createView(),
        ];

        return $this->render('MonitoraggioBundle:Progetti:anagraficaProgetto.html.twig', $dati);
    }

    /**
     * @Route("/monitoraggio_crea_localizzazione_geografica/{richiesta_id}", name="monitoraggio_crea_localizzazione_geografica")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $richiesta_id
     */
    public function creaLocalizzazioneGeograficaAction(Request $request, $richiesta_id): Response {
        $richiesta = $this->getRichiesta($richiesta_id);
        $res = new LocalizzazioneGeografica($richiesta);
        $richiesta->addMonLocalizzazioneGeografica($res);

        $em = $this->getEm();
        try {
            $em->persist($res);
            $em->flush($res);
        } catch (\Exception $e) {
            $this->get('monolog.logger.schema31')->error($e->getMessage());

            return $this->addErrorRedirect(
                'Errore durante il salvataggio delle informazioni',
                'anagrafica_progetto',
                ['richiesta_id' => $richiesta_id]
            );
        }

        return $this->addSuccessRedirect(
            'Localizzazione geografica inserita con successo',
            'monitoraggio_modifica_localizzazione_geografica',
            [
                'id_localizzazione' => $res->getId(),
            ]
        );
    }

    /**
     * @PaginaInfo(titolo="Localizzazione geografica", sottoTitolo="Modifica la localizzione geografica")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/monitoraggio_modifica_localizzazione_geografica/{id_localizzazione}", name="monitoraggio_modifica_localizzazione_geografica")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="procedure aggiudicazione")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $id_localizzazione
     */
    public function modificaLocalizzazioneGeograficaAction(Request $request, $id_localizzazione): Response {
        $localizzazione = $this->getLocalizzazioneGeografica($id_localizzazione);
        $form = $this->createForm(RichiestaLocalizzazioneGeograficaType::class, $localizzazione)
        ->add('submit', SalvaIndietroType::class, [
            'url' => $this->generateUrl('anagrafica_progetto', ['richiesta_id' => $localizzazione->getRichiesta()->getId()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em = $this->getEm();
                $em->persist($localizzazione);
                $em->flush();
                $this->addSuccess('Informazioni salvate correttamente');
                return $this->redirectToRoute('anagrafica_progetto', ['richiesta_id' => $localizzazione->getRichiesta()->getId()]);
            } catch (\Exception $e) {
                $this->get('monolog.logger.schema31')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        $dati = [
            'form' => $form->createView(),
        ];
        return $this->render('MonitoraggioBundle:Progetti:localizzazioneGeografica.html.twig', $dati);
    }

    /**
     * @Route("/monitoraggio_elimina_localizzazione_geografica/{id_localizzazione}", name="monitoraggio_elimina_localizzazione_geografica")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $id_localizzazione
     */
    public function eliminaLocalizzazioneGeografica(Request $request, $id_localizzazione): Response {
        $localizzazione = $this->getLocalizzazioneGeografica($id_localizzazione);
        $richiesta = $localizzazione->getRichiesta();
        $richiesta->removeMonLocalizzazioneGeografica($localizzazione);

        $em = $this->getEm();
        try {
            $em->remove($localizzazione);
            $em->flush($localizzazione);
            $this->addSuccess('Localizzazione geografica rimossa con successo');
        } catch (\Exception $e) {
            $this->get('monolog.logger.schema31')->error($e->getMessage());
            $this->addError('Errore durante il salvataggio delle informazioni');
        }

        return $this->redirectToRoute(
            'anagrafica_progetto',
            [
                'richiesta_id' => $richiesta->getId(),
            ]
        );
    }

    protected function getLocalizzazioneGeografica($id_localizzazione): LocalizzazioneGeografica {
        $res = $this->getEm()->getRepository('MonitoraggioBundle:LocalizzazioneGeografica')->find($id_localizzazione);

        return $res;
    }

    /**
     * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="mostra l'elenco delle procedure di aggiudicazione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/monitoraggio_procedure_aggiudicazione/{richiesta_id}", name="monitoraggio_procedure_aggiudicazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="procedure aggiudicazione")})
     * @param string|null $richiesta_id
     */
    public function procedureAggiudicazioneAction($richiesta_id) {
        $richiesta = $this->getRichiesta($richiesta_id);

        $datiRicerca = new RicercaProceduraAggiudicazione($richiesta);
        $risultato = $this->get('ricerca')->ricerca($datiRicerca);
        $risultato['richiesta'] = $richiesta;
        $risultato['page_view'] = 'procedureAggiudicazione';

        return $this->render('MonitoraggioBundle:Progetti:proceduraAggiudicazioneElenco.html.twig', $risultato);
    }

    /**
     * @PaginaInfo(titolo="Procedure di aggiudicazione", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/monitoraggio_procedure_aggiudicazione_pulisci/{richiesta_id}", name="monitoraggio_procedure_aggiudicazione_pulisci")
     * @param string|null $richiesta_id
     */
    public function procedureAggiudicazionePulisciAction($richiesta_id) {
        $richiesta = $this->getRichiesta($richiesta_id);
        $datiRicerca = new RicercaProceduraAggiudicazione($richiesta);

        $this->get('ricerca')->pulisci($datiRicerca);

        return $this->redirectToRoute('monitoraggio_procedure_aggiudicazione', ['richiesta_id' => $richiesta_id]);
    }

    /**
     * @Route("/dettaglio_procedura_aggiudicazione/{idProcedura}", name="dettaglio_procedura_aggiudicazione")
     * @PaginaInfo(titolo="Modifica procedura di aggiudicazione", sottoTitolo="Modifica procedura di aggiudicazione")
     * @Menuitem(menuAttivo="dettaglioProcedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="procedura aggiudicazione")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $idProcedura
     */
    public function dettaglio_procedura_aggiudicazioneAction($idProcedura) {
        $procedura = $this->getEm()->getRepository('AttuazioneControlloBundle:ProceduraAggiudicazione')->findOneById($idProcedura);

        $form = $this->createForm('MonitoraggioBundle\Form\ProceduraAggiudicazioneType', $procedura, [
            'url_indietro' => $this->generateUrl('monitoraggio_procedure_aggiudicazione', ['richiesta_id' => $procedura->getRichiesta()->getId()]),
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);

        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->persist($procedura);
                $this->getEm()->flush();
                $this->addFlash('success', 'Modifica dei dati effettuata con successo.');
            } catch (\Exception $exc) {
                $this->get('monolog.logger.schema31')->error($exc->getTraceAsString());
                $this->addFlash('error', 'Errore nella modifica dei dati.');
            }
        }

        return $this->render('MonitoraggioBundle:Progetti:dettaglioProceduraAggiudicazione.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/dettaglio_programma/{programma_richiesta_id}", name="dettaglio_programma")
     * @PaginaInfo(titolo="Dettaglio delle classificazioni", sottoTitolo="dettaglio classificazioni")
     * @Menuitem(menuAttivo="dettaglioProcedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="dettaglio classificazioni")})
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     * @param string|null $programma_richiesta_id
     */
    public function dettaglioClassificazioniRichiesta($programma_richiesta_id) {
        $em = $this->getEm();
        $programmaRichiesta = $this->getRichiestaProgramma($programma_richiesta_id);
        $richiesta = $programmaRichiesta->getRichiesta();
        $copiaClassificazioni = self::copyArrayCollection($programmaRichiesta->getClassificazioni());
        $copiaLivelliGerarchici = self::copyArrayCollection($programmaRichiesta->getMonLivelliGerarchici());

        $options = [
            'url_indietro' => $this->generateUrl('anagrafica_progetto', ['richiesta_id' => $richiesta->getId()]),
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ];

        $form = $this->createForm('MonitoraggioBundle\Form\RichiestaProgrammaType', $programmaRichiesta, $options);

        /*
        $form->add('submit', CommonType::salva_indietro, array(
            'url' => $this->generateUrl('anagrafica_progetto', array('richiesta_id' => $richiesta->getId())),
        ));
        */

        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $connection = $em->getConnection();
            try {
                $connection->beginTransaction();
                self::eliminaElementiCancellati($copiaClassificazioni, $programmaRichiesta->getClassificazioni(), $em);
                self::eliminaElementiCancellati($copiaLivelliGerarchici, $programmaRichiesta->getMonLivelliGerarchici(), $em);
                $em->persist($programmaRichiesta);
                $em->flush();
                $connection->commit();
                $this->addSuccess('Informazioni salvate correttamente');
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->get('monolog.logger.schema31')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }

        $dati = [
            'form' => $form->createView(),
            'page_view' => 'anagraficaProgetto',
            'richiesta' => $richiesta,
            'richiesta_programma' => $programmaRichiesta,
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ];

        return $this->render('MonitoraggioBundle:Progetti:dettaglioClassificazioni.hmtl.twig', $dati);
    }

    /**
     * @param int $programma_richiesta_id
     * @return RichiestaProgramma
     * @throws SfingeException
     */
    private function getRichiestaProgramma($programma_richiesta_id) {
        $em = $this->getEm();

        $programmaRichiesta = $em->getRepository('AttuazioneControlloBundle:RichiestaProgramma')->find($programma_richiesta_id);
        if (!$programmaRichiesta) {
            throw new SfingeException('Programma non trovato');
        }

        return $programmaRichiesta;
    }

    /**
     * @PaginaInfo(titolo="Indicatori di output", sottoTitolo="mostra l'elenco degli indicatori di output associati al progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/indicatori/{richiesta_id}", name="indicatori_elenco")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="indicatori di output")})
     * @param string|null $richiesta_id
     */
    public function indicatoriAction($richiesta_id) {
        $em = $this->getEm();
        $richiesta = $this->getRichiesta($richiesta_id);

        $indicatoriOutputPresenti = self::copyArrayCollection($richiesta->getMonIndicatoreOutput());
        $indicatoriRisultatoPresenti = self::copyArrayCollection($richiesta->getMonIndicatoreRisultato());

        $options = [
            'disabled' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
            'url_indietro' => $this->generateUrl('anagrafica_progetto', ['richiesta_id' => $richiesta->getId()]),
        ];
        $form = $this->createForm(RichiestaIndicatoriType::class, $richiesta, $options);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                self::eliminaElementiCancellati($indicatoriOutputPresenti, $richiesta->getMonIndicatoreOutput(), $em);
                self::eliminaElementiCancellati($indicatoriRisultatoPresenti, $richiesta->getMonIndicatoreRisultato(), $em);
                $em->flush();

                return $this->addSuccessRedirect('Operazione effettuata con successo', 'indicatori_elenco', ['richiesta_id' => $richiesta_id]);
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());

                return $this->addErrorRedirect('Errore durante il salvataggio dei dati', 'indicatori_elenco', ['richiesta_id' => $richiesta_id]);
            }
        }

        $risultato = [
            'richiesta' => $richiesta,
            'page_view' => 'indicatori',
            'form' => $form->createView(),
        ];

        return $this->render('MonitoraggioBundle:Progetti:indicatoriElenco.html.twig', $risultato);
    }

    /**
     * @PaginaInfo(titolo="Soggetti collegati", sottoTitolo="Mostra l'elenco dei soggetti collegati al progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/soggetti_correlati/{richiesta_id}", name="soggetti_correlati")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="soggetti collegati")})
     * @param string|null $richiesta_id
     */
    public function soggettiCorrelatiAction($richiesta_id) {
        $richiesta = $this->getRichiesta($richiesta_id);
        $risultato['richiesta'] = $richiesta;
        $risultato['page_view'] = 'soggettiCorrelati';

        return $this->render('MonitoraggioBundle:Progetti:soggettiCorrelatiElenco.html.twig', $risultato);
    }

    /**
     * @PaginaInfo(titolo="Sezione Finanziaria", sottoTitolo="Mostra i dati finanziari del progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/sezione_finanziaria/{richiesta_id}", name="sezione_finanziaria")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="sezione finanziaria")})
     * @param string|null $richiesta_id
     */
    public function sezioneFinanziariaAction($richiesta_id) {
        $em = $this->getEm();
        $richiesta = $this->getRichiesta($richiesta_id);

        $options = [
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ];

        $formCostoAmmesso = $this->createForm('MonitoraggioBundle\Form\CostiAmmessiRichiestaType', $richiesta, $options);

        $formCostoAmmesso->handleRequest($this->getCurrentRequest());
        if ($formCostoAmmesso->isSubmitted() && $formCostoAmmesso->isValid()) {
            try {
                $em->persist($richiesta);
                $em->flush();
                $this->addSuccess('Informazioni salvate correttamente');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        /** @var \AttuazioneControlloBundle\Service\IGestoreRichiesteATC $gestoreATC */
        $gestoreATC = $this->get('gestore_richieste_atc')->getGestore($richiesta->getProcedura());
        $pianoCostiATC = $gestoreATC->calcolaAvanzamentoPianoCosti($richiesta, null, null);

        $impegni_ammessi = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->findAllRichiestaImpegni($richiesta, 'I');
        $disimpegni_ammessi = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->findAllRichiestaImpegni($richiesta, 'D');
        $pagamenti_percettori = $em->getRepository('AttuazioneControlloBundle:PagamentiPercettori')->findAllPagamentiPercettori($richiesta);
        $pagamentiAmmessi = $em->getRepository('AttuazioneControlloBundle:RichiestaPagamento')->findAllPagamenti($richiesta, 'P');
        $rettifiche = $em->getRepository('AttuazioneControlloBundle:RichiestaPagamento')->findAllPagamenti($richiesta, 'R');
        $certificazioni = $em->getRepository('CertificazioniBundle:CertificazionePagamento')->findAllSpeseCertificate($richiesta);
        $quadro_economico = $em->getRepository('MonitoraggioBundle:VistaFN02')->findBy(['richiesta' => $richiesta]);
        $piano_costi = $em->getRepository('MonitoraggioBundle:VistaFN03')->findBy(['richiesta' => $richiesta]);
        $datiView = [
            'richiesta' => $richiesta,
            'impegni_ammessi' => $impegni_ammessi,
            'disimpegni_ammessi' => $disimpegni_ammessi,
            'page_view' => 'sezioneFinanziaria',
            'piano_costi' => $piano_costi,
            'pagamenti_percettori' => $pagamenti_percettori,
            'pagamentiAmmessi' => $pagamentiAmmessi,
            'rettifiche' => $rettifiche,
            'avanzamento' => $pianoCostiATC,
            'formCostoAmmesso' => $formCostoAmmesso->createView(),
            'quadro_economico' => $quadro_economico,
            'certificazioni' => $certificazioni,
        ];

        return $this->render('MonitoraggioBundle:Progetti:sezioneFinanziaria.html.twig', $datiView);
    }

    /**
     * @PaginaInfo(titolo="Modifica richiesta impegno/disimpegno", sottoTitolo="Modifica impegno/disimpegno")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/modifica_richiesta_impegni/{richiestaimpegni_id}", name="modifica_richiesta_impegni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Modifica impegno/disimpegno")})
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     * @param string|null $richiestaimpegni_id
     */
    public function modificaRichiestaImpegniAction($richiestaimpegni_id) {
        $em = $this->getEm();
        $obj = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->find($richiestaimpegni_id);
        $richiesta = $obj->getRichiesta();
        if (is_null($obj)) {
            $this->container->get('monolog.logger.schema31')->error('Tentato accesso a impegno non esistente, ID: ' . $richiestaimpegni_id);

            return $this->addErrorRedirect('Risorsa non disponibile', 'monitoraggio_elenco_progetti');
        }
        $form = $this->createForm('MonitoraggioBundle\Form\RichiestaImpegniType', $obj, [
            'enabledTr' => !is_null($richiesta->getMonLivIstituzioneStrFin()),
            'url_indietro' => $this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]) . '#imp',
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);
        $richiesta = $obj->getRichiesta();
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($obj);
                $em->flush($obj);
                $this->addSuccess('Operazione effettuata con successo');
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());

                return $this->addErrorRedirect('Errore durante il salvataggio dei dati', 'monitoraggio_elenco_progetti');
            }
        }

        $datiTwig = [
            'form' => $form->createView(),
            'page_view' => 'sezioneFinanziaria',
            'richiesta' => $richiesta,
        ];

        return $this->render('MonitoraggioBundle:Progetti:formImpegni.html.twig', $datiTwig);
    }

    /**
     * @PaginaInfo(titolo="Modifica impegno/disimpegno ammesso", sottoTitolo="Modifica impegno/disimpegno ammesso")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/modifica_impegni_ammessi/{impegniammessi_id}",  name="modifica_impegni_ammessi")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Modifica impegno/disimpegno ammesso")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $impegniammessi_id
     */
    public function modificaImpegniAmmessiAction($impegniammessi_id) {
        $em = $this->getEm();
        $obj = $em->getRepository('AttuazioneControlloBundle:ImpegniAmmessi')->find($impegniammessi_id);
        if (is_null($obj)) {
            $this->container->get('monolog.logger.schema31')->error('Tentato accesso a impegno ammesso non esistente, ID: ' . $impegniammessi_id);

            return $this->addErrorRedirect('Risorsa non disponibile', 'monitoraggio_elenco_progetti');
        }
        $richiesta = $obj->getRichiestaImpegni()->getRichiesta();
        $form = $this->createForm("MonitoraggioBundle\Form\ImpegniAmmessiType", $obj, [
            'disabled' => false,
            'url_indietro' => $this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]),
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($obj);
                $em->flush($obj);
                $this->addSuccess('Operazione effettuata con successo');
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());

                return $this->addErrorRedirect('Errore durante il salvataggio dei dati', 'monitoraggio_elenco_progetti');
            }
        }

        $datiTwig = [
            'form' => $form->createView(),
            'page_view' => 'sezioneFinanziaria',
            'richiesta' => $richiesta,
        ];

        return $this->render('MonitoraggioBundle:Progetti:formImpegniAmmessi.html.twig', $datiTwig);
    }

    /**
     * @PaginaInfo(titolo="Elimina richiesta impegno", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elimina_richiesta_impegni/{richiestaimpegni_id}/{impegniammessi_id}", defaults={"impegniammessi_id" : null}, name="elimina_richiesta_impegni")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $richiestaimpegni_id
     * @param string|null|null $impegniammessi_id
     */
    public function eliminaRichiestaImpegniAction($richiestaimpegni_id, $impegniammessi_id = null) {
        $this->checkCsrf('token', 'csrfToken');
        $em = $this->getEm();
        try {
            if (is_null($impegniammessi_id)) {
                $obj = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->find($richiestaimpegni_id);
                $richiesta_id = $obj->getRichiesta()->getId();
            } else {
                $obj = $em->getRepository('AttuazioneControlloBundle:ImpegniAmmessi')->find($impegniammessi_id);
                $richiesta_id = $obj->getRichiestaImpegni()->getRichiesta()->getId();
            }
            $em->remove($obj);
            $em->flush();
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());

            return $this->addErrorRedirect('Errore durante il salvataggio dei dati', 'monitoraggio_elenco_progetti');
        }
        $this->addSuccess('Operazione effettuata con successo');

        return $this->redirect($this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta_id]) . '#imp');
    }

    /**
     * @PaginaInfo(titolo="Inserisci richiesta impegno/disimpegno", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/inserisci_richiesta_impegni/{richiesta_id}/{tipo_impegno}", name="inserisci_richiesta_impegni")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $richiesta_id
     * @param string|null $tipo_impegno
     */
    public function inserisciRichiestaImpegniAction($richiesta_id, $tipo_impegno) {
        $em = $this->getEm();
        $richiesta = $this->getRichiesta($richiesta_id);

        $obj = new \AttuazioneControlloBundle\Entity\RichiestaImpegni($richiesta);
        $obj->setTipologiaImpegno($tipo_impegno);
        $form = $this->createForm('MonitoraggioBundle\Form\RichiestaImpegniType', $obj, [
            'enabledTr' => !is_null($richiesta->getMonLivIstituzioneStrFin()),
            'url_indietro' => $this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]),
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $connection = $em->getConnection();
            $connection->beginTransaction();
            try {
                $numeroProgrammi = count($richiesta->getMonProgrammi());
                $numeroLivelliGerarchici = 0;
                if (1 == $numeroProgrammi) {
                    $richiesta_programma = $richiesta->getMonProgrammi();
                    $richiesta_programma = $richiesta_programma[0];
                    $numeroLivelliGerarchici = count($richiesta_programma->getMonLivelliGerarchici());
                    if (1 == $numeroLivelliGerarchici) {
                        $objAmm = new ImpegniAmmessi();
                        $objAmm->setRichiestaImpegni($obj);
                        $objAmm->setDataImpAmm($obj->getDataImpegno());
                        $objAmm->setTc38CausaleDisimpegnoAmm($obj->getTc38CausaleDisimpegno());
                        $objAmm->setTipologiaImpAmm($obj->getTipologiaImpegno());
                        $objAmm->setImportoImpAmm($obj->getImportoImpegno());
                        $livelli_gerarchici = $richiesta_programma->getMonLivelliGerarchici();
                        $objAmm->setRichiestaLivelloGerarchico($livelli_gerarchici[0]);
                        $obj->addMonImpegniAmmessi($objAmm);
                        $em->persist($obj);
                        $em->flush();
                        $connection->commit();

                        return $this->addSuccessRedirect('Impegno ed impegno ammesso inseriti con successo', 'sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]);
                    }
                }

                $em->persist($obj);
                $em->flush();
                $connection->commit();

                if ($numeroLivelliGerarchici > 1 || $numeroProgrammi > 1) {
                    return $this->addSuccessRedirect('Impegno aggiunto con successo', 'aggiungi_impegni_ammessi', ['richiestaimpegni_id' => $obj->getId()]);
                }
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());

                return $this->addErrorRedirect('Errore durante il salvataggio dei dati', 'monitoraggio_elenco_progetti');
            }
        }

        $datiTwig = [
            'form' => $form->createView(),
            'page_view' => 'sezioneFinanziaria',
            'richiesta' => $richiesta,
        ];

        return $this->render('MonitoraggioBundle:Progetti:formImpegni.html.twig', $datiTwig);
    }

    /**
     * @PaginaInfo(titolo="Aggiungi impegno/disimpegno ammesso", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/aggiungi_impegni_ammessi/{richiestaimpegni_id}",  name="aggiungi_impegni_ammessi")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $richiestaimpegni_id
     */
    public function aggiungiImpegniAmmessiAction($richiestaimpegni_id) {
        $em = $this->getEm();
        $richiestaImpegno = $em->getRepository('AttuazioneControlloBundle:RichiestaImpegni')->find($richiestaimpegni_id);
        $obj = new ImpegniAmmessi();
        $obj->setRichiestaImpegni($richiestaImpegno);
        $obj->setDataImpAmm($richiestaImpegno->getDataImpegno());
        $obj->setTc38CausaleDisimpegnoAmm($richiestaImpegno->getTc38CausaleDisimpegno());
        $obj->setTipologiaImpAmm($richiestaImpegno->getTipologiaImpegno());
        $richiestaImpegno->addMonImpegniAmmessi($obj);
        $richiesta = $richiestaImpegno->getRichiesta();
        $url_indietro = $this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]) . '#imp';
        $form = $this->createForm("MonitoraggioBundle\Form\ImpegniAmmessiType", $obj, [
            'disabled' => false,
            'url_indietro' => $url_indietro,
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($obj);
                $em->flush($obj);
                $this->addSuccess('Operazione effettuata con successo');

                return $this->redirect($url_indietro);
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());

                return $this->addErrorRedirect('Errore durante il salvataggio dei dati', 'monitoraggio_elenco_progetti');
            }
        }

        $datiTwig = [
            'form' => $form->createView(),
            'page_view' => 'sezioneFinanziaria',
            'richiesta' => $richiesta,
        ];

        return $this->render('MonitoraggioBundle:Progetti:formImpegniAmmessi.html.twig', $datiTwig);
    }

    /**
     * @PaginaInfo(titolo="Aggiungi pagamento/recupero ammesso", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/aggiungi_pagamento_ammesso/{pagamento_id}",  name="monitoraggio_aggiungi_pagamento_ammesso")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $pagamento_id
     */
    public function aggiungiPagamentoAmmessoAction($pagamento_id) {
        $em = $this->getEm();
        /** @var RichiestaPagamento $pagamento */
        $pagamento = $em->getRepository('AttuazioneControlloBundle:RichiestaPagamento')->findOneById($pagamento_id);
        if (\is_null($pagamento)) {
            return $this->addErrorRedirect('Pagamento relativo alla richiesta non trovato', 'monitoraggio_elenco_progetti');
        }
        $richiesta = $pagamento->getRichiesta();
        $pagamentoAmmesso = new PagamentoAmmesso($pagamento);
        $url_indietro = $this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]) . '#pag';

        $form = $this->createForm(PagamentoAmmessoType::class, $pagamentoAmmesso, [
            'url_indietro' => $url_indietro . '#pag',
            'disabled' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($pagamentoAmmesso);
                $em->flush($pagamentoAmmesso);
                $this->addSuccess('Operazione effettuata con successo');

                return $this->redirect($url_indietro);
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio dei dati');

                return $this->redirect($url_indietro);
            }
        }
        $dati = [
            'form' => $form->createView(),
            'page_view' => 'sezioneFinanziaria',
            'richiesta' => $richiesta,
        ];

        return $this->render('MonitoraggioBundle:Progetti:formAggiungiPagamentoAmmesso.html.twig', $dati);
    }

    /**
     * @PaginaInfo(titolo="Modifica pagamento/recupero ammesso", sottoTitolo="Modifica pagamento/recupero ammesso")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/modifica_pagamento_ammesso/{pagamento_ammesso_id}",  name="monitoraggio_modifica_pagamento_ammesso")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Modifica pagamento/recupero ammesso")})
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     * @param string|null $pagamento_ammesso_id
     */
    public function modificaPagamentoAmmessoAction($pagamento_ammesso_id) {
        $em = $this->getEm();
        $pagamentoAmmesso = $em->getRepository('AttuazioneControlloBundle:PagamentoAmmesso')->findOneById($pagamento_ammesso_id);
        if (is_null($pagamento_ammesso_id)) {
            //throw new \BaseBundle\Exception\SfingeException('Pagamento relativo alla richiesta non trovato');
            return $this->addErrorRedirect('Pagamento relativo alla richiesta non trovato', 'monitoraggio_elenco_progetti');
        }
        $richiesta = $pagamentoAmmesso->getRichiestaPagamento()->getRichiesta();
        $url_indietro = $this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]) . '#pag';
        $form = $this->createForm('MonitoraggioBundle\Form\PagamentoAmmessoType', $pagamentoAmmesso, [
            'url_indietro' => $url_indietro,
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($pagamentoAmmesso);
                $em->flush($pagamentoAmmesso);
                $this->addSuccess('Operazione effettuata con successo');
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio dei dati');

                return $this->redirect($url_indietro);
            }
        }
        $dati = [
            'form' => $form->createView(),
            'page_view' => 'sezioneFinanziaria',
            'richiesta' => $richiesta,
        ];

        return $this->render('MonitoraggioBundle:Progetti:formModificaPagamentoAmmesso.html.twig', $dati);
    }

    /**
     * @PaginaInfo(titolo="Elimina richiesta impegno", sottoTitolo="")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elimina_pagamento_ammesso/{pagamento_ammesso_id}", name="monitoraggio_elimina_pagamento_ammesso")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $pagamento_ammesso_id
     */
    public function eliminaPagamentoAmmessoAction($pagamento_ammesso_id) {
        $this->checkCsrf('token', 'csrfToken');
        $em = $this->getEm();
        $obj = $em->getRepository('AttuazioneControlloBundle:PagamentoAmmesso')->findOneById($pagamento_ammesso_id);
        $richiesta = $obj->getRichiestaPagamento()->getRichiesta();
        try {
            $em->remove($obj);
            $em->flush();
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());

            return $this->addErrorRedirect('Errore durante il salvataggio dei dati', 'monitoraggio_elenco_progetti');
        }
        $this->addSuccess('Operazione effettuata con successo');

        return $this->redirect($this->generateUrl('sezione_finanziaria', ['richiesta_id' => $richiesta->getId()]) . '#imp');
    }

    /**
     * @PaginaInfo(titolo="Inserisci soggetto collegato", sottoTitolo="nuovo soggetto collegato al progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/nuovo_soggetto_collegato/{richiesta_id}", name="monitoraggio_nuovo_soggetto_collegato")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="nuovo soggetto collegato")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $richiesta_id
     */
    public function nuovoSoggettoCollegato($richiesta_id) {
        $em = $this->getEm();
        $richiesta = $this->getRichiesta($richiesta_id);
        $nuovoSoggettoCollegato = new SoggettiCollegati($richiesta);
        $form = $this->createForm('MonitoraggioBundle\Form\SoggettoCollegatoType', $nuovoSoggettoCollegato, [
            'url_indietro' => $this->generateUrl('soggetti_correlati', ['richiesta_id' => $richiesta->getId()]),
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $richiesta->addMonSoggettiCorrelati($nuovoSoggettoCollegato);
                $em->persist($richiesta);
                $em->flush();
                return $this->addSuccessRedirect(
                    'Operazione effettuata con successo',
                    'soggetti_correlati',
                    ['richiesta_id' => $richiesta->getid()]
                );
            } catch (\Exception $e) {
                $this->addError('Errore nel salvataggio delle informazioni');
            }
        }
        return $this->render('MonitoraggioBundle:Progetti:nuovoSoggettoCollegato.html.twig', [
            'form' => $form->createView(),
            'page_view' => 'soggettiCorrelati',
            'richiesta' => $richiesta,
        ]);
    }

    /**
     * @Route("/find_soggetti", name="monitoraggio_cerca_soggetto")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function searchSoggettiAction() {
        $request = $this->getCurrentRequest();
        $query = $request->query->get('q');
        $result = $this->getEm()->getRepository('SoggettoBundle:Soggetto')->searchSoggetto($query);

        return new JsonResponse($result);
    }

    /**
     * @PaginaInfo(titolo="Modifica soggetto collegato", sottoTitolo="modifica soggetto collegato al progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/modifica_soggetto_collegato/{soggetto_collegato_id}", name="monitoraggio_modifica_soggetto_collegato")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="modifica soggetto collegato")})
     * @Security("has_role('ROLE_MONITORAGGIO_LETTURA')")
     * @param string|null $soggetto_collegato_id
     */
    public function modificaSoggettoCollegatoAction($soggetto_collegato_id) {
        $soggettoCollegato = $this->getSoggettoCollegato($soggetto_collegato_id);
        $richiesta = $soggettoCollegato->getRichiesta();

        $form = $this->createForm('MonitoraggioBundle\Form\SoggettoCollegatoType', $soggettoCollegato, [
            'url_indietro' => $this->generateUrl('soggetti_correlati', ['richiesta_id' => $richiesta->getId()]),
            'ruolo_lettura' => $this->isGranted('ROLE_MONITORAGGIO_LETTURA') && !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ]);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->persist($soggettoCollegato);
                $em->flush($soggettoCollegato);
                $this->addSuccess('Operazione effettuata con successo');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        return $this->render('MonitoraggioBundle:Progetti:nuovoSoggettoCollegato.html.twig', [
            'form' => $form->createView(),
            'page_view' => 'soggettiCorrelati',
            'richiesta' => $richiesta,
        ]);
    }

    /**
     * @Route("/elimina_soggetto_collegato/{soggetto_collegato_id}", name="monitoraggio_elimina_soggetto_collegato")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $soggetto_collegato_id
     */
    public function eliminaSoggettoCollegatoAction($soggetto_collegato_id) {
        $this->checkCsrf('token');
        $soggettoCollegato = $this->getSoggettoCollegato($soggetto_collegato_id);
        $richiesta = $soggettoCollegato->getRichiesta();
        $em = $this->getEm();
        try {
            $richiesta->removeMonSoggettiCorrelati($soggettoCollegato);
            $em->remove($soggettoCollegato);
            $em->persist($richiesta);
            $em->flush();
            $this->addSuccess('Soggetto collegato eliminato correttamente');
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getMessage());
            $this->addError('Errore durante la cancellazione del soggetto collegato');
        }
        return $this->redirectToRoute('soggetti_correlati', ['richiesta_id' => $richiesta->getId()]);
    }

    /**
     * @return SoggettiCollegati
     * @throws SfingeException
     * @param string|null $soggetto_collegato_id
     */
    private function getSoggettoCollegato($soggetto_collegato_id) {
        $soggettoCollegato = $this->getEm()->getRepository('AttuazioneControlloBundle:SoggettiCollegati')->find($soggetto_collegato_id);
        if (\is_null($soggettoCollegato)) {
            throw new SfingeException('Soggetto collegato non trovato');
        }
        return $soggettoCollegato;
    }

    /**
     * @Route("/classificazioni_richiesta_programma", name="monitoraggio_api_classificazione_richiesta_programma")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function classificazioniPerRichiestaProgrammaAction() {
        $query = $this->getCurrentRequest()->query;
        $richiestaProgramma = $query->get('richiesta_programma');
        $tipoClassificazione = $query->get('tipo_classificazione');

        if ($richiestaProgramma && $tipoClassificazione) {
            return new JsonResponse(
                $this->getEm()
                ->getRepository('AttuazioneControlloBundle:RichiestaProgrammaClassificazione')
                ->searchClassificazioni($tipoClassificazione, $richiestaProgramma)
            );
        }

        throw new SfingeException('Richiesta non valida');
    }

    /**
     * @PaginaInfo(titolo="Validazione progetto", sottoTitolo="elenco problemi riscontrati nel progetto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/validazione_progetto/{richiesta_id}", name="monitoraggio_validazione_progetto")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="nuovo soggetto collegato")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @param string|null $richiesta_id
     */
    public function reportValidazioneAction($richiesta_id): Response {
        $richiesta = $this->getRichiesta($richiesta_id);

        $controlliValidazione = [
            new ControlloIGRUE(),
        ];

        /**
         * @var ValidatorInterface
         */
        $validatore = $this->get('validator');
        $validationList = $validatore->validate($richiesta, $controlliValidazione);

        return $this->render('MonitoraggioBundle:Progetti:elencoErrori.html.twig', [
            'lista_errori' => $validationList,
            'page_view' => 'validazione',
            'richiesta' => $richiesta,
        ]);
    }
}
