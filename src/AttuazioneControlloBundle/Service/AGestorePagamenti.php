<?php

namespace AttuazioneControlloBundle\Service;

use BaseBundle\Service\BaseService;
use BaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use SfingeBundle\Entity\Procedura;
use BaseBundle\Exception\SfingeException;
use SoggettoBundle\Entity\Soggetto;

/**
 * @package AttuazioneControlloBundle\Service
 */
abstract class AGestorePagamenti extends BaseService implements IGestorePagamenti {

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

    protected function generaPdfPagamento($pagamento, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {

        if (!$pagamento->getStato()->uguale(\AttuazioneControlloBundle\Entity\StatoPagamento::PAG_INSERITO)) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }
        
        $richiesta = $pagamento->getRichiesta();
        

        $pdf = $this->container->get("pdf");

        $dati = array();
        $dati = array_merge_recursive($dati, $datiAggiuntivi);

        $dati["pagamento"] = $pagamento;
        $dati["procedura"] = $richiesta->getProcedura();
        $dati["richiesta"] = $richiesta;
        $dati["capofila"] = $this->getCapofila();
        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $opzioni = array();

        $dati['facsimile'] = $facsimile;
        $pdf->load($twig, $dati);
        //return $this->render($twig,$dati);
        //TODO mettere gestione fac simile

        if ($download) {
            $pdf->download($this->getNomePdfPagamento($pagamento));
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    protected function getNomePdfPagamento($pagamento) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Richiesta di pagamento " . $pagamento->getId() . " " . $data;
    }

    public function getSoggetto() {
        $soggetto = $this->getSession()->get(BaseController::SESSIONE_SOGGETTO);
        if (is_null($soggetto)) {
            throw new \Exception("Soggetto non specificato");
        }
        $soggetto = $this->getEm()->merge($soggetto);
        return $soggetto;
    }

    /**
     * @return Soggetto
     */
    public function getCapofila() {
        return $this->getSoggetto();
    }

}
