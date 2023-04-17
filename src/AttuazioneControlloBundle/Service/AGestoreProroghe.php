<?php

namespace AttuazioneControlloBundle\Service;

use BaseBundle\Service\BaseService;
use AttuazioneControlloBundle\Entity\Proroga;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package AttuazioneControlloBundle\Service
 */
abstract class AGestoreProroghe extends BaseService implements IGestoreProroghe {

    protected function getNomePdfProroga($proroga) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Richiesta di proroga " . $proroga->getId() . " " . $data;
    }

    protected function generaPdfProroga($proroga, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {
        if (!$proroga->getStato()->uguale(\AttuazioneControlloBundle\Entity\StatoProroga::PROROGA_INSERITA)) {
            throw new SfingeException("Impossibile generare il pdf della proroga nello stato in cui si trova");
        }
        $pdf = $this->container->get("pdf");
        $dati = array();

        $dati = array_merge_recursive($dati, $datiAggiuntivi);

        $richiesta = $proroga->getRichiesta();
        $dati["proroga"] = $proroga;
        $dati["richiesta"] = $richiesta;
        $dati["capofila"] = $richiesta->getMandatario()->getSoggetto();
        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $opzioni = array();

        $dati['facsimile'] = $facsimile;
        $pdf->load($twig, $dati);

        if ($download) {
            $pdf->download($this->getNomePdfProroga($proroga));
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

}
