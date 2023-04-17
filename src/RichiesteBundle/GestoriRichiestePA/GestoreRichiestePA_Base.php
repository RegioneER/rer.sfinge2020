<?php
/**
 * @author lfontana
 */

namespace RichiesteBundle\GestoriRichiestePA;

use RichiesteBundle\Service\IGestoreRichiestaPA;
use Doctrine\ORM\EntityManager;
use BaseBundle\Service\BaseService;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\OggettoRichiesta;
use Symfony\Component\HttpFoundation\Response;
use SoggettoBundle\Entity\Sede;
use BaseBundle\Entity\StatoRichiesta;
use Doctrine\Common\Collections\Collection;
use SfingeBundle\Entity\Procedura;
use FascicoloBundle\Entity\IstanzaFascicolo;
use FascicoloBundle\Entity\IstanzaPagina;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\VocePianoCosto;
use SoggettoBundle\Entity\SoggettoRepository;

class GestoreRichiestePA_Base extends BaseService implements IGestoreRichiestaPA {
    const TIPOLOGIA_DOCUMENTI = 'ALL_PROCEDURA_PA';
    const NAME_SPACE = 'RichiesteBundle\GestoriRichiestePA\Riepilogo';
    const RIEPILOGO_PREFIX = 'Riepilogo_';
    /**
     * @var Procedura
     */
    protected $procedura;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Azione[]
     */

    protected $azioni = array();
	
	 /**
     * @var string 
     */
    protected $titoloQuestionario;


    /**
     * @param ContainerInterface $container
     * @param Procedura|null     $procedura
     * @param Richiesta|null     $richiesta
     */
    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        parent::__construct($container);
        $this->logger = $container->get('logger');
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->richiesta = $richiesta;
        $this->procedura = $this->richiesta->getProcedura();
		$this->titoloQuestionario = 'BANDO_' . $this->procedura->getId();
    }

    //Inizializza la richiesta creata
    public function nuovaRichiesta() {
        $mandatario = $this->richiesta->getMandatario();
        $soggetto = $mandatario->getSoggetto();
        /** @var SoggettoRepository */
        $soggettoRepository = $this->getEm()->getRepository("SoggettoBundle:Soggetto");
        $delegati = $soggettoRepository ->getFirmatariAmmissibili($soggetto);
        $form = $this->createForm('RichiesteBundle\Form\Bando60\FirmatarioType', $this->richiesta, [
            'delegati' => $delegati,
        ]);

        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $connection = $this->em->getConnection();
            try {
                $connection->beginTransaction();
                $oggettoRichiesta = $this->getOggettoRichiesta();
                $oggettoRichiesta->setDescrizione($this->titoloQuestionario);

                $this->container->get("sfinge.stati")->avanzaStato($this->richiesta, "PRE_INSERITA");
                $this->container->get('monitoraggio.iter_progetto')->getIstanza($this->richiesta)->aggiungiFasiProcedurali();
                $this->inizializzaPianoCosti();
                $fascicoloProcedura = $this->procedura->getFascicoliProcedura()->first();
                if ($fascicoloProcedura) {
                    $fascicolo = $fascicoloProcedura->getFascicolo();
                    $istanzaFascicolo = new IstanzaFascicolo($fascicolo);
                    $indice = new IstanzaPagina($fascicolo->getIndice());
                    $istanzaFascicolo->setIndice($indice);
                    $oggettoRichiesta->setIstanzaFascicolo($istanzaFascicolo);

                    $this->em->persist($indice);
                    $this->em->persist($istanzaFascicolo);
                }
                $this->beforeRichiestaPersist();
                $this->em->persist($oggettoRichiesta);
                $this->em->persist($this->richiesta);
                if ($soggetto->getSedi()->isEmpty()) {
                    $this->em->persist($this->getSedeLegaleComeSedeOperativa());
                }
                $this->em->flush();
                $connection->commit();

                return $this->addSuccessRedirect('Operazione effettuata con successo', 'procedura_pa_dettaglio_richiesta', ['id_richiesta' => $this->richiesta->getId()]);
            } catch (SfingeException $e) {
                $this->logger->error($e->getMessage());
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                $env = $this->container->getParameter('kernel.environment');
                if($env == 'dev'){
                    throw $e;
                }
                $this->logger->error($e->getMessage());
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $this->addError('Errore durante il salvataggio del beneficiario');
            }
        }

        $pagina = $this->container->get('pagina');
        $pagina->setTitolo('Selezione del beneficiario');
        $pagina->setSottoTitolo('pagina per selezionare il beneficiario della domanda');
        $pagina->aggiungiElementoBreadcrumb('Elenco richieste', $this->generateUrl('procedura_pa_elenco'));

        $dati = [
            'form' => $form->createView(),
        ];

        return $this->render('RichiesteBundle:ProcedureParticolari:nuovaRichiesta.html.twig', $dati);
    }

    /**
     * @return OggettoRichiesta
     */
    protected function getOggettoRichiesta() {
        $oggetto = new OggettoRichiesta();
        $oggetto->setRichiesta($this->richiesta);
        return $oggetto;
    }

    protected function inizializzaPianoCosti(): void {
        $procedura = $this->richiesta->getProcedura();
        $mandatario = $this->richiesta->getMandatario();
        $voci = $procedura->getPianiCosto()->map(function(PianoCosto $piano) use($mandatario) {
            $voce = new VocePianoCosto();
            $voce->setRichiesta($this->richiesta);
            $voce->setProponente($mandatario);
            $voce->setPianoCosto($piano);
            $this->em->persist($voce);

            return  $voce;
        });

        $this->richiesta->setVociPianoCosto($voci);
    }

    /**
     * @param string $tipo
     * @return Collection
     */
    public function getFascicoli($tipo = null) {
        $fascicoliProcedura = $this->procedura->getFascicoliProcedura()->filter(function ($fascicoloProcedura) use ($tipo) {  /* @var \SfingeBundle\Entity\FascicoloProcedura $fascicoloProcedura */
            return \is_null($tipo) || $fascicoloProcedura->getTipo() == $tipo;
        });
        return $fascicoliProcedura->map(function ($fascicoloProcedura) {  /* @var \SfingeBundle\Entity\FascicoloProcedura $fascicoloProcedura */
            return $fascicoloProcedura->getFascicolo();
        });
    }

    public function dettaglioRichiesta() {
        $riepilogo = $this->getRiepilogoRichiestaInstance();
        $riepilogo->validaRichiesta();

        return $this->render('RichiesteBundle:ProcedurePA:dettaglioRichiesta.html.twig', [
            'riepilogo' => $riepilogo,
            'richiesta' => $this->richiesta,
        ]);
    }

    public function gestioneBarraAvanzamento() {
        $statoRichiesta = $this->richiesta->getStato()->getCodice();
        $arrayStati = [
            'Inserita' => true,
            'Completata' => StatoRichiesta::PRE_INVIATA_PA == $statoRichiesta,
        ];

        return $arrayStati;
    }

    /**
     * @return array
     */
    public function getVociMenu() {
        $riepilogo = $this->getRiepilogoRichiestaInstance();

        return $riepilogo->getVociMenu();
    }

    /**
     * @return IRiepilogoRichiesta
     */
    public function getRiepilogoRichiestaInstance() {
        $class = self::NAME_SPACE . '\\' . self::RIEPILOGO_PREFIX . ($this->procedura->getId());
        if (!class_exists($class)) {
            $class = self::NAME_SPACE . '\\' . self::RIEPILOGO_PREFIX . 'Base';
        }

        return new $class($this->container, $this->richiesta);
    }

    public function visualizzaSezione($nome_sezione, array $parametri) {
        return $this->getRiepilogoRichiestaInstance()->visualizzaSezione($nome_sezione, $parametri);
    }

    /**
     * @param string $nome_azione Nome indentificativo dell'azione da richiamare
     * @throws SfingeException
     * @return Response
     */
    public function risultatoAzione($nome_azione) {
        return $this->getRiepilogoRichiestaInstance()->risultatoAzione($nome_azione);
    }

    /**
     * @return Sede
     */
    protected function getSedeLegaleComeSedeOperativa() {
        $proponente = $this->richiesta->getMandatario();
        $soggetto = $proponente->getSoggetto();

        $sede = Sede::SedeFromSoggetto($soggetto);

        return $sede;
    }

    protected function beforeRichiestaPersist(): void {
    }
}
