<?php
namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use Exception;
use IstruttorieBundle\Entity\PosizioneImpegno;
use IstruttorieBundle\Entity\PropostaImpegno;
use IstruttorieBundle\Form\Entity\RicercaPropostaImpegno;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SfingeBundle\Form\ImportaPropostaImpegnoType;
use SfingeBundle\Form\PosizioneImpegnoType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UtilitiesController extends BaseController
{
    /**
     * @Route("/elenco_proposte_impegno/{sort}/{direction}/{page}",
     *     defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_proposte_impegno")
     * @PaginaInfo(titolo="Elenco proposte impegno")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco proposte impegno")
     * 				})
     */
    public function elencoProposteImpegnoAction(): Response
    {
        $datiRicerca = new RicercaPropostaImpegno();
        $risultato = $this->get('ricerca')->ricerca($datiRicerca);
        $parameters = [
            'proposte_impegni' => $risultato['risultato'],
            'form' => $risultato['form_ricerca'],
            'filtro_attivo' => $risultato['filtro_attivo'],
        ];

        return $this->render('SfingeBundle:SuperAdmin:elencoProposteImpegni.html.twig', $parameters);
    }

    /**
     * @Route("/elenco_proposte_impegno_pulisci", name="elenco_proposte_impegno_pulisci")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function elencoProposteImpegnoPulisciAction(): Response
    {
        $this->get('ricerca')->pulisci(new RicercaPropostaImpegno());
        return $this->redirectToRoute('elenco_proposte_impegno');
    }

    /**
     * @Route("/nuova_proposta_impegno", name="nuova_proposta_impegno")
     * @PaginaInfo(titolo="Nuova proposta impegno")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Template("SfingeBundle:SuperAdmin:dettaglioPropostaImpegno.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco proposte impegno", route="elenco_proposte_impegno"),
     *              @ElementoBreadcrumb(testo="Nuova proposta impegno"),
     * 				})
     */
    public function nuovaPropostaImpegnoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $propostaImpegno = new PropostaImpegno();
        $options['indietro'] = $this->generateUrl('elenco_proposte_impegno');
        $form = $this->createForm('SfingeBundle\Form\PropostaImpegnoType', $propostaImpegno, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->persist($propostaImpegno);
                    $em->flush();
                    $this->addFlash('success', 'Proposta di impegno creata correttamente.');
                    return $this->redirect($this->generateUrl('elenco_proposte_impegno'));
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params['form'] = $form->createView();
        return $form_params;
    }

    /**
     * @Route("/{id_proposta_impegno}/modifica_proposta_impegno", name="modifica_proposta_impegno")
     * @PaginaInfo(titolo="Modifica proposta impegno")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("propostaImpegno", options={"mapping": {"id_proposta_impegno" : "id"}})
     * @Template("SfingeBundle:SuperAdmin:dettaglioPropostaImpegno.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco proposte impegno", route="elenco_proposte_impegno"),
     *              @ElementoBreadcrumb(testo="Proposta impegno"),
     * 				})
     */
    public function modificaPropostaImpegnoAction(Request $request, PropostaImpegno $propostaImpegno)
    {
        $em = $this->getDoctrine()->getManager();
        $options['indietro'] = $this->generateUrl('elenco_proposte_impegno');
        $form = $this->createForm('SfingeBundle\Form\PropostaImpegnoType', $propostaImpegno, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->persist($propostaImpegno);
                    $em->flush();
                    $this->addFlash('success', 'Proposta di impegno salvata correttamente');
                    return $this->redirect($this->generateUrl('elenco_proposte_impegno'));
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params['form'] = $form->createView();
        return $form_params;
    }

    /**
     * @Route("/importa_proposta_impegno", name="importa_proposta_impegno")
     * @PaginaInfo(titolo="Importa proposta di impegno", sottoTitolo="Importa proposta di impegno")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function importaPropostaImpegnoAction(Request $request): Response
    {
        $options = ['indietro' => $this->generateUrl('elenco_proposte_impegno'),];
        $form = $this->createForm(ImportaPropostaImpegnoType::class, null, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $procedura = $form->get('procedura')->getData();
            $propostaImpegnoFile = $form->get('propostaImpegno')->getData();

            if ($propostaImpegnoFile) {
                $esito = $this->get('gestore_proposta_impegno')->importa($propostaImpegnoFile, $procedura);
                if ($esito->esito === 0) {
                    return $this->addSuccessRedirect(
                        'Proposta di impegno importata con successo con ID: '
                        . $esito->idPropostaImpegno, 'elenco_proposte_impegno'
                    );
                } else {
                    $this->addFlash('error', $esito->messaggi);
                }
            } else {
                $this->addFlash('error', 'Selezionare un file di tipo xls..');
            }
        }
        $parameters = ['form' => $form->createView(),];
        return $this->render('SfingeBundle:SuperAdmin:formImportaPropostaImpegno.html.twig', $parameters);
    }

    /**
     * @param PropostaImpegno $propostaImpegno
     * @Route("/{id_proposta_impegno}/crea_proposta_impegno", name="crea_proposta_impegno")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("propostaImpegno", options={"mapping": {"id_proposta_impegno" : "id"}})
     * @return Response|null
     * @throws Exception
     */
    public function creaPropostaImpegnoAction(PropostaImpegno $propostaImpegno): ?Response
    {
        $result = $this->get('app.proposta_impegno')->creaPropostaImpegno($propostaImpegno);
        if ($result->esitoRichiesta == 'OK') {
            $this->addFlash('success', 'Esito richiesta: ' . $result->esitoRichiesta
                . '<br/>ProcessInstanceID: ' . $result->ProcessInstanceID);
        } else {
            $this->addFlash('error', 'Errore nella richiesta:' . $result->messaggi);
        }

        return $this->redirectToRoute('elenco_proposte_impegno');
    }

    /**
     * @Route("/{id_proposta_impegno}/elenco_posizioni_impegno/{sort}/{direction}/{page}",
     *     defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_posizioni_impegno")
     * @PaginaInfo(titolo="Elenco posizioni impegno")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("propostaImpegno", options={"mapping": {"id_proposta_impegno" : "id"}})
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco posizioni impegno")
     * 				})
     */
    public function elencoPosizioniImpegnoAction(PropostaImpegno $propostaImpegno): Response
    {
        $posizioniImpegno = $this->getEm()->getRepository('IstruttorieBundle:PosizioneImpegno')
            ->findBy(['proposta_impegno' => $propostaImpegno->getId()]);
        $parameters = [
            'posizioni_impegno' => $posizioniImpegno,
            'proposta_impegno' => $propostaImpegno,
        ];

        return $this->render('SfingeBundle:SuperAdmin:elencoPosizioniImpegno.html.twig', $parameters);
    }

    /**
     * @Route("{id_proposta_impegno}/nuova_posizione_impegno", name="nuova_posizione_impegno")
     * @PaginaInfo(titolo="Nuova posizione impegno")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("propostaImpegno", options={"mapping": {"id_proposta_impegno" : "id"}})
     * @Template("SfingeBundle:SuperAdmin:dettaglioPosizioneImpegno.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco proposte impegno", route="elenco_proposte_impegno"),
     *              @ElementoBreadcrumb(testo="Elenco posizioni impegno", route="elenco_posizioni_impegno", parametri={"id_proposta_impegno"}),
     *              @ElementoBreadcrumb(testo="Nuova posizione impegno"),
     * 				})
     */
    public function nuovaPosizioneImpegnoAction(Request $request, PropostaImpegno $propostaImpegno)
    {
        $posizioneImpegno = new PosizioneImpegno();
        $posizioneImpegno->setPropostaImpegno($propostaImpegno);
        $options['indietro'] = $this->generateUrl('elenco_posizioni_impegno', ['id_proposta_impegno' => $propostaImpegno->getId()]);
        $form = $this->createForm('SfingeBundle\Form\PosizioneImpegnoType', $posizioneImpegno, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($posizioneImpegno);
                    $em->flush();
                    $this->addFlash('success', 'Posizione impegno creata correttamente.');
                    return $this->redirect($this->generateUrl('elenco_posizioni_impegno', [
                        'id_proposta_impegno' => $propostaImpegno->getId()]));
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params['form'] = $form->createView();
        return $form_params;
    }

    /**
     * @Route("/{id_posizione_impegno}/modifica_posizione_impegno", name="modifica_posizione_impegno")
     * @PaginaInfo(titolo="Modifica posizione impegno")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("posizioneImpegno", options={"mapping": {"id_posizione_impegno" : "id"}})
     * @Template("SfingeBundle:SuperAdmin:dettaglioPosizioneImpegno.html.twig")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco proposte impegno", route="elenco_proposte_impegno"),
     *              @ElementoBreadcrumb(testo="Posizione impegno"),
     * 				})
     */
    public function modificaPosizioneImpegnoAction(Request $request, PosizioneImpegno $posizioneImpegno)
    {
        $em = $this->getDoctrine()->getManager();
        $options['indietro'] = $this->generateUrl('elenco_posizioni_impegno', [
            'id_proposta_impegno' => $posizioneImpegno->getId()]);
        $form = $this->createForm('SfingeBundle\Form\PosizioneImpegnoType', $posizioneImpegno, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $em->persist($posizioneImpegno);
                    $em->flush();
                    $this->addFlash('success', 'Posizione di impegno salvata correttamente');
                    return $this->redirect($this->generateUrl('elenco_posizioni_impegno', [
                        'id_proposta_impegno' => $posizioneImpegno->getPropostaImpegno()->getId()]));
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params['form'] = $form->createView();
        return $form_params;
    }

    /**
     * @Route("/{id_posizione_impegno}/elimina_posizione_impegno", name="elimina_posizione_impegno")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @ParamConverter("posizioneImpegno", options={"mapping": {"id_posizione_impegno" : "id"}})
     */
    public function eliminaPosizioneImpegnoAction(PosizioneImpegno $posizioneImpegno): RedirectResponse
    {
        $this->get('base')->checkCsrf('token');
        $idPropostaImpegno = $posizioneImpegno->getPropostaImpegno()->getId();
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($posizioneImpegno);
            $em->flush();
            $this->addFlash('success', "Posizione impegno eliminata correttamente.");
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('elenco_posizioni_impegno', [
            'id_proposta_impegno' => $idPropostaImpegno]);
    }
}
