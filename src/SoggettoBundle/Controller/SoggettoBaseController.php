<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 04/01/16
 * Time: 17:25
 */

namespace SoggettoBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Entity\Indirizzo;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use SoggettoBundle\Entity\Sede;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use function is_null;

/**
 * Class SoggettoBaseController
 *
 * @package SoggettoBundle\Controller
 */
class SoggettoBaseController extends BaseController {

    protected function isUtentePrincipale() {
        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        if (is_null($soggettoSession)) {
            return false;
        }
        $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
        if (is_null($soggetto)) {
            return false;
        }

        $persona = $this->getPersona();
        if (is_null($persona)) {
            return false;
        }
        $tipoIncarico = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::UTENTE_PRINCIPALE);

        return $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->haIncaricoPersonaAttivo($soggetto, $persona, $tipoIncarico);
    }

    /**
     * @param $codiceAtecoAdrier
     *
     * @return mixed|null
     * @throws NonUniqueResultException
     */
    public function getCodiceAtecoDaAdrier($codiceAtecoAdrier) {
        if (empty($codiceAtecoAdrier)) {
            return null;
        } else {
            return $this->getDoctrine()->getRepository('SoggettoBundle\Entity\Ateco')->ricercaByCodiceAndDescrizione($codiceAtecoAdrier[0], $codiceAtecoAdrier[1]);
        }
    }

    /**
     * @param $formaGiuridicaAdrier
     *
     * @return mixed
     */
    public function getFormaGiuridicaDaAdrier($formaGiuridicaAdrier) {
        return $this->getDoctrine()->getRepository('SoggettoBundle:FormaGiuridica')->ricercaDaDescrizioneAdrier($formaGiuridicaAdrier);
    }

    /**
     * @param $cciaa
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getRegistroCciaaDiDaAdrier($cciaa) {
        return $this->getDoctrine()->getRepository('GeoBundle:GeoProvincia')->ricercaDaSiglaProvinciaAdrier($cciaa);
    }

    /**
     * @param $istat
     *
     * @return object|null
     * @throws NonUniqueResultException
     */
    public function getComuneSedeDaAdrier($istat) {
        $comune = $this->getDoctrine()->getRepository('GeoBundle:GeoComune')->ricercaByCodiceParziale($istat);
        //Purtroppo adrier non tiene aggiornata la lista dei comuni e ci mettono mesi a rispondere ad una segnalazione e
        //siamo costretti a mettere dei valori di default (BOLOGNA)
        if (is_null($comune)) {
            return $this->getDoctrine()->getRepository('GeoBundle:GeoComune')->ricercaByCodiceParziale('037006');
        }
        return $comune;
    }

    /**
     * @param $istat
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getStatoSedeDaAdrier($istat) {
        $stato = $this->getDoctrine()->getRepository('GeoBundle:GeoStato')->ricercaByCodiceParziale($istat);
        //Purtroppo adrier non tiene aggiornata la lista dei comuni e ci mettono mesi a rispondere ad una segnalazione e
        //siamo costretti a mettere dei valori di default (ITALIA)
        if (is_null($stato)) {
            return $this->getDoctrine()->getRepository('GeoBundle:GeoStato')->ricercaByCodiceParziale('11101');
        }
        return $stato;
    }

    /**
     * @param $istat
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getStatoLrDaAdrier($istat, $codiceFiscale) {
        if (empty($istat)) {
            return $this->get('inserimento_persona')->dammiNazioneNascita($codiceFiscale)[3];
        }
        return $this->getStatoSedeDaAdrier($istat);
    }

    /**
     * @param $istat
     *
     * @return object|null
     * @throws NonUniqueResultException
     */
    public function getComuneLrDaAdrier($istat, $codiceFiscale) {
        if (empty($istat)) {
            return $this->get('inserimento_persona')->dammiNazioneNascita($codiceFiscale)[0];
        }
        return $this->getComuneSedeDaAdrier($istat);
    }

    /**
     * @param Soggetto      $soggetto
     * @param ObjectManager $em
     */
    protected function aggiungiSedeLegaleAlleSedi(Soggetto $soggetto, ObjectManager $em) {
        $nuovaSede = new Sede();
        $indirizzoSede = new Indirizzo();

        $nuovaSede->setSoggetto($soggetto->getSoggetto());
        $nuovaSede->setDenominazione($soggetto->getDenominazione());
        $nuovaSede->setAteco($soggetto->getCodiceAteco());
        $nuovaSede->setAtecoSecondario($soggetto->getCodiceAtecoSecondario());

        $indirizzoSede->setStato($soggetto->getStato());
        $indirizzoSede->setComune($soggetto->getComune());
        $indirizzoSede->setVia($soggetto->getVia());
        $indirizzoSede->setNumeroCivico($soggetto->getCivico());
        $indirizzoSede->setCap($soggetto->getCap());
        $indirizzoSede->setLocalita($soggetto->getLocalita());
        $indirizzoSede->setProvinciaEstera($soggetto->getProvinciaEstera());
        $indirizzoSede->setComuneEstero($soggetto->getComuneEstero());

        $em->persist($indirizzoSede);

        $nuovaSede->setIndirizzo($indirizzoSede);

        $em->persist($nuovaSede);

        $soggetto->addSedi($nuovaSede);

        $em->persist($soggetto);
    }

    /**
     * @Route("/cancella_soggetto/{soggetto_id}", name="cancella_soggetto")
     * @param mixed $soggetto_id
     */
    public function cancellaSoggettoAction($soggetto_id) {
        if (!$this->isSuperAdmin()) {
            return $this->addErrorRedirect("Non hai i privilegi per effettuare questa operazione", "elenco_soggetti_giuridici");
        }
        $soggetto = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->find($soggetto_id);
        if (count($soggetto->getProponenti()) > 0) {
            return $this->addErrorRedirect("Il soggetto è un proponente attivo", "elenco_soggetti_giuridici");
        }
        $this->get('base')->checkCsrf('token');
        try {
            $this->getEm()->remove($soggetto);
            $this->getEm()->flush();
            $this->addFlash("success", "Operazione eseguita");
        } catch (\Exception $e) {
            $this->getEm()->rollback();
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute("elenco_soggetti_giuridici");
    }

     /**
     * @Route("/estrazioni/estrazione_universo_soggetti", name="estrazione_universo_soggetti")
     */
    public function estrazioneUniversoSoggetti() {
        if (!$this->isSuperAdmin()) {
            return $this->addErrorRedirect("Non hai i privilegi per effettuare questa operazione", "home");
        }

        ini_set('memory_limit', '2G');
        set_time_limit(0);

        $soggetti = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->findAll();

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");

        $riga = 1;

        $phpExcelObject->setActiveSheetIndex(0);
        $activeSheet = $phpExcelObject->getActiveSheet();

        $column = 0;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Id');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Data creazione');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Ragione sociale');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Partita iva');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice fiscale');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Tipo');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Forma giuridica');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Provincia Sede Legale');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Mail Azienda/Soggetto Giuridico');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'PEC Azienda/Soggetto Giuridico');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice Ateco');
        $column++;
        $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, 'Codice Ateco Secondario');

        $column = 0;
        foreach ($soggetti as $soggetto) {
            $riga++;
            $phpExcelObject->setActiveSheetIndex(0);
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $soggetto->getId());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $soggetto->getDataCreazione()->format('d-m-Y H:i:s'));
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $soggetto->getDenominazione());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getPartitaIva()) ? '-' : $soggetto->getPartitaIva());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $soggetto->getCodiceFiscale());
            $column++;
            if($soggetto->getTipo() == 'PERSONA_FISICA') {
                $tipo = 'PERSONA FISICA';
            }else {
                $tipo = $soggetto->getTipo();
            }
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $tipo);
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getFormaGiuridica()) ? '-' : $soggetto->getFormaGiuridica()->getDescrizione());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, $soggetto->dammiProvincia());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getEmail()) ? '-' : $soggetto->getEmail());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getEmailPec()) ? '-' : $soggetto->getEmailPec());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getCodiceAteco()) ? '-' : $soggetto->getCodiceAteco());
            $column++;
            $activeSheet->setCellValueExplicitByColumnAndRow($column, $riga, is_null($soggetto->getCodiceAtecoSecondario()) ? '-' : $soggetto->getCodiceAtecoSecondario());

            $column = 0;
        }

        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Estrazione_soggetti.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

}
