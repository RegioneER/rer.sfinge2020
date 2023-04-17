<?php

namespace FunzioniServizioBundle\Controller;

use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\Pagamento;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Entity\Richiesta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SfingeBundle\Entity\Procedura;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class EsportazioneBando773Controller extends Controller
{
    const BANDO_773 = 7;
    
    /**
     * @Route("/esportazione_bando/{idBando}/{dataFiltro}/{token}", name="funzioni_servizio_esportazione_bando", requirements={"idBando"="\d+", "dataFiltro"="[0-9]{4}-[0-9]{2}-[0-9]{2}", "token"="w4ggZm5jdnJhZ3IgZmJ5YiBwdXYgZm4gcXYgYWJhIGZuY3JlciwgYWJhIHB1diBmJ3Z5eWhxciBxdiBmbmNyZXIgciB2dGFiZW4gcGJmw6wgY3Jlc3ZhYiB5biBmaG4gZmdyZmZuIHZ0YWJlbmFtbi4="})
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     * 
     * @param $idBando
     * @param $dataFiltro
     * @return JsonResponse
     * @throws \Exception
     */
    public function esportazioneBandoAction($idBando, $dataFiltro)
    {
        $progetti = [];
        $dataFiltro = explode('-', $dataFiltro);
        if ($idBando == self::BANDO_773) {
            $progetti = $this->getJsonBando773(new \DateTime($dataFiltro[0] . '-' . $dataFiltro[1] . '-' . $dataFiltro[2] . ' 00:00:00')); 
        }
        
        return new JsonResponse($progetti);
    }

    /**
     * @param $idBando
     * @param \DateTime $dataFiltro
     * @return mixed
     * @throws \Exception
     */
    public function getRepositoryBando($idBando, \DateTime $dataFiltro)
    {
        $dql = 'SELECT richiesta_attuazione '
            . 'FROM AttuazioneControlloBundle:AttuazioneControlloRichiesta richiesta_attuazione '
            . 'JOIN richiesta_attuazione.richiesta richiesta '
            . 'JOIN richiesta.procedura procedura '
            . 'JOIN richiesta_attuazione.pagamenti pagamenti '
            . 'JOIN pagamenti.mandato_pagamento mandato_pagamento '
            . 'JOIN mandato_pagamento.atto_liquidazione atto_liquidazione '
            . 'WHERE procedura = :idProcedura AND atto_liquidazione.data >= :dataLimiteMin '
            . 'GROUP BY richiesta_attuazione.id';

        $retVal =  $this->getDoctrine()->getManager()
            ->createQuery($dql)
            ->setParameter('idProcedura', $idBando)
            ->setParameter('dataLimiteMin', $dataFiltro->format('Y-m-d H:i:s'))
            ->getResult();

        return $retVal;
    }

    /**
     * @param \DateTime $dataFiltro
     * @return array
     * @throws \Exception
     */
    public function getJsonBando773(\DateTime $dataFiltro)
    {
        /** @var AttuazioneControlloRichiesta[] $progetti */
        $progetti = $this->getRepositoryBando(self::BANDO_773, $dataFiltro);
        
        if (empty($progetti)) {
            return array();
        }
        
        /** @var Procedura $procedura */
        $procedura = $progetti[0]->getRichiesta()->getProcedura();
        
        $bando = [
            'bando' => [
                'titolo' => $procedura->getTitolo(),
                'numero_delibera' => $procedura->getAtto()->getNumero(),
                'data_delibera' => $procedura->getAtto()->getDataPubblicazione()->getTimestamp(),
            ]
        ];

        $elencoProgetti = [];
        foreach ($progetti as $progetto) {
            /** @var Richiesta $richiesta */
            $richiesta = $progetto->getRichiesta();
            
            $elencoProgetti[] = [
                'id_progetto' => $progetto->getId(),
                'numero_protocollo' => $this->getNumeroProtocolloProgetto($progetto),
                'data_protocollo' => $this->getDataProtocolloProgetto($progetto),
                'beneficiario' => $richiesta->getMandatario()->getDenominazione(),
                'codice_fiscale' => $richiesta->getMandatario()->getSoggetto()->getCodiceFiscale(),
                'partita_iva' => $richiesta->getMandatario()->getSoggetto()->getPartitaIva(),
            ];
        }
        
        $bando['bando']['elenco_progetti'] = $elencoProgetti;
        return $bando;
    }

    /**
     * @Route("/esportazione_progetto/{idProgetto}/{token}", name="funzioni_servizio_esportazione_progetto", requirements={"idProgetto"="\d+", "token"="w4ggZm5jdnJhZ3IgZmJ5YiBwdXYgZm4gcXYgYWJhIGZuY3JlciwgYWJhIHB1diBmJ3Z5eWhxciBxdiBmbmNyZXIgciB2dGFiZW4gcGJmw6wgY3Jlc3ZhYiB5biBmaG4gZmdyZmZuIHZ0YWJlbmFtbi4="})
     * @Security("has_role('ROLE_VERIFICHE_ESTERNE')")
     * 
     * @param $idProgetto
     * @return JsonResponse
     * @throws \Exception
     */
    public function esportazioneProgettoAction($idProgetto)
    {
        /** @var AttuazioneControlloRichiesta $progetto */
        $progetto = $this->getDoctrine()->getManager()->getRepository("AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta")->find($idProgetto);

        $progetti = [];
        if ($progetto->getRichiesta()->getProcedura()->getId() == self::BANDO_773) {
            $progetti = $this->getJsonProgettoBando773($idProgetto);
        }
        
        return new JsonResponse($progetti);
    }

    /**
     * @param $idProgetto
     * @return mixed
     * @throws \Exception
     */
    public function getRepositoryProgetto($idProgetto)
    {
        $dql = 'SELECT richiesta_attuazione '
            . 'FROM AttuazioneControlloBundle:AttuazioneControlloRichiesta richiesta_attuazione '
            . 'WHERE richiesta_attuazione = :idProgetto';

        $retVal =  $this->getDoctrine()->getManager()
            ->createQuery($dql)
            ->setParameter('idProgetto', $idProgetto)
            ->getResult();

        return $retVal;
    }

    /**
     * @param $idProgetto
     * @return array
     * @throws \Exception
     */
    public function getJsonProgettoBando773($idProgetto)
    {
        /** @var AttuazioneControlloRichiesta[] $progetto */
        $progetto = $this->getRepositoryProgetto($idProgetto);

        if (empty($progetto)) {
            return array();
        }

        /** @var AttuazioneControlloRichiesta $progetto */
        $progetto = $progetto[0];
        $richiesta = $progetto->getRichiesta();
        $mandatario = $richiesta->getMandatario();

        // Importi
        $costi = $this->totaliPianoCosto($richiesta);
        
        // Tipologie allegati giustificativi
        $arrayTipologieAllegatiGiustificativi = ['TIMESHEET_MENSILE', 'DICH_COSTO_ORARIO', 'GIUSTIFICATIVO_SPESA_SING'];
        $arrayTipologieQuetanzeGiustificativi = ['ESTRATTO_CONTO_F24', 'ESTRATTO_CONTO_SING'];

        $retVal['progetto'] = [
            'id_progetto' => $progetto->getId(),
            'contestuale' => 'N',
            'numero_protocollo' => $this->getNumeroProtocolloProgetto($progetto),
            'data_protocollo' => $this->getDataProtocolloProgetto($progetto),
            'beneficiario' => $mandatario->getDenominazione(),
            'codice_fiscale' => $mandatario->getSoggetto()->getCodiceFiscale(),
            'partita_iva' => $mandatario->getSoggetto()->getPartitaIva(),
            'elenco_documenti' => [],
            'elenco_documenti_amministrativi' => [],
            'costo_presentato' => $costi['totali']['presentato_1'],
            'costo_ammesso' => $costi['totali']['ammissibile_1'],
            'contributo_concesso' => $richiesta->getIstruttoria()->getContributoAmmesso(),
        ];
        
        // Atto di concessione
        if ($richiesta->getIstruttoria()) {
            $url = $this->dammiUrl($richiesta->getIstruttoria()->getAttoConcessioneAtc()->getDocumentoAtto());
            $retVal['progetto']['elenco_concessioni'][] = [
                'protocollo_atto_concessione' => null,
                'numero_atto_concessione' => null,
                'data_atto_concessione' => $richiesta->getIstruttoria()->getDataContributo()->getTimestamp(),
                'file_atto_concessione' => $url,
            ];
        }
        
        // Documenti progetto
        $url = $this->dammiUrl($richiesta->getDocumentoRichiestaFirmato());
        if ($url) {
            $retVal['progetto']['elenco_documenti'][] = [
                'url' => $url,
                'nome' => 'Domanda firmata',
            ];
        }
        
        $url = $this->dammiUrl($richiesta->getIstruttoria()->getAttoAmmissibilitaAtc()->getDocumentoAtto());
        if ($url) {
            $retVal['progetto']['elenco_documenti'][] = [
                'url' => $url,
                'nome' => 'Documento atto di ammissibilità',
            ];
        }

        foreach ($richiesta->getDocumentiRichiesta() as $documentoProgetto) {
            $url = $this->dammiUrl($documentoProgetto->getDocumentoFile());
            $retVal['progetto']['elenco_documenti'][] = [
                'url' => $url,
                'nome' => $documentoProgetto->getDocumentoFile()->getNomeOriginale(),
            ];
        }

        foreach ($progetto->getPagamenti() as $pagamento) {
            foreach ($pagamento->getDocumentiIstruttoriaBando7() as $documentoIstruttoriaBando7) {
                $url = $this->dammiUrl($documentoIstruttoriaBando7->getDocumentoFile());

                $dataRicezione = null;
                if ($documentoIstruttoriaBando7->getDataRicevuto()) {
                    $dataRicezione = $documentoIstruttoriaBando7->getDataRicevuto()->getTimestamp(); 
                }
                
                $retVal['progetto']['elenco_documenti_amministrativi'][] = [
                    'url' => $url,
                    'tipologia_documento' => $documentoIstruttoriaBando7->getDocumentoFile()->getNomeOriginale(),
                    'tipologia_caricamento' => 'M',
                    'protocollo' => '',
                    'data' => $dataRicezione,
                ];
            }
        }
        
        /** @var Pagamento[] $pagamenti */
        $pagamenti = $progetto->getPagamenti();
        
        $arrayPagamenti = [];
        foreach ($pagamenti as $keyPagamento => $pagamento) {
            $arrayPagamenti[$keyPagamento] = [
                'id_pagamento' => $pagamento->getId(),
                'protocollo_domanda_pagamento' => $this->getNumeroProtocolloPagamento($pagamento),
                'data_domanda_pagamento' => $this->getDataProtocolloPagamento($pagamento),
                'tipo_domanda_pagamento' => $pagamento->getModalitaPagamento()->getCodice(),
                'elenco_documenti_allegati' => [],
                'elenco_documenti_istruttoria' => [],
            ];
            
            // Giustificativi di spesa
            foreach ($pagamento->getGiustificativi() as $keyGiustificativo => $giustificativo) {
                if ($giustificativo->getTipologiaGiustificativo()->getInvisibile()) {
                    continue;
                }
                
                $allegatiGiustificativo = $this->getDoctrine()->getManager()->getRepository("AttuazioneControlloBundle\Entity\DocumentoGiustificativo")->findBy(array("giustificativo_pagamento" => $giustificativo->getId()));
                $urlGiustificativo = '';
                $urlQuietanza = '';
                
                foreach ($allegatiGiustificativo as $allegatoGiustificativo) {
                    if (in_array($allegatoGiustificativo->getDocumentoFile()->getTipologiaDocumento()->getCodice(), $arrayTipologieAllegatiGiustificativi)) {
                        $urlGiustificativo = $this->dammiUrl($allegatoGiustificativo->getDocumentoFile());
                    } elseif (in_array($allegatoGiustificativo->getDocumentoFile()->getTipologiaDocumento()->getCodice(), $arrayTipologieQuetanzeGiustificativi)) {
                        $urlQuietanza = $this->dammiUrl($allegatoGiustificativo->getDocumentoFile());
                    }
                }
               
                if ($giustificativo->getDenominazioneFornitore()) {
                    $intestatario = $giustificativo->getDenominazioneFornitore();
                    $importoFattura =  (float) $giustificativo->getImportoGiustificativo();
                } elseif ($giustificativo->getEstensione() && $giustificativo->getEstensione()->getNome()) {
                    $intestatario = $giustificativo->getEstensione()->getNome() . ' ' . $giustificativo->getEstensione()->getCognome();
                    $importoRi =  round(($giustificativo->getEstensione()->getCostoOrario() * $giustificativo->getEstensione()->getNumeroOreRi()), 2);
                    $importoSs =  round(($giustificativo->getEstensione()->getCostoOrario() * $giustificativo->getEstensione()->getNumeroOreSs()), 2);
                    $importoFattura = (float) round(($importoRi + $importoSs), 2);
                } else {
                    $intestatario = '-';
                    $importoFattura = null;
                }
                
                $arrayPagamenti[$keyPagamento]['elenco_giustificativi'][$keyGiustificativo] = [
                    'id_g' => $giustificativo->getId(),
                    'url' => $urlGiustificativo,
                    'intestatario' => $intestatario, 
                    'importo_fattura' => $importoFattura,
                    'elenco_imputazioni' => [],
                    'elenco_quietanze' => [],
                ];
                
                // Imputazioni del giustificativo
                foreach ($giustificativo->getVociPianoCosto() as $imputazione) {
                    $arrayPagamenti[$keyPagamento]['elenco_giustificativi'][$keyGiustificativo]['elenco_imputazioni'][] = [
                        'voce_di_spesa' => $giustificativo->getTipologiaGiustificativo()->getDescrizioneTabGiustificativi(),
                        'importo_richiesto' => (float) $imputazione->getImporto(),
                        'importo_ammesso' => (float) $imputazione->getImportoApprovato(),
                    ];
                }
                
                // Quietanze del giustificativo
                if ($urlQuietanza) {
                    $arrayPagamenti[$keyPagamento]['elenco_giustificativi'][$keyGiustificativo]['elenco_quietanze'][] = [
                        'url' => $urlQuietanza,
                        'importo' => null,
                    ]; 
                }
            }

            // *************************
            // Elenco documenti allegati
            $url = $this->dammiUrl($pagamento->getDocumentoPagamentoFirmato());
            if ($url) {
                $arrayPagamenti[$keyPagamento]['elenco_documenti_allegati'][] = [
                    'url' => $url,
                    'nome' => 'Domanda firmata',
                ];
            }
            
            $documentiCaricatiPagamento = $this->getDoctrine()->getManager()
                ->getRepository("AttuazioneControlloBundle\Entity\DocumentoEstensionePagamento")
                ->findBy(array("estensione_pagamento" => $pagamento->getEstensione()->getId()));

            foreach ($documentiCaricatiPagamento as $documentoCaricatoPagamento) {
                $url = $this->dammiUrl($documentoCaricatoPagamento->getDocumentoFile());
                $arrayPagamenti[$keyPagamento]['elenco_documenti_allegati'][] = [
                    'url' => $url,
                    'nome' => $documentoCaricatoPagamento->getDocumentoFile()->getNomeOriginale(),
                ];
            }
            
            // *************************


            // *************************
            // Elenco documenti istruttoria
            
            // Atto di liquidazione e ordinativo di pagamento (in questo bando coincidono).
            $url = $this->dammiUrl($pagamento->getMandatoPagamento()->getAttoLiquidazione()->getDocumento());
            if ($url) {
                $arrayPagamenti[$keyPagamento]['elenco_documenti_istruttoria'][] = [
                    'tipologia' => 'Atto di liquidazione',
                    'url' => $url,
                    'importo' => (float) $pagamento->getMandatoPagamento()->getImportoPagato(),
                    'numero' => $pagamento->getMandatoPagamento()->getNumeroMandato(),
                    'data' => $pagamento->getMandatoPagamento()->getDataMandato()->getTimestamp(),
                ];
            }

            // Scheda riepilogativa
            if ($pagamento->getEsitiIstruttoriaPagamento()) {
                $url = $this->dammiUrl($pagamento->getEsitiIstruttoriaPagamento()->last()->getDocumento());
                $arrayPagamenti[$keyPagamento]['elenco_documenti_istruttoria'][] = [
                    'tipologia' => 'Scheda riepilogativa',
                    'url' => $url,
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
     * @param Richiesta $richiesta
     * @return array
     */
    public function totaliPianoCosto(Richiesta $richiesta) {
        $dati = array();
        $somme = array("presentato" => 0, "taglio" => 0);
        $totali = $this->getDoctrine()->getManager()->getRepository("RichiesteBundle:Richiesta")->getTotaliRichiesta($richiesta->getId());
        foreach ($totali as $chiave => $valore) {
            if (preg_match("/^presentato/", $chiave)) {
                $somme["presentato"] += $valore;
            } elseif (preg_match("/^taglio/", $chiave)) {
                $somme["taglio"] += $valore;
            }
        }

        $dati["totali"] = $totali;
        return $dati;
    }

    /**
     * @param AttuazioneControlloRichiesta $progetto
     * @return string
     */
    public function getNumeroProtocolloProgetto(AttuazioneControlloRichiesta $progetto)
    {
        $richiesteProtocollo = $progetto->getRichiesta()->getRichiesteProtocollo();
        $richiestaProtocollo = $richiesteProtocollo[0];

        if (is_null($richiestaProtocollo)) {
            $numeroRichiestaProtocollo =  '-';
        } else {
            try {
                $numeroRichiestaProtocollo = $richiestaProtocollo->getRegistro_pg() . '/' . $richiestaProtocollo->getAnno_pg() . '/' . $richiestaProtocollo->getNum_pg();
            } catch (\Exception $e) {
                $numeroRichiestaProtocollo =  '-';
            }
        }
        
        return $numeroRichiestaProtocollo;
    }

    /**
     * @param AttuazioneControlloRichiesta $progetto
     * @return string
     */
    public function getDataProtocolloProgetto(AttuazioneControlloRichiesta $progetto)
    {
        $richiesteProtocollo = $progetto->getRichiesta()->getRichiesteProtocollo();
        $richiestaProtocollo = $richiesteProtocollo[0];

        if (is_null($richiestaProtocollo)) {
            $dataRichiestaProtocollo =  '-';
        } else {
            try {
                $dataRichiestaProtocollo = $richiestaProtocollo->getDataPg()->getTimestamp();
            } catch (\Exception $e) {
                $dataRichiestaProtocollo = '-';
            }
        }
        
        return $dataRichiestaProtocollo;
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
            } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                $dataProtocollo = '-';
            }
        }
        
        return $dataProtocollo;
    }

    /**
     * @param DocumentoFile|null $documento
     * @return string|null
     */
    public function dammiUrl(?DocumentoFile $documento)
    {
        if (!$documento) {
            return null;
        }
        
        $url = $this->container->get("funzioni_utili")->encid($documento->getPath() . $documento->getNome());
        $url = $this->generateUrl('scarica', ['path_codificato' => $url]);
        return $url;
    }
}
