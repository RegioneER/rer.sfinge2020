<?php

namespace RichiesteBundle\Service;

use BaseBundle\Service\BaseService;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Service\DocumentiService;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiestaRepository;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\RichiestaCupBatch;
use RichiesteBundle\Service\Cipe\RichiestaCipeService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author gaetanoborgosano
 */
class GestoreRichiestaCupBatch extends BaseService {
    /**
     * @var RichiestaCipeService
     */
    protected $RichiestaCipeService;

    /**
     * @var DocumentiService
     */
    protected $DocumentiService;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->RichiestaCipeService = $container->get('richiesta_cipe_service');
        $this->DocumentiService = $container->get('documenti');
    }

    protected function return_render($view, $parameters): Response {
        return $this->render($view, $parameters);
    }

    public function saveRichiestaCupBatch(RichiestaCupBatch $RichiestaCupBatch, $flush = true) {
        $em = $this->getEm();
        $em->persist($RichiestaCupBatch);
        if ($flush) {
            $em->flush();
        }
        return $RichiestaCupBatch;
    }

    /**
     * @param bool $render
     * @return array
     * @throws \Exception
     */
    public function findAll($render = true) {
        $em = $this->getEm();
        $richiestaCupBatches = $em->getRepository(RichiestaCupBatch::class)->findAll();

        if (!$render) {
            return $richiestaCupBatches;
        }

        $parameters = ['richiestaCupBatches' => $richiestaCupBatches];
        $view = "RichiesteBundle:richiestecupbatch:index.html.twig";
        return $this->return_render($view, $parameters);
    }

    public function show(RichiestaCupBatch $richiestaCupBatch, $render = true) {
        if (!$render) {
            return $richiestaCupBatch;
        }

        $parameters = ['richiestaCupBatch' => $richiestaCupBatch];
        $view = "RichiesteBundle:richiestecupbatch:show.html.twig";
        return $this->return_render($view, $parameters);
    }

    public function associaCupARichiesta(RichiestaCupBatch $richiestaCupBatch, $id_progetto, $codifica_locale, $codice_cup, $codice_cup_null = true) {
        /** @var IstruttoriaRichiestaRepository $repoIstruttoria */
        $repoIstruttoria = $this->getEm()->getRepository(IstruttoriaRichiesta::class);
        /* @var $IstruttoriaRichiesta IstruttoriaRichiesta */
        $IstruttoriaRichiesta = $repoIstruttoria->findIstruttoriaByEsitoCupBatchArray($id_progetto, $codifica_locale, $codice_cup_null);
        if (\is_null($IstruttoriaRichiesta)) {
            return null;
        }

        if (!\is_null($IstruttoriaRichiesta->getCodiceCup())) {
            return false; // codice cup già presente
        }

        $IstruttoriaRichiesta->setCodiceCup($codice_cup);
        $IstruttoriaRichiesta->setRichiestaCupBatchRisposta($richiestaCupBatch);
        $this->getEm()->persist($IstruttoriaRichiesta);
        //			$this->getEm()->flush();
        //			$this->getEm()->detach($IstruttoriaRichiesta);
        return true;
    }

    public function associaRispostaRichiestaCupBatch(RichiestaCupBatch $richiestaCupBatch, DocumentoFile $cupBatchDocumentoRisposta) {
        //			$richiestaCupBatch->setCupBatchDocumentoRichiesta($cupBatchDocumentoRisposta);
        //			$this->getEm()->persist($richiestaCupBatch);
        $tipologia_documento = $this->getEm()->getRepository(\DocumentoBundle\Entity\TipologiaDocumento::class)->findOneBy(["codice" => \CipeBundle\Services\CupBatchService::CODICE_TIPOLOGIA_DOCUMENTO_DEFAULT]);
        $cupBatchDocumentoRisposta->setTipologiaDocumento($tipologia_documento);

        $cupBatchDocumentoRisposta = $this->DocumentiService->carica($cupBatchDocumentoRisposta, false);
        $nome = $cupBatchDocumentoRisposta->getNome();
        $path = $cupBatchDocumentoRisposta->getPath();
        $xml_filename = $path . $nome;

        $array_esiti = $this->RichiestaCipeService->elaboraFileRichiestaCupBatch($xml_filename);

        foreach ($array_esiti as $k => $esito) {
            /*
            * $esito = array(
                            "id_progetto"		=> $id_progetto,
                            "codifica_locale"	=> $codifica_locale,
                            "codice_cup"			=> $codice_cup,
                            "messaggi_scarto"	=> $messaggi_scarto (nullo che codice_cup è valorizzato)
                            );
            */
            $codice_cup = $esito['codice_cup'];
            $id_progetto = $esito['id_progetto'];
            $codifica_locale = $esito['codifica_locale'];
            $risultato = "SCARTO";
            if (!\is_null($codice_cup)) {
                $st = $this->associaCupARichiesta($richiestaCupBatch, $id_progetto, $codifica_locale, $codice_cup);
            }
            $esito["corrispondenza"] = $st;
            $array_esiti[$k] = $esito;
        }

        if ($richiestaCupBatch->getSalvaEsiti()) {
            $richiestaCupBatch->setEsiti($array_esiti);
        }

        $this->getEm()->persist($cupBatchDocumentoRisposta);
        $richiestaCupBatch->setCupBatchDocumentoRisposta($cupBatchDocumentoRisposta);
        $dataRisposta = new \DateTime('NOW');
        $richiestaCupBatch->setDataRisposta($dataRisposta);
        $this->getEm()->persist($richiestaCupBatch);
        $this->getEm()->flush();

        return $this->show($richiestaCupBatch);
    }

    public function associaScartiCupBatch(RichiestaCupBatch $richiestaCupBatch, DocumentoFile $DocumentiScartiCupBatch) {
        $tipologia_documento = $this->getEm()->getRepository(\get_class(new \DocumentoBundle\Entity\TipologiaDocumento()))->findOneBy(["codice" => \CipeBundle\Services\CupBatchService::CODICE_TIPOLOGIA_DOCUMENTO_DEFAULT]);
        $DocumentiScartiCupBatch->setTipologiaDocumento($tipologia_documento);

        $cupBatchDocumentoScarto = $this->DocumentiService->carica($DocumentiScartiCupBatch, false);
        $nome = $cupBatchDocumentoScarto->getNome();
        $path = $cupBatchDocumentoScarto->getPath();
        $xml_filename = $path . $nome;

        $array_scarti = $this->RichiestaCipeService->elaboraFileRichiestaCupBatch($xml_filename);

        foreach ($array_scarti as $k => $scarto) {
            //				"id_progetto"		=> trim($id_progetto),
            //									"codifica_locale"	=> trim($codifica_locale),
            //									"codice_cup"		=> trim($codice_cup),
            //									"messaggi_scarto"	=> $messaggi_scarto
            $codice_cup = (\array_key_exists("codice_cup", $scarto) && strlen(trim($scarto['codice_cup']) > 0)) ? $scarto['codice_cup'] : null;
            if (\is_null($codice_cup) && \array_key_exists("messaggi_scarto", $scarto) && count($scarto["messaggi_scarto"]) > 0) {
                $id_progetto = $scarto['id_progetto'];
                $codifica_locale = $scarto['codifica_locale'];
                $st = $this->associaScartoARichiesta($richiestaCupBatch, $id_progetto, $codifica_locale);
                // setto se si trova corrispondenza con i record presenti a sistema.
                $array_scarti[$k]['corrispondenza'] = $st;
            }
        }

        if ($richiestaCupBatch->getSalvaScarti()) {
            $richiestaCupBatch->setScarti($array_scarti);
        }

        $this->getEm()->persist($cupBatchDocumentoScarto);
        $richiestaCupBatch->setCupBatchDocumentoScarto($cupBatchDocumentoScarto);
        $dataScarto = new \DateTime('NOW');
        $richiestaCupBatch->setDataScarto($dataScarto);
        $this->getEm()->persist($richiestaCupBatch);
        $this->getEm()->flush();

        return $this->show($richiestaCupBatch);
    }

    /**
     * associa richiestaCupBatch con scarto a Richiesta - Istruttoria
     * @param int $id_progetto
     * @param string  $codifica_locale
     * @return bool
     * @throws \Exception
     */
    public function associaScartoARichiesta(RichiestaCupBatch $richiestaCupBatch, $id_progetto, $codifica_locale) {
        /** @var IstruttoriaRichiestaRepository $repoIstruttoria */
        $repoIstruttoria = $this->getEm()->getRepository(IstruttoriaRichiesta::class);
        /** @var IstruttoriaRichiesta $IstruttoriaRichiesta */
        $IstruttoriaRichiesta = $repoIstruttoria->findIstruttoriaByEsitoCupBatchArray($id_progetto, $codifica_locale, true);
        if (\is_null($IstruttoriaRichiesta)) {
            return false;
        }
        if (\is_null($IstruttoriaRichiesta->getUltimaRichiestaCupBatch())) {
            return false;
        }
        $IstruttoriaRichiesta->setUltimaRichiestaCupBatchScarto($richiestaCupBatch);
        $this->getEm()->persist($IstruttoriaRichiesta);

        return true;
    }
}
