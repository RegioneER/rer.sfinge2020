<?php
namespace FunzioniServizioBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use AttuazioneControlloBundle\Entity\DatiBancari;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_135;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SfingeBundle\Entity\Procedura;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FunzioniBandoLiquidazioneController
 *
 * @Route("/liquidazione")
 */
class FunzioniLiquidazioneController extends Controller
{
    const ARRAY_BANDI = [133, 135, 136, 139, 150, 151, 163, 164];

    /**
     * @Route("/", name="funzionalita_liquidazione")
     * @Security("has_role('ROLE_UTENTE_PA')")
     * @return Response|null
     */
    public function indexAction(): ?Response
    {
        return $this->render('FunzioniServizioBundle:Liquidazione:index.html.twig');
    }

    /**
     * @Route("/crea_quietanza_elenco_procedure/", name="crea_quietanza_elenco_procedure")
     * @Security("has_role('ROLE_UTENTE_PA')")
     * @return Response|null
     */
    public function creaQuietanzaElencoProcedureAction(): ?Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $procedure = $em->getRepository("SfingeBundle:Bando")->findBy(['id' => self::ARRAY_BANDI]);
        return $this->render('@FunzioniServizio/Liquidazione/creaQuietanzaElencoProcedure.html.twig',
            ['procedure' => $procedure]
        );
    }

    /**
     * @Route("/crea_quietanza_elenco_richieste/{procedura_id}", name="crea_quietanza_elenco_richieste")
     * @ParamConverter("procedura", options={"mapping": {"procedura_id" : "id"}})
     * @param Procedura $procedura
     * @return Response|null
     */
    public function creaQuietanzaElencoRichiesteAction(Procedura $procedura): ?Response
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $istruttorieAmmesse = $em->getRepository("IstruttorieBundle:IstruttoriaRichiesta")
            ->getIstruttoriePerBando($procedura->getId(), true);

        $nrIstruttorieSelezionabili = 0;
        foreach ($istruttorieAmmesse as $istruttoriaAmmessa) {
            $istruttoriaAmmessa->selezionabile = false;

            /** @var Proponente $proponente */
            $proponente = $istruttoriaAmmessa->getRichiesta()->getMandatario();
            /** @var DatiBancari $datiBancari */
            $datiBancari = $proponente->getDatiBancari()->first();
            $istruttoriaAmmessa->datiBancari = $datiBancari;
            $lifnr = $this->get('app.liquidazione_service')->getLifnrRichiesta($istruttoriaAmmessa->getRichiesta());
            $proponente->getSoggetto()->setLifnrSap($lifnr);

            if ($lifnr && $datiBancari) {
                $istruttoriaAmmessa->selezionabile = true;
                $nrIstruttorieSelezionabili++;
            }
        }

        return $this->render('@FunzioniServizio/Liquidazione/creaQuietanzaElencoRichieste.html.twig', [
            'istruttorie_ammesse' => $istruttorieAmmesse,
                'procedura' => $procedura,
                'nr_istruttorie_selezionabili' => $nrIstruttorieSelezionabili,]
        );
    }

    /**
     * @Route("/crea_quietanza", name="crea_quietanza")
     * @param Request $request
     * @return Response|null
     * @throws Exception
     */
    public function creaQuietanzaAction(Request $request): ?Response
    {
        $arrayEsitoCreazioneQuietanze = [];
        $ambiente = 'Dev';
        if ($request->request->has('submit-prod')) {
           $ambiente = 'Prod';
        }

        $elencoRichieste = $request->get('check');
        if (!empty($elencoRichieste)) {
            $arrayEsitoCreazioneQuietanze = $this->get('app.liquidazione_service')
                ->generaQuietanzeBatch($elencoRichieste, $ambiente);
        }

        return $this->render('@FunzioniServizio/Liquidazione/elencoQuietanzeCreate.html.twig', [
            'esito_creazione_quietanze' => $arrayEsitoCreazioneQuietanze,
                'procedura_id' => $request->get('procedura_id')
            ]
        );
    }

    /**
     * @Route("/crea_partita_elenco_procedure/", name="crea_partita_elenco_procedure")
     * @Security("has_role('ROLE_UTENTE_PA')")
     * @return Response|null
     */
    public function creaPartitaElencoProcedureAction(): ?Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $procedure = $em->getRepository("SfingeBundle:Bando")->findBy(['id' => self::ARRAY_BANDI]);
        return $this->render('@FunzioniServizio/Liquidazione/creaPartitaElencoProcedure.html.twig',
            ['procedure' => $procedure]
        );
    }

    /**
     * @Route("/crea_partita_elenco_richieste/{procedura_id}", name="crea_partita_elenco_richieste")
     * @ParamConverter("procedura", options={"mapping": {"procedura_id" : "id"}})
     * @param Procedura $procedura
     * @return Response|null
     */
    public function creaPartitaElencoRichiesteAction(Procedura $procedura): ?Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        /** @var IstruttoriaRichiesta[] $istruttorieAmmesse */
        $istruttorieAmmesse = $em->getRepository("IstruttorieBundle:IstruttoriaRichiesta")
            ->getIstruttoriePerBando($procedura->getId(), true);

        $istruttorieAmmesseConPartite = [];
        $istruttorieAmmesseSenzaPartite = [];
        $nrIstruttorieSelezionabili = 0;
        foreach ($istruttorieAmmesse as $istruttoriaAmmessa) {
            $richiesta = $istruttoriaAmmessa->getRichiesta();
            $istruttoria = $richiesta->getIstruttoria();
            $istruttoria->selezionabile = false;

            $lifnr = $this->get('app.liquidazione_service')->getLifnrRichiesta($istruttoriaAmmessa->getRichiesta());
            /** @var Proponente $proponente */
            $proponente = $istruttoriaAmmessa->getRichiesta()->getMandatario();
            $proponente->getSoggetto()->setLifnrSap($lifnr);

            if (!empty($richiesta->getAttuazioneControllo()) && $richiesta->getAttuazioneControllo()->getPartite()->count() > 0) {
                $istruttorieAmmesseConPartite[] = $istruttoria;
            } else {
                if (!empty($richiesta->getMandatario()->getSoggetto()->getLifnrSap())
                    && !empty($richiesta->getAttuazioneControllo())
                    && !empty($richiesta->getProcedura()->getCentroDiCosto())
                    && !empty($istruttoria->getAttoConcessioneAtc()) && !empty($istruttoria->getAttoConcessioneAtc()->getDataPubblicazione())
                    && !empty($istruttoria->getAttoConcessioneAtc()) && !empty($istruttoria->getAttoConcessioneAtc()->getNumero())
                    && !empty($istruttoria->getNumeroImpegno())
                    && !empty($istruttoria->getPosizioneImpegno())
                    && !empty($istruttoria->getContributoAmmesso())
                ) {
                    $istruttoria->selezionabile = true;
                    $nrIstruttorieSelezionabili++;
                }

                $istruttorieAmmesseSenzaPartite[] = $istruttoria;
            }
        }

        return $this->render('@FunzioniServizio/Liquidazione/creaPartitaElencoRichieste.html.twig', [
                'istruttorie_ammesse_con_partite' => $istruttorieAmmesseConPartite,
                'istruttorie_ammesse_senza_partite' => $istruttorieAmmesseSenzaPartite,
                'nr_istruttorie_selezionabili' => $nrIstruttorieSelezionabili,
                'procedura' => $procedura
            ]
        );
    }

    /**
     * @Route("/crea_partita_elenco_richieste_bando_135/{procedura_id}", name="crea_partita_elenco_richieste_bando_135")
     * @ParamConverter("procedura", options={"mapping": {"procedura_id" : "id"}})
     * @param Procedura $procedura
     * @return Response|null
     */
    public function creaPartitaElencoRichiesteBando135Action(Procedura $procedura): ?Response
    {
        ini_set('max_execution_time', -1);
        ini_set("memory_limit", "1024M");

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        /** @var IstruttoriaRichiesta[] $istruttorieAmmesse */
        $istruttorieAmmesse = $em->getRepository("IstruttorieBundle:IstruttoriaRichiesta")
            ->getIstruttoriePerBando($procedura->getId(), true);

        $gestoreRichiestaBando135Obj = new GestoreRichiesteBando_135($this->container);
        $istruttorieAmmesseRetVal = [];
        foreach ($istruttorieAmmesse as $istruttoriaAmmessa) {
            $richiesta = $istruttoriaAmmessa->getRichiesta();
            $istruttoria = $richiesta->getIstruttoria();
            $istruttoria->selezionabile = false;

            $elencoStabilimentiBalneari = $gestoreRichiestaBando135Obj->dammiStabilimentiBalneari($richiesta);

            if (!empty($richiesta->getAttuazioneControllo())
                && !empty($richiesta->getProcedura()->getCentroDiCosto())
                && !empty($istruttoria->getAttoConcessioneAtc()) && !empty($istruttoria->getAttoConcessioneAtc()->getDataPubblicazione())
                && !empty($istruttoria->getAttoConcessioneAtc()) && !empty($istruttoria->getAttoConcessioneAtc()->getNumero())
                && !empty($istruttoria->getNumeroImpegno())
                && !empty($istruttoria->getPosizioneImpegno())
            ) {
                $istruttoria->selezionabile = true;
            }

            $istruttoria->partiteCreate = $richiesta->getAttuazioneControllo()->getPartite()->count();
            $istruttoria->numeroStabilimentiBalneari = count($elencoStabilimentiBalneari);
            $istruttorieAmmesseRetVal[] = $istruttoria;
        }

        return $this->render('@FunzioniServizio/Liquidazione/creaPartitaElencoRichiesteBando135.html.twig', [
                'istruttorie_ammesse' => $istruttorieAmmesseRetVal,
                'procedura' => $procedura
            ]
        );
    }

    /**
     * @Route("/crea_partita_elenco_stabilimenti_balneari/{richiesta_id}", name="crea_partita_elenco_stabilimenti_balneari")
     * @ParamConverter("richiesta", options={"mapping": {"richiesta_id" : "id"}})
     * @param Richiesta $richiesta
     * @return Response|null
     */
    public function creaPartitaElencoStabilimentiBalneariAction(Richiesta $richiesta): ?Response
    {
        $istruttoria = $richiesta->getIstruttoria();
        $gestoreRichiestaBando135Obj = new GestoreRichiesteBando_135($this->container);
        $elencoStabilimentiBalneari = $gestoreRichiestaBando135Obj->dammiStabilimentiBalneari($richiesta);

        $partiteCreate = null;
        if (!empty($richiesta->getAttuazioneControllo())) {
            $partiteCreate = $richiesta->getAttuazioneControllo()->getPartite();
        }

        $elencoStabilimentiBalneariRetVal = [];
        foreach ($elencoStabilimentiBalneari as $stabilimentoBalneare) {
            $stabilimentoBalneare['selezionabile'] = false;
            if (!empty($stabilimentoBalneare['lifnr_sap'])
                && !empty($richiesta->getAttuazioneControllo())
                && !empty($richiesta->getProcedura()->getCentroDiCosto())
                && !empty($istruttoria->getAttoConcessioneAtc()) && !empty($istruttoria->getAttoConcessioneAtc()->getDataPubblicazione())
                && !empty($istruttoria->getAttoConcessioneAtc()) && !empty($istruttoria->getAttoConcessioneAtc()->getNumero())
                && !empty($istruttoria->getNumeroImpegno())
                && !empty($istruttoria->getPosizioneImpegno())
                && !empty($stabilimentoBalneare['contributo_ammesso'])
            ) {
                $stabilimentoBalneare['selezionabile'] = true;
            }
            $elencoStabilimentiBalneariRetVal[] = $stabilimentoBalneare;
        }

        return $this->render('@FunzioniServizio/Liquidazione/creaPartitaElencoStabilimentiBalneari135.html.twig', [
                'elenco_stabilimenti_balneari' => $elencoStabilimentiBalneariRetVal,
                'partite_create' => $partiteCreate,
                'istruttoria' => $istruttoria,
            ]
        );
    }

    /**
     * @Route("/crea_partita", name="crea_partita")
     * @param Request $request
     * @return Response|null
     * @throws Exception
     */
    public function creaPartitaAction(Request $request): ?Response
    {
        $arrayEsitoCreazionePartite = [];
        $ambiente = 'Dev';
        if ($request->request->has('submit-prod')) {
            $ambiente = 'Prod';
        }

        $elencoRichieste = $request->get('check');
        if (!empty($elencoRichieste)) {
            $arrayEsitoCreazionePartite = $this->get('app.liquidazione_service')
                ->generaPartiteBatch($elencoRichieste, $ambiente);
        }

        return $this->render('@FunzioniServizio/Liquidazione/elencoPartiteCreate.html.twig', [
            'esito_creazione_partite' => $arrayEsitoCreazionePartite,
                'procedura_id' => $request->get('procedura_id')
            ]
        );
    }

    /**
     * @Route("/crea_partita_stabilimenti_balneari", name="crea_partita_stabilimenti_balneari")
     * @param Request $request
     * @return Response|null
     * @throws Exception
     */
    public function creaPartitaStabilimentiBalneariAction(Request $request): ?Response
    {
        $arrayEsitoCreazionePartite = [];
        $ambiente = 'Dev';
        if ($request->request->has('submit-prod')) {
            $ambiente = 'Prod';
        }

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $richiesta_id = $request->get('richiesta_id');
        $procedura_id = $request->get('procedura_id');
        $idStabilimentiBalneariSelezionati = $request->get('check');
        if (!empty($richiesta_id)) {
            $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($richiesta_id);
            $istruttoria = $richiesta->getIstruttoria();
            $gestoreRichiestaBando135Obj = new GestoreRichiesteBando_135($this->container);
            $elencoStabilimentiBalneari = $gestoreRichiestaBando135Obj->dammiStabilimentiBalneari($richiesta);

            foreach ($elencoStabilimentiBalneari as $key => $stabilimentiBalneare) {
                if (!in_array($stabilimentiBalneare['id'], $idStabilimentiBalneariSelezionati)) {
                    unset($elencoStabilimentiBalneari[$key]);
                }
            }

            $retVal = [];
            foreach ($elencoStabilimentiBalneari as $stabilimentiBalneare) {
                $data['budat'] = date("Y-m-d"); // Data di registrazione nel documento
                $data['bldat'] = $istruttoria->getAttoConcessioneAtc()->getDataPubblicazione()->format('Y-m-d'); // Data atto di concessione
                //$data['zlsch'] = 4; // Fisso viene già passato dalla funzione creaPartita
                $data['xblnr'] = $istruttoria->getAttoConcessioneAtc()->getNumero(); // Numero di adozione dell’atto di concessione
                $data['zz_num_loc'] = '000001'; // Si era deciso per tutti 000001 Regione Emilia-Romagna
                $data['lifnr'] = $stabilimentiBalneare['lifnr_sap'];
                $data['kblnr'] = $istruttoria->getNumeroImpegno(); // Numero impegno
                $data['kblpos'] = $istruttoria->getPosizioneImpegno(); // Posizione impegno
                $data['wrbtr'] = $stabilimentiBalneare['contributo_ammesso']; // Importo lordo
                $data['kostl'] = $istruttoria->getProcedura()->getCentroDiCosto(); // Centro di costo
                $data['richiesta_id'] = $richiesta->getId();

                $retVal[] = $data;
            }

            if ($retVal) {
                $arrayEsitoCreazionePartite = $this->get('app.liquidazione_service')
                    ->generaPartiteStabilimentiBalneariBatch($retVal, $ambiente);
            }
        }

        return $this->render('@FunzioniServizio/Liquidazione/elencoPartiteCreate.html.twig', [
                'esito_creazione_partite' => $arrayEsitoCreazionePartite,
                'procedura_id' => $procedura_id,
            ]
        );
    }
}
