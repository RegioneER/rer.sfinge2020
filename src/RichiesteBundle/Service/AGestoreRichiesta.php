<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 22/01/16
 * Time: 09:24
 */

namespace RichiesteBundle\Service;

use BaseBundle\Controller\BaseController;
use BaseBundle\Entity\StatoRichiesta;
use BaseBundle\Exception\SfingeException;
use BaseBundle\Service\BaseService;
use Doctrine\ORM\EntityManagerInterface;
use FascicoloBundle\Entity\Fascicolo;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use RichiesteBundle\Entity\Proponente;

/**
 *
 * Classe usata per mettere i metodi di servizio tra tutti i gestori di richieste
 * Class AGestoreRichiesta
 *
 * @package RichiesteBundle\Service
 */
abstract class AGestoreRichiesta extends BaseService implements IGestoreRichiesta {

    /**
     * @return Session
     */
    public function getSession() {
        $session = $this->container->get('request_stack')->getCurrentRequest()->getSession();
        return $session;
    }

    public function getCurrentRequest() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        return $request;
    }

    /**
     * @throw \Exception
     */
    public function getSoggetto(): Soggetto {
        $soggetto = $this->getSession()->get(BaseController::SESSIONE_SOGGETTO);
        if (\is_null($soggetto)) {
            throw new \Exception("Soggetto non specificato");
        }

        $soggettoOut = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->find($soggetto);
        return $soggettoOut;
    }

    /**
     * @return Soggetto
     */
    public function getCapofila() {
        return $this->getSoggetto();
    }

    /**
     * @return Procedura
     * @throws SfingeException
     */
    public function getProcedura() {
        $id_bando = $this->container->get("request_stack")->getCurrentRequest()->get("id_bando");
        if (is_null($id_bando)) {
            $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
            if (is_null($id_richiesta)) {
                throw new SfingeException("Nessun id_richiesta indicato");
            }
            $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
            if (is_null($richiesta)) {
                throw new SfingeException("Nessuna richiesta trovata");
            }
            return $richiesta->getProcedura();
        }
        throw new SfingeException("Nessuna richiesta trovata");
    }

    /**
     * @return Fascicolo[]
     */
    public function getFascicoli($tipo = null) {
        $fascicoli = array();
        foreach ($this->getProcedura()->getFascicoliProcedura() as $fascioloProcedura) {
            if (!is_null($tipo)) {
                if ($fascioloProcedura->getTipoFascicolo() == $tipo) {
                    $fascicoli[] = $fascioloProcedura->getFascicolo();
                }
            } else {
                $fascicoli[] = $fascioloProcedura->getFascicolo();
            }
        }
        return $fascicoli;
    }

    /**
     * @return Fascicolo[]
     */
    public function getFascicoloProponente() {
        // TODO: Implement getFascicoloProponente() method.
    }

    /**
     * @return integer
     */
    public function numeroMaxProponenti() {
        $this->getProcedura()->getNumeroProponenti();
    }

    /**
     * @return Richiesta
     * @throws SfingeException
     */
    public function getRichiesta() {
        $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
        if (is_null($id_richiesta)) {
            throw new SfingeException("Id richiesta non trovata");
        }
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException("Richiesta non trovata");
        }

        return $richiesta;
    }

    protected function generaPdfDomanda($twig, $datiAggiuntivi = array(), $facsimile = true, $download = true, $orientation = 'portrait') {
        //ini_set("memory_limit", "512M");
        if (!$this->getRichiesta()->getStato()->uguale(StatoRichiesta::PRE_INSERITA)) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }

        $dati = array();
        $dati["procedura"] = $this->getProcedura();
        $dati["capofila"] = $this->getCapofila();
        $dati["firmatario"] = $this->getRichiesta()->getFirmatario();
        $dati["richiesta"] = $this->getRichiesta();
        if ($this->hasDichiarazioniDsnh() == true) {
            $dati['dsnh'] = $this->getRichiesta()->getDichiarazioneDnsh();
        }
        if ($this->hasSezioneAmbitiTematiciS3()) {
            $dati['ambiti_tematici_s3'] = $this->getEm()->getRepository("SfingeBundle:AmbitoTematicoS3")->findAll();
        }
        $dati = array_merge_recursive($dati, $datiAggiuntivi);

        $opzioni = array();
        if (isset($datiAggiuntivi["pdc_annualita"]) && $datiAggiuntivi["pdc_annualita"] > 1) {
            $opzioni["pdc_annualita"] = $datiAggiuntivi["pdc_annualita"];
        }

        if (!isset($datiAggiuntivi["interventi_sede"])) {
            $datiAggiuntivi["interventi_sede"] = false;
        }

        if (!isset($datiAggiuntivi["interventi_richiesta"])) {
            $datiAggiuntivi["interventi_richiesta"] = false;
        }

        if (!isset($datiAggiuntivi["codice_voce"])) {
            $datiAggiuntivi["codice_voce"] = false;
        }

        if (!isset($datiAggiuntivi["tipologia_allegati"])) {
            $datiAggiuntivi["tipologia_allegati"] = false;
        }

        $opzioni["interventi_sede"] = $datiAggiuntivi["interventi_sede"];
        $opzioni["interventi_richiesta"] = $datiAggiuntivi["interventi_richiesta"];
        $opzioni["codice_voce"] = $datiAggiuntivi["codice_voce"];
        $opzioni["tipologia_allegati"] = $datiAggiuntivi["tipologia_allegati"];

        /*
         * AGGIUNGO QUESTO PARAMETRO PER DECIDERE SE FORMATTARE IMPORTO ALLA SORGENTE O NO
         */
        if (!isset($datiAggiuntivi["formatta_importo"])) {
            $datiAggiuntivi["formatta_importo"] = false;
        }
        /*
         * AGGIUNGO QUESTO PARAMETRO PER DECIDERE SE CALCOLARE IL TOTALE ALLA SORGENTE O NO
         */
        if (!isset($datiAggiuntivi["calcola_totale"])) {
            $datiAggiuntivi["calcola_totale"] = false;
        }

        $opzioni["formatta_importo"] = $datiAggiuntivi["formatta_importo"];
        $opzioni["calcola_totale"] = $datiAggiuntivi["calcola_totale"];

        if ($this->getProcedura()->getPianoCostoAttivo() == true) {
            if ($this->getProcedura()->getMultiPianoCosto() == true) {
                foreach ($this->getProponenti() as $proponente) {
                    $dati["pdc_proponenti"][$proponente->getSoggetto()->getDenominazione()] = $this->container->get("gestore_piano_costo")->getGestore()->generaArrayVista($proponente->getId(), $opzioni);
                    if($opzioni["interventi_sede"] == true) {
                        $opzioni_interventi = $opzioni;
                        $opzioni_interventi["pdc_annualita"] = 2;
                        $dati["pdc_proponenti_interventi"][$proponente->getSoggetto()->getDenominazione()] = $this->container->get("gestore_piano_costo")->getGestore()->generaArrayVista($proponente->getId(), $opzioni_interventi);
                    }
                }
                if (!isset($opzioni["pdc_annualita"])) {
                    $dati["pdc_totale"] = $this->container->get("gestore_piano_costo")->getGestore()->generaArrayVistaTotaleRichiestaSingolaAnnualita($this->getRichiesta()->getId(), $opzioni );
                }
            } else {
                $dati["pdc_proponenti"][$this->getProponenteMandatario()->getSoggetto()->getDenominazione()] = $this->container->get("gestore_piano_costo")->getGestore()->generaArrayVista($this->getProponenteMandatario()->getId(), $opzioni);
            }
        }

        $dati['facsimile'] = $facsimile;

        /** @var \PdfBundle\Wrapper\PdfWrapper $pdf */
        $pdf = $this->container->get("pdf");
        $pdf->setPageOrientation($orientation);
        $pdf->load($twig, $dati);

        //TODO mettere gestione fac simile
        //return $this->render($twig, $dati);

        if ($download) {
            $pdf->download($this->getNomePdfDomanda($facsimile));
            //Metto response vuota per non far tornare l'eccezione della mancata response 
            //Stranamente la $pdf->download scatena il download da browser ma non restituisce una response
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    protected function getNomePdfDomanda(bool $facsimile = false) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return ($facsimile ? 'FACSIMILE ' : '') . "Richiesta di finanziamento " . $this->getRichiesta()->getId() . " " . $data;
    }

    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     */
    protected function popolaIterProgetto(Richiesta $richiesta) {
        throw new \Exception("Metodo non implementato");
    }

    protected function getProponenteMandatario(): Proponente {
        return $this->getRichiesta()->getMandatario();
    }

    public function hasDichiarazioniDsnh() {
        return false;
    }

}
