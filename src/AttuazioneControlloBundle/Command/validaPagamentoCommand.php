<?php

namespace AttuazioneControlloBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\QuietanzaGiustificativo;
use AttuazioneControlloBundle\Entity\Contratto;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AttuazioneControlloBundle\Entity\DocumentoGiustificativo;
use AttuazioneControlloBundle\Entity\DocumentoContratto;
use AttuazioneControlloBundle\Entity\DocumentoPagamento;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use Doctrine\ORM\EntityManagerInterface;
use RichiesteBundle\Entity\Richiesta;

/**
 * @author vdamico
 */
class validaPagamentoCommand extends ContainerAwareCommand {

    private $em;

    public function __construct($name = null) {
        parent::__construct($name);
    }

    protected function configure() {
        $this->setName('pagamenti:validaPagamento')->setDescription('');
        $this->addArgument('id_pagamento', InputArgument::REQUIRED, 'tipo di risorsa da trasmettere');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id_pagamento = $input->getArgument('id_pagamento');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $pagamento = $this->em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        if (!$pagamento->isValidabile()) {
            $output->writeln("<error>Pagamento non validabile</error>");
        } else {
            $resImport = $this->validaPagamento($pagamento, $output);
            if ($resImport == true) {
                $output->writeln("<comment>Pagamento validato</comment>");
            }
        }
    }

    public function validaPagamento($pagamento, $output) {
        ini_set('memory_limit', '2G');

        $id_pagamento = $pagamento->getId();
        //genero il nuovo pdf
        $pdf = $this->generaPdf($id_pagamento, false, false);

        //avanzo lo stato del pagamento
        $this->getContainer()->get("sfinge.stati")->avanzaStato($pagamento, \AttuazioneControlloBundle\Entity\StatoPagamento::PAG_VALIDATO);
        $em = $this->em;

        //lo persisto
        $tipoDocumento = $em->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(\DocumentoBundle\Entity\TipologiaDocumento::PAGAMENTO_CONTRIBUTO);
        $documentoPagamento = $this->getContainer()->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfPagamento($pagamento) . ".pdf", $tipoDocumento, false, $pagamento->getRichiesta());

        //associo il documento al pagamento
        $pagamento->setDocumentoPagamento($documentoPagamento);
        $em->persist($pagamento);

        try {
            $em->flush();
        } catch (\Exception $e) {
            $output->writeln("<error>Impossibile validare il pagamento {$e}</error>");
            return false;
        }

        return true;
    }

    protected function generaPdfPagamento($pagamento, $twig, $datiAggiuntivi = array(), $facsimile = true, $download = true) {
        if (!$pagamento->getStato()->uguale(\AttuazioneControlloBundle\Entity\StatoPagamento::PAG_INSERITO)) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }

        $richiesta = $pagamento->getRichiesta();


        $pdf = $this->getContainer()->get("pdf");

        $dati = array();
        $dati = array_merge_recursive($dati, $datiAggiuntivi);

        $dati["pagamento"] = $pagamento;
        $dati["procedura"] = $richiesta->getProcedura();
        $dati["richiesta"] = $richiesta;
        $dati["capofila"] = $this->getCapofila($pagamento);
        $isFsc = $this->getContainer()->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;

        $opzioni = array();

        //TODO mettere gestione fac simile
        $dati['facsimile'] = $facsimile;
        //return $this->render($twig, $dati);
        $pdf->load($twig, $dati);

        if ($download) {
            $pdf->download($this->getNomePdfPagamento($pagamento));
            return new Response();
        } else {
            return $pdf->binaryData();
        }
    }

    protected function generaPdfSaldo($pagamento, $facsimile = true, $download = true, $opzioni = array()) {

        $dati = array();
        $dati['rendicontazioneProceduraConfig'] = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $dati = array_merge($dati, $opzioni['dati_twig']);


        $gestoreRichieste = $this->getContainer()->get("gestore_richieste")->getGestore($pagamento->getRichiesta()->getProcedura());

        $twig = "@AttuazioneControllo/Pdf/Bando140/pdf_pagamento_standard_new.html.twig";

        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        return $this->generaPdfPagamento($pagamento, $twig, $dati, $facsimile, $download);
    }

    protected function generaPdfSal($pagamento, $facsimile = true, $download = true, $opzioni = array()) {

        $dati = array();

        $dati = array_merge($dati, $opzioni['dati_twig']);

        $gestoreRichieste = $this->getContainer()->get("gestore_richieste")->getGestore($pagamento->getRichiesta()->getProcedura());

        $twig = "@AttuazioneControllo/Pdf/Bando140/pdf_pagamento_standard_new.html.twig";

        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        return $this->generaPdfPagamento($pagamento, $twig, $dati, $facsimile, $download);
    }

    public function generaPdf($id_pagamento, $facsimile = true, $download = true) {
        ini_set('memory_limit', '2G');
        set_time_limit(0);
        $em = $this->em;

        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $modalitaPagamento = $pagamento->getModalitaPagamento();
        $opzioni = array();

        /**
         * è stato chiesto di leggere, anche in caso di variazione, sempre i due valori dalla concessione
         */
        $istruttoriaRichiesta = $pagamento->getRichiesta()->getIstruttoria();
        $costoTotaleAmmesso = $istruttoriaRichiesta->getCostoAmmesso();
        $contributoTotaleAmmesso = $istruttoriaRichiesta->getContributoAmmesso();

        if ($pagamento->getModalitaPagamento()->isAnticipo()) {
            $elenchiProcedura = array();
        } else {
            $elenchiProcedura = $em->getRepository('AttuazioneControlloBundle\Entity\Autodichiarazioni\ElencoProcedura')->getElenchiProceduraByPagamento($pagamento);
        }
        $sezioneAllega = $em->getRepository('AttuazioneControlloBundle\Entity\Sezioni\SezioneAllega')->getSezioneAllegaByPagamento($pagamento);

        $datiSede = new \stdClass();
        $datiSede->comune = null;
        $datiSede->provincia = null;
        $datiSede->via = null;
        $datiSede->numero = null;

        /**
         * concordato: 
         * se esiste la sede intervento(Intervento) stampo i dati di quella, altrimenti stampo la sede operativa (SedeOperativa),
         * altrimenti stampo i dati della sede legale del mandatario
         * va valutato anche il flag $mandatario->getSedeLegaleComeOperativa()..se true vuol dire che la sede operativa è la sede legale  
         */
        $mandatario = $pagamento->getRichiesta()->getMandatario();

        $sediIntervento = $mandatario->getInterventi();
        $sediOperative = $mandatario->getSedi();

        if (count($sediIntervento) > 0) {
            $sedeIntervento = $sediIntervento->first();
            $indirizzo = $sedeIntervento->getIndirizzo();
            $comune = $indirizzo->getComune();

            $datiSede->comune = $comune ? $comune->getDenominazione() : $indirizzo->getComuneEstero();
            $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $indirizzo->getProvinciaEstera();
            $datiSede->via = $indirizzo->getVia();
            $datiSede->numero = $indirizzo->getNumeroCivico();
        } elseif (!$mandatario->getSedeLegaleComeOperativa() && count($sediOperative) > 0) {
            $sedeOperativa = $sediOperative->first();
            $indirizzo = $sedeOperativa->getSede()->getIndirizzo();
            $comune = $indirizzo->getComune();

            $datiSede->comune = $comune ? $comune->getDenominazione() : $indirizzo->getComuneEstero();
            $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $indirizzo->getProvinciaEstera();
            $datiSede->via = $indirizzo->getVia();
            $datiSede->numero = $indirizzo->getNumeroCivico();
        }
        /**
         * Si aggiunge ora anche la sede del bando 5 in quanto gestita come sede in gestione progetti
         */ elseif ($pagamento->getProcedura()->getId() == 5) {
            $oggetti_richiesta = $pagamento->getRichiesta()->getOggettiRichiesta();
            $oggetto = $oggetti_richiesta->first();
            $elencoEdifici = $oggetto->getIndirizziCatastali();
            $intervento = $elencoEdifici->first();
            if ($intervento != false) {
                $comune = $intervento->getComune();
                $datiSede->comune = $comune ? $comune->getDenominazione() : '-';
                $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : '-';
                $datiSede->via = $intervento->getVia();
                $datiSede->numero = $intervento->getNumeroCivico();
            } else {
                $soggetto = $mandatario->getSoggetto();
                $comune = $soggetto->getComune();

                $datiSede->comune = $comune ? $comune->getDenominazione() : $soggetto->getComuneEstero();
                $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $soggetto->getProvinciaEstera();
                $datiSede->via = $soggetto->getVia();
                $datiSede->numero = $soggetto->getCivico();
            }
        } else {
            $soggetto = $mandatario->getSoggetto();
            $comune = $soggetto->getComune();

            $datiSede->comune = $comune ? $comune->getDenominazione() : $soggetto->getComuneEstero();
            $datiSede->provincia = $comune ? $comune->getProvincia()->getDenominazione() : $soggetto->getProvinciaEstera();
            $datiSede->via = $soggetto->getVia();
            $datiSede->numero = $soggetto->getCivico();
        }

        $contratti = $pagamento->getContratti();
        $contrattiArray = array(); //
        foreach ($contratti as $contratto) {
            $contrattiArray[$contratto->getId()]['giustificativi'] = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->getGiustificativiByContrattoPerPdfDomandaPagamento($contratto->getId(), $id_pagamento);
        }


        /**
         * dati comuni alle modalita pagamento passati al twig pdf
         * eventuali dati specifici di una modalita pagamento vanno aggiunti nella sotto procedura relativa
         */
        $opzioni['dati_twig'] = array(
            'costoTotaleAmmesso' => $costoTotaleAmmesso,
            'contributoTotaleAmmesso' => $contributoTotaleAmmesso,
            'elenchiProcedura' => $elenchiProcedura,
            'rendicontazioneProceduraConfig' => $this->getRendicontazioneProceduraConfig($pagamento->getProcedura()),
            'sezioneAllega' => count($sezioneAllega) > 0 ? $sezioneAllega[0] : null,
            'datiSede' => $datiSede,
            'contratti' => $contrattiArray,
            'giustificativi_precedente' => $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->getGiustificativiPrecedentePerPdfDomandaPagamento($id_pagamento),
        );


        if ($modalitaPagamento->isAnticipo()) {
            return $this->generaPdfAnticipo($pagamento, $facsimile, $download, $opzioni);
            //accomuniamo tutti i pafgamenti intermedi sal sal1 sal2 etc..
            // se dovesse servire diversificare (ma non penso) vanno gestite le casistiche specifiche
        } elseif ($modalitaPagamento->isPagamentoIntermedio()) {
            return $this->generaPdfSal($pagamento, $facsimile, $download, $opzioni);
        } elseif ($modalitaPagamento->isSaldo()) {
            return $this->generaPdfSaldo($pagamento, $facsimile, $download, $opzioni);
        } elseif ($modalitaPagamento->isUnicaSoluzione()) {
            return $this->generaPdfUnicaSoluzione($pagamento, $facsimile, $download, $opzioni);
        }

        throw new \Exception('modalità pagamento non gestita');
    }

    protected function generaPdfUnicaSoluzione($pagamento, $facsimile = true, $download = true, $opzioni = array()) {

        $dati = array();
        $dati['rendicontazioneProceduraConfig'] = $this->getRendicontazioneProceduraConfig($pagamento->getRichiesta()->getProcedura());

        $dati = array_merge($dati, $opzioni['dati_twig']);

        $gestoreRichieste = $this->getContainer()->get("gestore_richieste")->getGestore($pagamento->getRichiesta()->getProcedura());

        $twig = "@AttuazioneControllo/Pdf/Bando140/pdf_pagamento_standard_new.html.twig";

        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        foreach ($opzioni as $key => $value) {
            $dati[$key] = $value;
        }

        return $this->generaPdfPagamento($pagamento, $twig, $dati, $facsimile, $download);
    }

    public function getRendicontazioneProceduraConfig($procedura) {

        $rendicontazioneProceduraConfig = $procedura->getRendicontazioneProceduraConfig();
        // fallback..default
        if (is_null($rendicontazioneProceduraConfig)) {
            $rendicontazioneProceduraConfig = new \AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig();
        }

        return $rendicontazioneProceduraConfig;
    }

    public function getCapofila($pagamento) {
        return $pagamento->getSoggetto();
    }

    protected function getNomePdfPagamento($pagamento) {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Richiesta di pagamento " . $pagamento->getId() . " " . $data;
    }

}
