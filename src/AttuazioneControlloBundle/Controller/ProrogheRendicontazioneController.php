<?php

namespace AttuazioneControlloBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use BaseBundle\Service\RicercaService;
use AttuazioneControlloBundle\Form\Entity\RicercaProrogaRendicontazione;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Symfony\Component\HttpFoundation\Request;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;
use AttuazioneControlloBundle\Entity\Proroga;
use AttuazioneControlloBundle\Repository\ProrogheRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use AttuazioneControlloBundle\Form\ProrogaRendicontazioneType;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use BaseBundle\Form\SalvaIndietroType;
use AttuazioneControlloBundle\Form\ProrogaRendicontazione\AttuazioneControlloRichiestaType;
use Doctrine\Common\Collections\ArrayCollection;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiestaRepository;

/**
 * @Route("/pa/proroghe_rendicontazione/")
 */
class ProrogheRendicontazioneController extends BaseController {
    const ROTTA_ELENCO = 'elenco_proroghe_rendicontazione';

    /**
     * @Route(
     *     "elenco/{sort}/{direction}/{page}",
     *     defaults={"sort" : "i.id",
     *     "direction" : "asc", "page" : "1"},
     *     name=ProrogheRendicontazioneController::ROTTA_ELENCO
     * )
     * @Menuitem(menuAttivo="elenco-proroghe-rendicontazione")
     * @PaginaInfo(
     *     titolo="Elenco proroghe di rendicontazione",
     *     sottoTitolo="mostra l'elenco delle proroghe di rendicontazione"
     * )
     */
    public function elencoAction(): Response {
        $oggettoRicerca = new RicercaProrogaRendicontazione();
        /** @var RicercaService $ricercaService */
        $ricercaService = $this->get('ricerca');
        $ricerca = $ricercaService->ricerca($oggettoRicerca);

        return $this->render('AttuazioneControlloBundle:ProrogaRendicontazione:elenco.html.twig', $ricerca);
    }

    /**
     * @Route("elenco", name="elenco_proroghe_rendicontazione_pulisci")
     */
    public function elencoPulisci(): Response {
        $oggettoRicerca = new RicercaProrogaRendicontazione();
        /** @var RicercaService $ricercaService */
        $ricercaService = $this->get('ricerca');
        $ricercaService->pulisci($oggettoRicerca);

        return $this->redirectToRoute(ProrogheRendicontazioneController::ROTTA_ELENCO);
    }

    /**
     * @Route(
     *     "aggiungi",
     *     defaults={"sort" : "i.id",
     *     "direction" : "asc", "page" : "1"},
     *     name="aggiungi_proroghe_rendicontazione"
     * )
     * @Menuitem(menuAttivo="elenco-proroghe-rendicontazione")
     * @PaginaInfo(
     *     titolo="Elenco proroghe di rendicontazione",
     *     sottoTitolo="mostra l'elenco delle proroghe di rendicontazione"
     * )
     * @Breadcrumb(
     *     elementi={
     *         @ElementoBreadcrumb(testo="Elenco proroghe di rendicontazione", route=ProrogheRendicontazioneController::ROTTA_ELENCO),
     *         @ElementoBreadcrumb(testo="Aggiungi proroga di rendicontazione")
     *     }
     * )
     */
    public function aggiungiProrogaAction(Request $request): Response {
        $indietro = $this->generateUrl(self::ROTTA_ELENCO);
        $form = $this->createForm(ProrogaRendicontazioneType::class, null, [
            'url_indietro' => $indietro,
            'nuova' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var ProrogaRendicontazione|null $prorogaRendicontazione */
                $prorogaRendicontazione = $form->getData();
                $atc = $prorogaRendicontazione->getAttuazioneControlloRichiesta();
                $atc->addProrogheRendicontazione($prorogaRendicontazione);
                $em = $this->getEm();
                $em->persist($prorogaRendicontazione);
                $em->flush();

                return $this->addSuccessRedirect('Proroga della rendicontazione inserita con successo', self::ROTTA_ELENCO);
            } catch (\Exception $e) {
                $this->logError($e->getTraceAsString());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        return $this->render('AttuazioneControlloBundle:ProrogaRendicontazione:aggiungi.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("cerca_proroghe_api",
     *     name="cerca_proroghe_api"
     * )
     */
    public function getProroghe(Request $request): Response {
        $query = $request->query;
        if (!$query->has('q')) {
            return new JsonResponse([]);
        }
        $termineRicerca = $query->get('q');

        /** recupero il risultato 
         * @var AttuazioneControlloRichiestaRepository $repo */
        $repo = $this->getEm()->getRepository(AttuazioneControlloRichiesta::class);
        $res = $repo->cercaProtocollo($termineRicerca);
        //Normalizza il risultato
        $risultati = \array_map(function (AttuazioneControlloRichiesta $atc) {
            $richiesta = $atc->getRichiesta();
            return [
                'id' => $atc->getId(),
                'text' => $richiesta->getProtocollo(),
                'soggetto' => (string) $richiesta->getSoggetto(),
                'titolo' => $richiesta->getTitolo(),
            ];
        }, $res);

        return new JsonResponse(['results' => $risultati]);
    }

    /**
     * @Route(
     *     "modifica/{id}",
     *     defaults={"sort" : "i.id",
     *     "direction" : "asc", "page" : "1"},
     *     name="modifica_proroghe_rendicontazione"
     * )
     * @Menuitem(menuAttivo="elenco-proroghe-rendicontazione")
     * @PaginaInfo(
     *     titolo="Elenco proroghe di rendicontazione",
     *     sottoTitolo="mostra l'elenco delle proroghe di rendicontazione"
     * )
     * @Breadcrumb(
     *     elementi={
     *         @ElementoBreadcrumb(testo="Elenco proroghe di rendicontazione", route=ProrogheRendicontazioneController::ROTTA_ELENCO),
     *         @ElementoBreadcrumb(testo="Modifica proroga di rendicontazione")
     *     }
     * )
     */
    public function modificaProrogaAction(Request $request, ProrogaRendicontazione $prorogaRendicontazione): Response {
        $indietro = $this->generateUrl(self::ROTTA_ELENCO);
        $form = $this->createForm(ProrogaRendicontazioneType::class, $prorogaRendicontazione, [
            'url_indietro' => $indietro,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em = $this->getEm();
                $em->flush();

                return $this->addSuccessRedirect('Proroga della rendicontazione modificata con successo', self::ROTTA_ELENCO);
            } catch (\Exception $e) {
                $this->logError($e->getTraceAsString());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        return $this->render('AttuazioneControlloBundle:ProrogaRendicontazione:aggiungi.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("elimina_proroga_rendicontazione/{id}",
     *     name="elimina_proroga_rendicontazione"
     * )
     */
    public function eliminaProrogaRendicontazione(ProrogaRendicontazione $proroga): Response {
        $this->checkCsrf('token');

        $em = $this->getEm();
        try {
            $em->remove($proroga);
            $em->flush($proroga);
            $this->addSuccess('Proroga di rendicontazione cancellata con successo');
        } catch (\Exception $e) {
            $this->logError($e->getTraceAsString());
            $this->addError('Errore durante la cancellazione della proroga di rendicontazione');
        }

        return $this->redirectToRoute(self::ROTTA_ELENCO);
    }

    /**
     * @Route(
     *     "gestione_proroghe/{id}",
     *     defaults={"sort" : "i.id",
     *     "direction" : "asc", "page" : "1"},
     *     name="gestione_proroghe_rendicontazione"
     * )
     * @Menuitem(menuAttivo="elencoRichiesteAttuazione")
     * @PaginaInfo(
     *     titolo="Gestione proroghe di rendicontazione",
     *     sottoTitolo=""
     * )
     * @Breadcrumb(
     *     elementi={
     *         @ElementoBreadcrumb(testo="Attuazione operazioni", route="elenco_gestione_pa"),
     *         @ElementoBreadcrumb(testo="Gestione proroghe")
     *     }
     * )
     */
    public function gestioneProrogheDaElencoOperazioneAction(Request $request, AttuazioneControlloRichiesta $atc): Response {
        $indietro = $this->generateUrl('elenco_gestione_pa');

        $elementi = new ArrayCollection();
        foreach ($atc->getProrogheRendicontazione() as $p) {
            $elementi->add($p);
        }

        $form = $this->createForm(AttuazioneControlloRichiestaType::class, $atc)
        ->add('submit', SalvaIndietroType::class, [
            'url' => $indietro,
        ])
        ->handleRequest($request);
        $em = $this->getEm();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                foreach ($elementi as $p) {
                    if (false === $atc->getProrogheRendicontazione()->contains($p)) {
                        // remove the Task from the Tag
                        $atc->removeProrogheRendicontazione($p);
                        $em->remove($p);
                    }
                }

                foreach ($atc->getProrogheRendicontazione() as $e) {
                        $em->persist($e);
                }

                $em->flush();
                $this->addSuccess('Proroghe salvate con successo');

                return $this->redirect($indietro);
            } catch (\Exception $e) {
                throw $e;
                $this->logError($e->getTraceAsString());
                $this->addError('Si è verificato un errrore durante il salvataggio delle informazioni');
            }
        }

        return $this->render('AttuazioneControlloBundle:ProrogaRendicontazione:gestione.hmtl.twig', [
            'form' => $form->createView(),
        ]);
    }
}
