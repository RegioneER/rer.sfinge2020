<?php

namespace MonitoraggioBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\IndicatoriOutputAzioni;
use SfingeBundle\Entity\Procedura;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use MonitoraggioBundle\Form\ProcedureAttivazioneType;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
use MonitoraggioBundle\Form\AssociazioniAzioniIndicatoriType;
use MonitoraggioBundle\Form\Entity\RicercaAssociazioneAzioniIndicatori;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MonitoraggioBundle\Form\Entity\RicercaProcedura;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MonitoraggioBundle\Service\IGestoreFinanziamento;
use MonitoraggioBundle\Form\VociSpesaProceduraType;
use RichiesteBundle\Entity\IndicatoreOutput;
use RichiesteBundle\Repository\IndicatoreOutputRepository;

/**
 * @Route("/procedure_attivazione")
 */
class ProcedureAttivazioneController extends BaseController {
    /**
     * @PaginaInfo(titolo="Procedure attivazione", sottoTitolo="mostra l'elenco delle procedure di attivazione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={ "sort": "i.id", "direction": "asc", "page": "1"}, name="elenco_procedure_attivazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco procedure attivazione")})
     */
    public function elencoAction(): Response {
        $ricerca = new RicercaProcedura();
        $dati = $this->get('ricerca')->ricerca($ricerca, []);

        return $this->render('MonitoraggioBundle:ProcedureAttivazione:procedureAttivazioneElenco.html.twig', $dati);
    }

    /**
     * @PaginaInfo(titolo="Procedure attivazione", sottoTitolo="mostra l'elenco delle procedure di attivazione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco_pulisci/{sort}/{direction}/{page}", defaults={ "sort": "i.id", "direction": "asc", "page": "1"}, name="elenco_procedure_attivazione_pulisci")
     */
    public function elencoPulisciAction(): Response {
        $ricerca = new RicercaProcedura();
        $dati = $this->get('ricerca')->pulisci($ricerca);

        return $this->redirectToRoute('elenco_procedure_attivazione');
    }

    /** Pagina per dettaglio e modifica delle procedure di attivazione
     * @PaginaInfo(titolo="Procedura attivazione", sottoTitolo="mostra il dettaglio della procedura di attivazione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/dettaglio/{procedura_id}", name="dettaglio_procedure_attivazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="dettaglio procedura attivazione")})
     */
    public function dettaglioProceduraAction(Request $request, $procedura_id): Response {
        $em = $this->getEm();
        /** @var Procedura $bando */
        $bando = $em->getRepository('SfingeBundle:Procedura')->find($procedura_id);

        //Effettuo copia valori dei programmi collegati
        $cacheProgrammi = new ArrayCollection();
        foreach ($bando->getMonProcedureProgrammi() as $programma) {
            $cacheProgrammi->add($programma);
        }

        $options = [
            'url_indietro' => $this->generateUrl('elenco_procedure_attivazione'),
            'disabled' => !$this->isGranted('ROLE_MONITORAGGIO_SCRITTURA'),
        ];

        $form = $this->createForm(ProcedureAttivazioneType::class, $bando, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                //Rimozione elementi non più presenti
                foreach ($cacheProgrammi as $programma) {
                    if (false === $bando->getMonProcedureProgrammi()->contains($programma)) {
                        $em->remove($programma);
                    }
                }
                $em->persist($this->aggiornaTC1($bando));

                $em->persist($bando);
                $em->flush();
                $this->addFlash('success', 'Dati salvati con successo');
            } catch (\Exception $e) {
                $this->get('monolog.logger.schema31')->error($e->getTraceAsString());
                $this->addFlash('error', 'Errore nel salvataggio delle informazioni');
                if ('dev' == $this->get('kernel')->getEnvironment()) {
                    throw $e;
                }
            }
        }
        $dati = [
            'form' => $form->createView(),
        ];

        return $this->render('MonitoraggioBundle:ProcedureAttivazione:dettaglio.html.twig', $dati);
    }

    private function aggiornaTC1(Procedura $bando): TC1ProceduraAttivazione {
        $em = $this->getEm();
        /** @var TC1ProceduraAttivazione $tc1 */
        $tc1 = $em->getRepository('MonitoraggioBundle:TC1ProceduraAttivazione')->findOneBy(
            [
                'proceduraOperativa' => $bando,
                'cod_proc_att' => $bando->getMonCodiceProceduraAttivazione(),
            ]
        );
        if (\is_null($tc1)) {
            $tc1 = new TC1ProceduraAttivazione();
            $tc1->setCodProcAtt($bando->getMonCodiceProceduraAttivazione())
                ->setCodProcAttLocale($bando->getAtto()->getNumero())
                ->setProceduraOperativa($bando);
            $bando->setMonProcAtt($tc1);
        }
        /** @var ProgrammaProcedura $programma */
        $programma = $bando->getMonProcedureProgrammi()->first();
        $tc1->setCodAiutoRna($bando->getMonCodAiutoRna())
            ->setTipProceduraAtt($bando->getMonTipoProceduraAttivazione())
            ->setFlagAiuti($bando->getMonFlagAiuti())
            ->setDescrProceduraAtt($bando->getTitolo())
            ->setDataAvvioProcedura($bando->getMonDataAvvioProcedura())
            ->setDataFineProcedura($bando->getMonDataFineProcedura())
            ->setCodProgramma(true == $programma ? $programma->getTc4Programma()->getCodProgramma() : null)
            ->setFlagFesr('FESR' == $bando->getFondo());

        return $tc1;
    }

    /**
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @Route("/ricalcolo_finanziamento_procedura/{procedura_id}",name="ricalcolo_finanziamento_procedura")
     */
    public function ricalcoloFinanziamentoProcedura($procedura_id): Response
    {
        $this->checkCsrf('token');
        $richieste = $this->getEm()->createQuery(
            "SELECT richiesta, procedura
            FROM RichiesteBundle:Richiesta as richiesta
            INNER JOIN richiesta.procedura as procedura
            INNER JOIN richiesta.attuazione_controllo as atc
            WHERE procedura.id = :procedura_id
            AND richiesta.flag_por = 1
        ")->setParameter('procedura_id', $procedura_id)->iterate();
        
        foreach ($richieste as $richiesta) {
            /** @var IGestoreFinanziamento $service */
            $service = $this->get('monitoraggio.gestore_finanziamento')->getGestore($richiesta[0]);
            $service->aggiornaFinanziamento(true);
            $service->persistFinanziamenti();
        }
        $this->getEm()->flush();
        return $this->addSuccessRedirect("Operazione effettuata con successo", "elenco_procedure_attivazione");
    }

    /**
     * @PaginaInfo(titolo="Voci spesa della procedura", sottoTitolo="l'elenco delle voci spesa associate alla procedura di attivazione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco procedure attivazione")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @Route("voci_spesa_procedura/{id}",name="voci_spesa_procedura")
     */
    public function vociSpesaAction(Request $request, Procedura $procedura): Response
    {
        $form = $this->createForm(VociSpesaProceduraType::class, $procedura,[
            'indietro' => $this->generateUrl('elenco_procedure_attivazione'),
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            try{
                $this->getEm()->flush();
                $this->addSuccess('Informazioni salvate correttamente');
            }
            catch(\Exception $e){
                $this->get('logger')->error($e->getTraceAsString(),['procedura' => $procedura->getId()]);
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        return $this->render('MonitoraggioBundle:ProcedureAttivazione:formVociSpesa.html.twig',[
            'procedura' => $procedura,
            'form' => $form->createView(),
        ]);
    }
     /**
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @PaginaInfo(titolo="Elenco associazione azione indicatore output", sottoTitolo="mostra le associazioni tra un indicatori ed azioni")
     * @Route("/lista_associazioni_azioni_indicatori/{sort}/{direction}/{page}",
     *      name="monitoraggio_associazioni_azioni_indicatori",
     *      defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}
     * )
     */
    public function listAssociazioneAzioniIndicatoriAction(): Response {
        $datiRicerca = new RicercaAssociazioneAzioniIndicatori();
        $ricerca = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('MonitoraggioBundle:AssociazioniAzioniIndicatori:lista.html.twig', [
            'ricerca' => $ricerca,
        ]);
    }

    /**
     * @Route("/elenco_pulisci/{sort}/{direction}/{page}", defaults={ "sort": "i.id", "direction": "asc", "page": "1"}, name="monitoraggio_associazioni_azioni_indicatori_pulisci")
     */
    public function listAssociazioneAzioniIndicatoriPulisciAction(): Response {
        $ricerca = new RicercaAssociazioneAzioniIndicatori();
        $dati = $this->get('ricerca')->pulisci($ricerca);

        return $this->redirectToRoute('monitoraggio_associazioni_azioni_indicatori');
    }

    /**
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @Route("/modifica_associazioni_azioni_indicatori/{id}",name="monitoraggio_associazioni_azioni_indicatori_edit")
     * @PaginaInfo(titolo="Modifica associazione azione indicatore output", sottoTitolo="modifica l'associazione tra un indicatore ed un'azione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={
     *      @ElementoBreadcrumb(testo="Elenco associazione azione indicatore output", route="monitoraggio_associazioni_azioni_indicatori"),
     *      @ElementoBreadcrumb(testo="Modifica associazione azione indicatore output")
     * })
     */
    public function modificaAssociazioneAzioniIndicatoriAction(Request $request, IndicatoriOutputAzioni $ioa ): Response {
        $form = $this->createForm(AssociazioniAzioniIndicatoriType::class, $ioa, [
            'url_indietro' => $this->generateUrl('monitoraggio_associazioni_azioni_indicatori'),
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getEm();
            $connection = $em->getConnection();
            try{
                $connection->beginTransaction();
                /** @var IndicatoreOutputRepository $associazioniRepo */
                $associazioniRepo = $em->getRepository(IndicatoreOutput::class);
                $associazioniRepo->applicaAssociazioneIndicatoriASistema($ioa);
                $this->getEm()->flush();
                $connection->commit();
                return $this->addSuccessRedirect("Informazioni salvate con successo", 'monitoraggio_associazioni_azioni_indicatori');
            }
            catch(\Exception $e){
                $connection->rollBack();
                throw $e;
                $this->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        return $this->render('MonitoraggioBundle:AssociazioniAzioniIndicatori:form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @Route("/aggiungi_associazioni_azioni_indicatori/",name="monitoraggio_associazioni_azioni_indicatori_add")
     * @PaginaInfo(titolo="Modifica associazione azione indicatore output", sottoTitolo="modifica l'associazione tra un indicatore ed un'azione")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={
     *      @ElementoBreadcrumb(testo="Elenco associazione azione indicatore output", route="monitoraggio_associazioni_azioni_indicatori"),
     *      @ElementoBreadcrumb(testo="Modifica associazione azione indicatore output")
     * })
     */
    public function aggiungiAssociazioneAzioniIndicatoriAction(Request $request): Response {
        $form = $this->createForm(AssociazioniAzioniIndicatoriType::class, null, [
            'url_indietro' => $this->generateUrl('monitoraggio_associazioni_azioni_indicatori'),
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            try{
                $record = $form->getData();
                $em = $this->getEm();
                $em->persist($record);
                $em->beginTransaction();
                /** @var IndicatoreOutputRepository $associazioniRepo */
                $em->flush();
                $associazioniRepo = $em->getRepository(IndicatoreOutput::class);
                $associazioniRepo->applicaAssociazioneIndicatoriASistema($record);
                $em->commit();
                return $this->addSuccessRedirect("Informazioni salvate con successo", 'monitoraggio_associazioni_azioni_indicatori');
            }
            catch(\Exception $e){
                $this->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        return $this->render('MonitoraggioBundle:AssociazioniAzioniIndicatori:form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
