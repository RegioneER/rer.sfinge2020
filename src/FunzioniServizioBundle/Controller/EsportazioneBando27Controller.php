<?php

namespace FunzioniServizioBundle\Controller;

use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\Pagamento;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class EsportazioneBando27Controller extends Controller
{
    const ORDINANZA_27 = 235;
    
    /**
     * @Route("/esportazione_bando_27/{idBando}/{dataFiltro}/{token}", name="funzioni_servizio_esportazione_bando_27", requirements={"idBando"="\d+", "dataFiltro"="[0-9]{4}-[0-9]{2}-[0-9]{2}", "token"="w4ggZm5jdnJhZ3IgZmJ5YiBwdXYgZm4gcXYgYWJhIGZuY3JlciwgYWJhIHB1diBmJ3Z5eWhxciBxdiBmbmNyZXIgciB2dGFiZW4gcGJmw6wgY3Jlc3ZhYiB5biBmaG4gZmdyZmZuIHZ0YWJlbmFtbi4="})
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     * 
     * @param $idBando
     * @param $dataFiltro
     * @return JsonResponse
     * @throws Exception
     */
    public function esportazioneBandoAction($idBando, $dataFiltro)
    {
        $progetti = [];
        $dataFiltro = explode('-', $dataFiltro);
        if ($idBando == self::ORDINANZA_27) {
            $progetti = $this->getJsonBando(new DateTime($dataFiltro[0] . '-' . $dataFiltro[1] . '-' . $dataFiltro[2] . ' 00:00:00')); 
        }
        
        return new JsonResponse($progetti);
    }

    /**
     * @param $idBando
     * @param DateTime $dataFiltro
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryBando($idBando, DateTime $dataFiltro)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
                        SELECT progetti._kp_progetto, progetti.numeroprotocollo, progetti.dataprotocollo,
                               aziende.ragionesociale, aziende.codicefiscale, aziende.partitaiva
                        FROM myfesr.atc_progetti AS progetti
                        JOIN myfesr.atc_aziende AS aziende ON (progetti._kp_progetto = aziende._kf_progetto)
                        JOIN myfesr.atc_pagamenti AS pagamenti ON (progetti._kp_progetto = pagamenti._kf_progetto)
                        WHERE progetti._kf_bando = :kp_bando AND pagamenti.mandatopagamento_data >= :dataLimiteMin
                        GROUP BY progetti._kp_progetto");
        $statement->bindValue('kp_bando', $idBando);
        $statement->bindValue('dataLimiteMin', $dataFiltro->format('Y-m-d H:i:s'));
       
        $statement->execute();
        $progetti = $statement->fetchAll();
        return $progetti;
    }

    /**
     * @param DateTime $dataFiltro
     * @return array
     * @throws Exception
     */
    public function getJsonBando(DateTime $dataFiltro)
    {
        $progetti = $this->getRepositoryBando(self::ORDINANZA_27, $dataFiltro);
        if (empty($progetti)) {
            return array();
        }
        
        $bando = [
            'bando' => [
                'titolo' => 'Ord. n. 27/2014 - Bando DGR n. 16 del 2013',
                'numero_delibera' => '16',
                'data_delibera' => 1358121600,
            ]
        ];

        $elencoProgetti = [];
        foreach ($progetti as $progetto) {
            $elencoProgetti[] = [
                'id_progetto' => $progetto['_kp_progetto'],
                'numero_protocollo' => $progetto['numeroprotocollo'],
                'data_protocollo' => $progetto['dataprotocollo'],
                'beneficiario' => $progetto['ragionesociale'],
                'codice_fiscale' => $progetto['codicefiscale'],
                'partita_iva' => $progetto['partitaiva'],
            ];
        }
        
        $bando['bando']['elenco_progetti'] = $elencoProgetti;
        return $bando;
    }

    /**
     * @Route("/esportazione_progetto_27/{idProgetto}/{token}", name="funzioni_servizio_esportazione_progetto_27", requirements={"idProgetto"="\d+", "token"="w4ggZm5jdnJhZ3IgZmJ5YiBwdXYgZm4gcXYgYWJhIGZuY3JlciwgYWJhIHB1diBmJ3Z5eWhxciBxdiBmbmNyZXIgciB2dGFiZW4gcGJmw6wgY3Jlc3ZhYiB5biBmaG4gZmdyZmZuIHZ0YWJlbmFtbi4="})
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     * 
     * @param $idProgetto
     * @return JsonResponse
     * @throws Exception
     */
    public function esportazioneProgettoAction($idProgetto)
    {
        $progetti = $this->getJsonProgettoBando27($idProgetto);
        return new JsonResponse($progetti);
    }

    /**
     * @param $idProgetto
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryProgetto($idProgetto)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
                SELECT prg._kf_bando, prg._kp_progetto, ist_prg._kp_progetto AS _kf_progetto_ist, ist_prg._kf_progetto AS _kf_progetto_pre, 
                   prg.numeroprotocollo, prg.dataprotocollo, COALESCE(azi.ragionesociale, 
                       concat(azi.nome_legale_rappresentante, ' ', azi.cognome_legale_rappresentante)) AS beneficiario, azi.codicefiscale, azi.partitaiva,
                       ist_prg.costoproposto AS costo_presentato, prg.costototale AS costo_ammesso, prg.contributorichiesto AS contributo_concesso,
                       '' AS numero_protocollo, concessione.numero AS nr_atto_concessione, concessione.dataatto AS data_atto_concessione, concessione.attourl AS concessione_url 
                FROM myfesr.atc_progetti prg 
                JOIN myfesr.ist_progetti ist_prg ON (ist_prg._kp_progetto = prg._kf_progetto)
                JOIN myfesr.atc_aziende azi ON (azi._kf_progetto = prg._kp_progetto)
                LEFT JOIN myfesr.aam_attiapprovazione AS concessione ON (prg._kf_attoapprovazione = concessione._kp_attoapprovazione) 
                WHERE prg._kp_progetto = :kp_progetto");
        $statement->bindValue('kp_progetto', $idProgetto);
        $statement->execute();
        $progetto = $statement->fetchAll();
        return $progetto;
    }

    /**
     * @param $idProgetto
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryDocumentiAmministrativi($idProgetto)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
                SELECT docs.tipo_documento, 'M' AS tipo_ricezione, docs.link AS url_documento, docs.data_ricevuto AS data_documento
                FROM myfesr.ist_progettidocs AS docs
                JOIN myfesr.ist_progetti AS prg_ist ON (docs._kf_progetto = prg_ist._kp_progetto)
                JOIN myfesr.atc_progetti AS prg_atc ON (prg_ist._kp_progetto = prg_atc._kf_progetto)
                WHERE prg_atc._kp_progetto = :kp_progetto");
        $statement->bindValue('kp_progetto', $idProgetto);
        $statement->execute();
        $docs = $statement->fetchAll();
        return $docs;
    }

    /**
     * @param $idProgetto
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryPagamenti($idProgetto)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
                SELECT pag._kp_pagamento, pag.numerofattura AS protocollo, pag.datafattura AS data_invio, com.urlfideiussione, com._kp_comunicazione,
                    UPPER(IF(cau.descrizione = 'UNICA', 'SALDO', cau.descrizione)) AS tipologia_comunicazione, geco.url_documento_firmato, geco.url_carta_identita_firmata 
                FROM myfesr_aziende.pag_comunicazioni AS com 
                JOIN myfesr.atc_pagamenti AS pag ON (com._kf_pagamento = pag._kp_pagamento)
                JOIN myfesr.tab_causalipagamenti AS cau ON (pag._kf_causalepagamenti = cau._kp_causalepagamenti)
                LEFT JOIN myfesr_aziende.geco_richieste_contributo AS geco ON (geco._kf_comunicazione = com._kp_comunicazione)
                WHERE com.stato = 3 AND pag._kf_progetto = :kp_progetto");
        $statement->bindValue('kp_progetto', $idProgetto);
        $statement->execute();
        $docs = $statement->fetchAll();
        return $docs;
    }

    /**
     * @param $idPagamento
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryGiustificativi($idPagamento)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
               SELECT f._kp_fattura, f.importorichiesto, f.fatturaurl, f.beneficiario
               FROM myfesr.atc_fatture AS f
               WHERE f.attivo='S' AND f._kf_pagamento = :kp_pagamento");
        $statement->bindValue('kp_pagamento', $idPagamento);
        $statement->execute();
        $giustificativi = $statement->fetchAll();
        return $giustificativi;
    }

    /**
     * @param $idGiustificativo
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryImputazioni($idGiustificativo)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
                SELECT CONCAT(COALESCE(aamp.codice, ''), ') ' , COALESCE(aamp.titolo, '')) AS voce_di_spesa, fp.importo, fp.importo_approvato
                FROM myfesr.atc_fatture a 
                LEFT JOIN myfesr.atc_fatture_preventivi as fp ON (a._kp_fattura=fp._kf_fattura) 
                LEFT JOIN myfesr.atc_preventivi as atcp ON (fp._kf_preventivo=atcp._kp_preventivo) 
                LEFT JOIN myfesr.aam_preventivi as aamp ON (atcp._kf_preventivo=aamp._kp_preventivo) 
                WHERE a.attivo='S' AND a._kp_fattura = :kp_fattura");
        $statement->bindValue('kp_fattura', $idGiustificativo);
        $statement->execute();
        $imputazioni = $statement->fetchAll();
        return $imputazioni;
    }

    /**
     * @param $idGiustificativo
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryQuietanze($idGiustificativo)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
                SELECT q.quietanzaurl, q.importo_quietanza
                FROM myfesr.atc_quietanzefatture AS q
                WHERE q._kf_fattura = :kp_fattura");
        $statement->bindValue('kp_fattura', $idGiustificativo);
        $statement->execute();
        $quietanze = $statement->fetchAll();
        return $quietanze;
    }

    /**
     * @param $idPagamento
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryDocumentiPagamento($idPagamento)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
               SELECT doc.url, doc.titolo_doc AS nome
               FROM myfesr_aziende.pag_comunicazionidocs doc 
               WHERE doc._kf_comunicazione = :kp_pagamento");
        $statement->bindValue('kp_pagamento', $idPagamento);
        $statement->execute();
        $docs = $statement->fetchAll();
        return $docs;
    }

    /**
     * @param $idPagamento
     * @return mixed
     * @throws Exception
     */
    public function getRepositoryDocumentiIstruttoriaPagamento($idPagamento)
    {
        $customerEm = $this->container->get('doctrine')->getManager('customer2');
        $connection = $customerEm->getConnection();
        $statement = $connection->prepare("
              SELECT doc.titolo as titolo, doc.link as url
              FROM myfesr.ord_progettidocs AS doc
              WHERE _kf_pagamento = :kp_pagamento");
        $statement->bindValue('kp_pagamento', $idPagamento);
        $statement->execute();
        $docs = $statement->fetchAll();
        return $docs;
    }

    /**
     * @param $idProgetto
     * @return array
     * @throws Exception
     */
    public function getJsonProgettoBando27($idProgetto)
    {
        $progetto = $this->getRepositoryProgetto($idProgetto);
        if (empty($progetto)) {
            return array();
        }
        $progetto = $progetto[0];
        
        /** @var AttuazioneControlloRichiesta $progetto */
        //$progetto = $progetto[0];
        //$richiesta = $progetto->getRichiesta();
        //$mandatario = $richiesta->getMandatario();

        
        // Tipologie allegati giustificativi
        //$arrayTipologieAllegatiGiustificativi = ['TIMESHEET_MENSILE', 'DICH_COSTO_ORARIO', 'GIUSTIFICATIVO_SPESA_SING'];
        //$arrayTipologieQuetanzeGiustificativi = ['ESTRATTO_CONTO_F24', 'ESTRATTO_CONTO_SING'];

        $retVal['progetto'] = [
            'id_progetto' => $progetto['_kp_progetto'],
            'contestuale' => 'N',
            'numero_protocollo' => $progetto['numeroprotocollo'],
            'data_protocollo' => $progetto['dataprotocollo'],
            'beneficiario' => $progetto['beneficiario'],
            'codice_fiscale' => $progetto['codicefiscale'],
            'partita_iva' => $progetto['partitaiva'],
            'elenco_documenti' => [],
            'elenco_documenti_amministrativi' => [],
            'costo_presentato' => $progetto['costo_presentato'],
            'costo_ammesso' => $progetto['costo_ammesso'],
            'contributo_concesso' => $progetto['contributo_concesso'],
        ];
        
        $retVal['progetto']['elenco_concessioni'][] = [
            'protocollo_atto_concessione' => null,
            'numero_atto_concessione' => $progetto['nr_atto_concessione'],
            'data_atto_concessione' => $progetto['data_atto_concessione'],
            'file_atto_concessione' => $this->dammiUrl($progetto['concessione_url']),
        ];
        
        // Documenti progetto
        $retVal['progetto']['elenco_documenti'] = array();
        
        $documentiAmministrativi = $this->getRepositoryDocumentiAmministrativi($idProgetto);
        foreach ($documentiAmministrativi as $documentoAmministrativo) {
            $retVal['progetto']['elenco_documenti_amministrativi'][] = [
                'url' => $this->dammiUrl($documentoAmministrativo['url_documento']),
                'tipologia_documento' => $documentoAmministrativo['tipo_documento'],
                'tipologia_caricamento' => $documentoAmministrativo['tipo_ricezione'],
                'protocollo' => '',
                'data' => $documentoAmministrativo['data_documento'],
            ];
        }
        
        $pagamenti = $this->getRepositoryPagamenti($idProgetto);
        $arrayPagamenti = [];
        foreach ($pagamenti as $keyPagamento => $pagamento) {
            $arrayPagamenti[$keyPagamento] = [
                'id_pagamento' => $pagamento['_kp_pagamento'],
                'protocollo_domanda_pagamento' => $pagamento['protocollo'],
                'data_domanda_pagamento' => $pagamento['data_invio'],
                'tipo_domanda_pagamento' => $pagamento['tipologia_comunicazione'],
                'elenco_documenti_allegati' => [],
                'elenco_documenti_istruttoria' => [],
                'elenco_giustificativi' => [],
            ];

            // Giustificativi di spesa
            $giustificativi = $this->getRepositoryGiustificativi($pagamento['_kp_pagamento']);
            foreach ($giustificativi as $keyGiustificativo => $giustificativo) {
                $arrayPagamenti[$keyPagamento]['elenco_giustificativi'][$keyGiustificativo] = [
                    'id_g' => $giustificativo['_kp_fattura'],
                    'url' => $this->dammiUrl($giustificativo['fatturaurl']),
                    'intestatario' => $giustificativo['beneficiario'], 
                    'importo_fattura' => $giustificativo['importorichiesto'],
                    'elenco_imputazioni' => [],
                    'elenco_quietanze' => [],
                ];
                
                // Imputazioni del giustificativo
                $imputazioni = $this->getRepositoryImputazioni($giustificativo['_kp_fattura']);
                foreach ($imputazioni as $imputazione) {
                    $arrayPagamenti[$keyPagamento]['elenco_giustificativi'][$keyGiustificativo]['elenco_imputazioni'][] = [
                        'voce_di_spesa' => $imputazione['voce_di_spesa'],
                        'importo_richiesto' => (float) $imputazione['importo'],
                        'importo_ammesso' => (float) $imputazione['importo_approvato'],
                    ];
                }
                
                // Quietanze del giustificativo
                $quietanze = $this->getRepositoryQuietanze($giustificativo['_kp_fattura']);
                foreach ($quietanze as $quietanza) {
                    $arrayPagamenti[$keyPagamento]['elenco_giustificativi'][$keyGiustificativo]['elenco_quietanze'][] = [
                        'url' => $this->dammiUrl($quietanza['quietanzaurl']),
                        'importo' => $quietanza['importo_quietanza'],
                    ];
                }
            }

            // *************************
            // Elenco documenti allegati

            // Documenti presentati
            $arrayPagamenti[$keyPagamento]["elenco_documenti_allegati"][] = array(
                "url" => $this->dammiUrl($pagamento['url_documento_firmato']),
                "nome" => 'Domanda firmata'
            );

            $arrayPagamenti[$keyPagamento]["elenco_documenti_allegati"][] = array(
                "url" => $this->dammiUrl($pagamento['url_carta_identita_firmata']),
                "nome" => 'Carta d\'identità firmata'
            );

            if ($pagamento['tipologia_comunicazione'] == 'ANTICIPO') {
                $arrayPagamenti[$keyPagamento]["elenco_documenti_allegati"][] = array(
                    "url" => $this->dammiUrl($pagamento['urlfideiussione']),
                    "nome" => 'Fidejussione'
                );
            }


            $documentiPagamento = $this->getRepositoryDocumentiPagamento($pagamento['_kp_pagamento']);
            
            foreach ($documentiPagamento as $documentoPagamento) {
                $arrayPagamenti[$keyPagamento]['elenco_documenti_allegati'][] = [
                    'url' => $this->dammiUrl($documentoPagamento['url']),
                    'nome' => $documentoPagamento['nome'],
                ];
            }
            // *************************


            // *************************
            // Elenco documenti istruttoria
            
            // Atto di liquidazione e ordinativo di pagamento (in questo bando coincidono).
            $documentiIstruttoriaPagamento = $this->getRepositoryDocumentiIstruttoriaPagamento($pagamento['_kp_pagamento']);
            foreach ($documentiIstruttoriaPagamento as $documentoIstruttoriaPagamento) {
                $arrayPagamenti[$keyPagamento]['elenco_documenti_istruttoria'][] = [
                    'tipologia' => $documentoIstruttoriaPagamento['titolo'],
                    'url' => $this->dammiUrl($documentoIstruttoriaPagamento['url']),
                    'importo' => null,
                    'numero' => null,
                    'data' => null,
                ];
            }
            // *************************
        }
        
        $retVal['progetto']['elenco_pagamenti'] = $arrayPagamenti;
        return $retVal;
    }

    /**
     * @param Pagamento $pagamento
     * @return string
     */
    public function getNumeroProtocolloPagamento(Pagamento $pagamento)
    {
        $protocolli = $pagamento->getRichiesteProtocollo();
        $protocollo = $protocolli[0];

        if (is_null($protocollo)) {
            $numeroProtocollo =  '-';
        } else {
            try {
                $numeroProtocollo = $protocollo->getRegistro_pg() . '/' . $protocollo->getAnno_pg() . '/' . $protocollo->getNum_pg();
            } catch (Exception $e) {
                $numeroProtocollo =  '-';
            }
        }
        
        return $numeroProtocollo;
    }

    /**
     * @param Pagamento $pagamento
     * @return string
     */
    public function getDataProtocolloPagamento(Pagamento $pagamento)
    {
        $protocolli = $pagamento->getRichiesteProtocollo();
        $protocollo = $protocolli[0];

        if (is_null($protocollo)) {
            $dataProtocollo =  '-';
        } else {
            try {
                $dataProtocollo = $protocollo->getDataPg()->getTimestamp();
            } catch (Exception $e) {
                $dataProtocollo = '-';
            }
        }
        
        return $dataProtocollo;
    }

    /**
     * @param string|null $documento
     * @return string|null
     */
    public function dammiUrl(?string $path)
    {
        if (!$path) {
            return null;
        }
        
        $url = $this->container->get("funzioni_utili")->encid($path);
        $url = $this->generateUrl('scarica_da_path', ['path_codificato' => $url]);
        return $url;
    }
}
