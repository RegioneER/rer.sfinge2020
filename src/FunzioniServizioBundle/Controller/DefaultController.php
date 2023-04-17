<?php

namespace FunzioniServizioBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class DefaultController
 *
 */
class DefaultController extends Controller
{
    const DATA_FILTRO = '2016-01-01';

    /**
     * @Route("/", name="funzioni_servizio_index")
     *
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     */
    public function indexAction()
    {
        return $this->render('FunzioniServizioBundle:Default:index.html.twig', $this->getBandos());
    }

    /**
     * @Route("/bando/{idBando}/{piattaforma}/elenco_progetti", name="funzioni_servizio_bando")
     *
     * @param string $idBando
     * @param string $piattaforma
     *
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bandoDetailAction(string $idBando, string $piattaforma)
    {
        return $this->render('FunzioniServizioBundle:Default:bando.html.twig', $this->getProgettos($idBando, $piattaforma));
    }

    /**
     * @Route("/bando/{idBando}/progetto/{idProgetto}/{piattaforma}/dettaglio_progetto", name="funzioni_servizio_progetto")
     *
     * @param string $idBando
     * @param string $idProgetto
     * @param string $piattaforma
     *
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function progettoDetailAction(string $idBando, string $idProgetto, string $piattaforma)
    {
        return $this->render('FunzioniServizioBundle:Default:progetto.html.twig', $this->getProgetto($idBando, $idProgetto, $piattaforma));
    }

    /**
     * @Route("/bando/{idBando}/progetto/{idProgetto}/{piattaforma}/{pagamento}/dettaglio_pagamento", name="funzioni_servizio_pagamento")
     *
     * @param string $idBando
     * @param string $idProgetto
     * @param string $piattaforma
     *
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pagamentoDetailAction(string $idBando, string $idProgetto, string $piattaforma, string $pagamento)
    {
        $progetto = $this->getProgetto($idBando, $idProgetto, $piattaforma);

        $pagamento = urldecode($pagamento);

        foreach ($progetto['progetto']['elenco_pagamenti'] as $key => $value) {
            if ($value['protocollo_domanda_pagamento'] != $pagamento) {
                unset($progetto['progetto']['elenco_pagamenti'][$key]);
            }
        }

        return $this->render('FunzioniServizioBundle:Default:pagamento.html.twig', $progetto);
    }

    /**
     * @param string $id
     * @param string $piattaforma
     *
     * @return bool|mixed
     */
    public function getBando(string $id, string $piattaforma)
    {
        $bandos = $this->getBandos();

        foreach ($bandos['bandos'] as $key => $bando) {
            if ($bando['id_bando'] == $id && $bando['piattaforma'] == $piattaforma) {
                return $bando;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getBandos()
    {
        $arrayBandos = json_decode(<<<JSON
{
    "bandos":[
        {
            "id_bando":"235",
            "piattaforma": "sfinge2020",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 27/2014 - Bando DGR n. 16 del 2013",
            "numero_delibera": "16/2013",
            "data_delibera": 1358121600
        },
        {
            "id_bando":"7",
            "piattaforma": "sfinge2020",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 49/2015 - Bando n. 773 del 2015",
            "numero_delibera": "773/2015",
            "data_delibera": 1434924000
        },
        {
            "id_bando":"7",
            "piattaforma": "terremoto",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 6 del 10 luglio 2014, Alluvione/Trombe d'aria",
            "numero_delibera": "6/2014",
            "data_delibera": 1404950400
        },
        {
            "id_bando":"4",
            "piattaforma": "terremoto",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 109/2013, Ricerca - Tipologia 1 - Progetti di ricerca e sviluppo delle PMI",
            "numero_delibera": "128",
            "data_delibera": 1381960800
        },
        {
            "id_bando":"5",
            "piattaforma": "terremoto",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 109/2013, Ricerca - Tipologia 2 Progetti di ricerca e sviluppo con impatto di filiera o previsioni di crescita occupazionale",
            "numero_delibera": "128",
            "data_delibera": 1381960800
        },
        {
            "id_bando":"3",
            "piattaforma": "terremoto",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 109/2013, Ricerca - Tipologia 3 - Acquisizione di servizi di ricerca e sperimentazione",
            "numero_delibera": "128",
            "data_delibera": 1381960800
        },
        {
            "id_bando":"216",
            "piattaforma": "terremoto",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 91 del 29 luglio 2013 (contiene anche Ord. 52/2013 e Ord 23/2013) - INAIL",
            "numero_delibera": "91",
            "data_delibera": 1361487600
        },
        {
            "id_bando":"12",
            "piattaforma": "terremoto",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanza n. 26/2016 del 22 Aprile 2016 - INAIL",
            "numero_delibera": "26",
            "data_delibera": 1461276000
        },
        {
            "id_bando":"14",
            "piattaforma": "terremoto",
            "data_termine_accesso" : "2021-12-31",
            "titolo":"Ordinanze nn. 13/2017 del 15 Maggio 2017 e 21/2017 del 16 ottobre 2017",
            "numero_delibera": "13",
            "data_delibera": 1494852125
        }
    ]
}
JSON
            , true);
        
        return $arrayBandos;
    }

    /**
     * @param string $idBando
     * @param string $piattaforma
     *
     * @return array
     */
    private function getProgettos(string $idBando, string $piattaforma)
    {
        switch ($piattaforma) {
            case "sfinge2020":
                switch ($idBando) {
                    case 7:
                        $json = $this->forward('FunzioniServizioBundle:EsportazioneBando773:esportazioneBando', ['idBando' => $idBando, 'dataFiltro' => self::DATA_FILTRO,'token' => $this->container->getParameter('token_sisma')])->getContent();
                        break;
                    case 235:
                        $json = $this->forward('FunzioniServizioBundle:EsportazioneBando27:esportazioneBando', ['idBando' => $idBando, 'dataFiltro' => self::DATA_FILTRO,'token' => $this->container->getParameter('token_sisma')])->getContent();
                        break;
                }
                break;
            case "terremoto":
                $json = file_get_contents('https://sfingesisma.regione.emilia-romagna.it/sfinge_si/aziende/presentazione/liquidazioni_2017.php?token=' . $this->container->getParameter('token_sisma') . '&idBando=' . $idBando . '&dataFiltro=' . self::DATA_FILTRO);
                break;
        }

        $bando = $this->getBando($idBando, $piattaforma);

        $dettaglibando = json_decode($json, true);

        if(!isset($dettaglibando['bando'])) {
            $dettaglibando = ["bando" => $dettaglibando];
        }

        $dettaglibando['bando']['id_bando']             = $bando['id_bando'];
        $dettaglibando['bando']['piattaforma']          = $bando['piattaforma'];
        $dettaglibando['bando']['data_termine_accesso'] = $bando['data_termine_accesso'];

        return $dettaglibando;
    }

    /**
     * @param string $idBando
     * @param string $idProgetto
     * @param string $piattaforma
     *
     * @return array
     */
    private function getProgetto(string $idBando, string $idProgetto, string $piattaforma)
    {
        switch ($piattaforma) {
            case "sfinge2020":
                switch ($idBando) {
                    case 7:
                        $json = $this->forward('FunzioniServizioBundle:EsportazioneBando773:esportazioneProgetto', ['idProgetto' => $idProgetto, 'token' => $this->container->getParameter('token_sisma')])->getContent();
                        break;
                    case 235:
                        $json = $this->forward('FunzioniServizioBundle:EsportazioneBando27:esportazioneProgetto', ['idProgetto' => $idProgetto, 'token' => $this->container->getParameter('token_sisma')])->getContent();
                        break;
                }
                break;
            case "terremoto":
                $json = file_get_contents('https://sfingesisma.regione.emilia-romagna.it/sfinge_si/aziende/presentazione/liquidazioni_2017.php?token=' . $this->container->getParameter('token_sisma') . '&idProgetto=' . $idProgetto);
                break;
        }

        $bando = $this->getBando($idBando, $piattaforma);

        $progetto = json_decode($json, true);

        if(!isset($progetto['progetto'])) {
            $progetto = ["progetto" => $progetto];
        }

        $progetto['progetto']['id_bando']             = $idBando;
        $progetto['progetto']['titolo_bando']         = $bando['titolo'];
        $progetto['progetto']['piattaforma']          = $bando['piattaforma'];
        $progetto['progetto']['data_termine_accesso'] = $bando['data_termine_accesso'];

        return $progetto;
    }
}
