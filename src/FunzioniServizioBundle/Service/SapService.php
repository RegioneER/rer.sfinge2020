<?php

namespace FunzioniServizioBundle\Service;

use AnagraficheBundle\Entity\Persona;
use SoapClient;
use SoapFault;
use SoggettoBundle\Entity\Soggetto;
use stdClass;

/**
 * Class SapService
 */
class SapService
{
    /**
     * @var SoapClient
     */
    private $client;

    protected $container;

    /**
     * SapService constructor.
     */
    public function __construct($container)
    {
        ini_set('default_socket_timeout', 0);
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getReq()
    {
        return htmlentities($this->client->__getLastRequest());
    }

    /**
     * @return string
     */
    public function getRes()
    {
        return htmlentities($this->client->__getLastResponse());
    }

    /**
     * @param     $serverLocation
     * @param int $doTrace
     *
     * @return mixed
     * @throws SoapFault
     */
    private function inizializzaClient($serverLocation, $env)
    {
        if ($env == 'Dev') {
            $pemFile = '/../../../app/config/certs/SFINGE_POT.pem';
        } elseif ($env == 'Prod') {
            $pemFile = '/../../../app/config/certs/SFINGE_POP.pem';
        } else {
            return false;
        }

        $options = [
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'local_cert'         => __DIR__ . $pemFile,
            //'passphrase'         => 'SFINGE_POT',
            'connection_timeout' => 2400,
            //'exceptions' => true,
            'authentication' => SOAP_AUTHENTICATION_DIGEST,
        ];

        $this->client = new SoapClient($serverLocation, $options);
    }

    /**
     * @param        $partitaIva
     * @param string $env
     *
     * @return bool
     */
    public function ricercaBeneficiari($partitaIva, $env = 'Prod')
    {
        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_BENEFICIARI.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        try {
            return $this->client->Z_WS_RICERCA_BENEFICIARI(['I_BUKRS' => 'RER', 'I_STCD1' => $partitaIva]);
        } catch (SoapFault $e) {
            return false;
        }
    }

    /**
     * @param        $numeroBeneficiario
     * @param string $env
     *
     * @return bool
     */
    public function visualizzaBeneficiario($numeroBeneficiario, $env = 'Prod')
    {
        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_BENEFICIARI.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        return $this->client->Z_WS_VISUALIZZA_BENEFICIARIO(['I_BUKRS' => 'RER', 'I_LIFNR' => $numeroBeneficiario]);
    }

    /**
     * @param Soggetto $soggetto
     * @param string   $env
     *
     * @return stdClass
     */
    public function creaBeneficiari(Soggetto $soggetto, $env = 'Prod')
    {
        // Il WS non accetta Forlì ma bensì Forli'
        $comuniDaSanare = [
            "forlì" => "Forli'",
            "roma capitale" => "Roma",
        ];

        $comune = $soggetto->getComune()->getDenominazione();
        if (array_key_exists(mb_strtolower($soggetto->getComune()->getDenominazione()), $comuniDaSanare)) {
            $comune = $comuniDaSanare[mb_strtolower($soggetto->getComune()->getDenominazione())];
        }

        $iTabEmail = [];
        if ($soggetto->getEmailPec() !== null) {
            $iTabEmail[] = [
                'SMTP_ADDR' => $soggetto->getEmailPec(), // Indirizzo e-mail CHAR(241)
                'ZZ_PEC'    => 'S'
            ];
        }

        if ($soggetto->getEmail() !== null) {
            $iTabEmail[] = [
                'SMTP_ADDR' => $soggetto->getEmail(), // Indirizzo e-mail CHAR(241)
                'ZZ_PEC'    => 'N'
            ];
        }

        $iben = [
            'BUKRS'            => 'RER',//	Società	                                                        CHAR(4)	    Obbligatorio
            'EKORG'            => '',//	Organizzazione acquisti	                                        CHAR(4)	    Non ammesso
            'KTOKK'            => '',//	Gruppo conti fornitore	                                        CHAR(4)	    Non ammesso
            'ZZ_CAT_EC'        => $soggetto->zzCatEc,//	Categoria economica	                                            NUMC(3)	    Quelle previste al punto 4
            'ZZ_GR_BEN'        => '',//	Gruppo beneficiari	                                            CHAR(1)	    Non ammesso
            'LIFNR'            => '',//	Numero beneficiario	                                            CHAR(10)
            'RAGIONE_SOCIALE'  => mb_strtoupper(mb_substr($soggetto->getDenominazione(), 0, 140)),// Ragione sociale	                                                CHAR(140)	Obbligatorio
            'ZZ_RAG_SOC_BREVE' => mb_strtoupper(mb_substr($soggetto->getDenominazione(), 0, 20)),// Ragione sociale breve	                                        CHAR(20)
            'STCD1'            => $soggetto->getCodiceFiscale(),//	Codice Fiscale	                                                CHAR(16)	Obbligatorio
            'STCD2'            => $soggetto->getPartitaIva(),//	Partita IVA 	                                                CHAR(11)
            'STREET'           => mb_strtoupper($soggetto->getVia()),//	Via	                                                            CHAR(60)
            'HOUSE_NUM1'       => '',//	Numero civico	                                                CHAR(10)	Non gestito
            'HOUSE_NUM2'       => '',//	Precisazione relativa al numero civico	                        CHAR(10)	Non gestito
            'CITY1'            => mb_strtoupper($comune),//	Località	                                                    CHAR(40)
            'CITY2'            => '',//	Frazione	                                                    CHAR(40)	Non gestito
            'POST_CODE1'       => $soggetto->getCap(),//	Codice di avviamento postale della città	                    CHAR(10)
            'COUNTRY'          => $this->getCodiceIso3166($soggetto->getStato()->getCodice()),//	Codice paese     	                                            CHAR(3)	    IT per l’Italia
            'REGION'           => mb_strtoupper($soggetto->region),//	Regione (stato federale, stato federato, provincia, contea)	    CHAR(3)
            'ALTKN'            => '',//	N. record anagrafico precedente	                                CHAR(10)	Non ammesso
            'KONZS'            => '',//	Chiave gruppo	                                                CHAR(10)	Non ammesso
            'ZZ_NUM_LOC_SPESE' => '',//	Codice localizzazione spese	                                    CHAR(6)	    Non ammesso
            'ZZ_NUM_LOC_OPERE' => '',//	Codice localizzazione opere	                                    CHAR(6)
            'ZZ_COD_CAM_COMM'  => $soggetto->zzCodCamComm,//	Codice di iscrizione alla camera di commercio	                CHAR(8)
            'ZZ_NUM_ALBO'      => '',//	Numero albo	                                                    CHAR(8)
            'ZZ_TP_ALBO'       => '',//	Tipo albo	                                                    CHAR(3)
            'ZZ_FLAG_DIP'      => '',//	Flag dipendente	                                                CHAR(1)
            'ZZ_FLAG_INCASSO'  => '',//	Flag incassa direttamente	                                    CHAR(1)	    Non ammesso
            'ZZ_COD_DAT_LAV'   => '',//	Codice datore di lavoro	                                        CHAR(10)	Non ammesso
            'ZZ_RAG_SOC_ENTE'  => '',//	Ragione sociale ente	                                        CHAR(60)	Non ammesso
            'ZZ_IND_ENTE'      => '',//	Indirizzo ente	                                                CHAR(60)	Non ammesso
            'ZZ_NAME_FIRST'    => $soggetto->zzNameFirst,//	Nome	                                                        CHAR(40)	Se si indica occorre inserire anche COGNOME/ DATA DI NASCITA/ SESSO
            'ZZ_NAME_LAST'     => $soggetto->zzNameLast,//	Cognome	                                                        CHAR(40)	Se si indica occorre inserire anche NOME/ DATA DI NASCITA/ SESSO
            'GBDAT'            => $soggetto->gbdat,//	Data di nascita della persona soggetta a rit. d'acconto	        CHAR(8)	    Se si indica occorre inserire anche NOME/ COGNOME/ SESSO
            'SEXKZ'            => $soggetto->sexkz,//	Chiave sesso utenti con obbligo di rit. d'acconto	            CHAR(1)	    1 = Maschio 2 = Femmina Se si indica occorre inserire anche NOME/ COGNOME/ DATA DI NASCITA
            'ZZ_FISC_COM'      => '',//	Codice Fiscale Comune	                                        CHAR(4)	    Non ammesso
            'ZZ_ISTAT_COMUNE'  => '',//	Codice ISTAT	                                                CHAR(6)	    Derivato
            'ZZ_ISTAT_PR'      => '',//	Codice ISTAT Provincia	                                        CHAR(3)	    Non ammesso
            'ZZ_FISC_PAESE'    => '',//	Codice Fiscale Paese	                                        CHAR(4)	    Non ammesso
            'ZZ_ISTAT_PSEEX'   => '',//	Codice stato estero Agenzia Entrate	                            CHAR(3)	    Derivato
            'ZZ_STRANIERO'     => '',//	Flag straniero	                                                CHAR(1)	    Non ammesso
            'DESCR_BUKRS'      => '',//	Definizione della società o della ditta	                        CHAR(25)	Derivato
            'DESCR_PAESE'      => '',//	Nome paese	                                                    CHAR(15)	Derivato
            'DESCR_PROVINCIA'  => '',//	Denominazione	                                                CHAR(20)	Derivato
            'DESCR_NUOVO_BEN'  => '',//	Nome 1	                                                        CHAR(35)	Derivato
            'DESCR_LOC_S'      => '',//	Descrizione	                                                    CHAR(120)	Derivato
            'DESCR_LOC_O'      => '',//	Descrizione	                                                    CHAR(120)	Derivato
            'DESCR_TP_ALBO'    => '',//	Descrizione	                                                    CHAR(50)	Derivato
            'DESCR_DAT_LAV'    => '',//	Nome 1	                                                        CHAR(35)	Derivato
            'DESCR_COMUNE_N'   => '',//	Descrizione Comune	                                            CHAR(40)	Derivato
            'DESCR_PROV_N'     => '',//	Denominazione	                                                CHAR(20)	Derivato
            'DESCR_PAESE_N'    => '',//	Descrizione Comune	                                            CHAR(40)	Derivato
            'DESCR_COD_770'    => '',//	Descrizione stato estero Agenzia Entrate	                    CHAR(50)	Derivato
            'SPERR'            => '',//	Blocco centrale di registrazione	                            CHAR(1)	    Non ammesso
            'SPERM'            => '',//	Blocco acquisti assegnato a livello centrale	                CHAR(1)	    Non ammesso
            'AKONT'            => '',//	Conto di riconciliazione nella contabilità generale	            CHAR(10)	Non ammesso
            'EXTENSION1'       => '',//	Ampliamento (solo per conversione dati) (per es. linea dati)	CHAR(40)	Non ammesso
            'EXTENSION2'       => '',//	Ampliamento (solo per conversione dati) (per es. telebox)	    CHAR(40)	Non ammesso
            'TEL_NUMBER'       => $soggetto->getTel(),//	Primo numero di telefono: prefisso + numero	CHAR(30)
            'TEL_EXTENS'       => '',//	Primo numero di telefono: interno	                            CHAR(10)
            'MOB_NUMBER'       => '',//	Primo n. cellulare: prefisso + numero abbonato	                CHAR(30)
            'FAX_NUMBER'       => $soggetto->getFax(),//	Primo numero fax: prefisso + numero	        CHAR(30)
            'FAX_EXTENS'       => '',//	Primo numero fax: numero interno	                            CHAR(10)
            'SMTP_ADDR'        => '',//	Indirizzo e-mail	                                            CHAR(241)	Non ammesso
            'ADRNR'            => '',//	Indirizzo	                                                    CHAR(10)	Non ammesso
            'ZZ_PEC'           => '',//	Flag PEC e-mail	                                                CHAR(1)	    Non ammesso
            //'ZZ_CAT_EC_ORG'    => '',//	Categoria economica originale	                                NUMC(3)	    Non ammesso
        ];

        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_BENEFICIARI.wsdl', $env);
            return $this->client->Z_WS_CREA_BENEFICIARI(['I_BEN' => $iben, 'I_TAB_EMAIL' => $iTabEmail]);
        } catch (SoapFault $e) {
            $this->container->get('logger')->error($e->getMessage());
            $result = new stdClass();
            $result->E_RC = -1;
            $result->E_MESSAGES = $e->getMessage();
            return $result;
        }
    }

    /**
     * @param Soggetto $soggetto
     * @param string   $env
     *
     * @return bool
     */
    public function modificaBeneficiario(Soggetto $soggetto, $env = 'Prod')
    {
        // Il WS non accetta Forlì ma bensì Forli'
        $comuniDaSanare = [
            "forlì" => "Forli'",
            "roma capitale" => "Roma",
        ];

        $comune = $soggetto->getComune()->getDenominazione();
        if (array_key_exists(mb_strtolower($soggetto->getComune()->getDenominazione()), $comuniDaSanare)) {
            $comune = $comuniDaSanare[mb_strtolower($soggetto->getComune()->getDenominazione())];
        }

        $iben = [
            'BUKRS'            => 'RER',//	Società	                                                        CHAR(4)	    Obbligatorio
            'EKORG'            => '',//	Organizzazione acquisti	                                        CHAR(4)	    Non ammesso
            'KTOKK'            => '',//	Gruppo conti fornitore	                                        CHAR(4)	    Non ammesso
            'ZZ_CAT_EC'        => $soggetto->getFormaGiuridica()->getCategoriaEconomicaSap(),//	Categoria economica	                                            NUMC(3)	    Quelle previste al punto 4
            'ZZ_GR_BEN'        => '',//	Gruppo beneficiari	                                            CHAR(1)	    Non ammesso
            'LIFNR'            => $soggetto->getLifnrSap(),//	Numero beneficiario	                                            CHAR(10)
            'RAGIONE_SOCIALE'  => mb_strtoupper(mb_substr($soggetto->getDenominazione(), 0, 140)),// Ragione sociale	                                                CHAR(140)	Obbligatorio
            'ZZ_RAG_SOC_BREVE' => mb_strtoupper(mb_substr($soggetto->getDenominazione(), 0, 20)),// Ragione sociale breve	                                        CHAR(20)
            'STCD1'            => $soggetto->getCodiceFiscale(),//	Codice Fiscale	                                                CHAR(16)	Obbligatorio
            'STCD2'            => $soggetto->getPartitaIva(),//	Partita IVA 	                                                CHAR(11)
            'STREET'           => mb_strtoupper($soggetto->getVia()),//	Via	                                                            CHAR(60)
            'HOUSE_NUM1'       => '',//	Numero civico	                                                CHAR(10)	Non gestito
            'HOUSE_NUM2'       => '',//	Precisazione relativa al numero civico	                        CHAR(10)	Non gestito
            'CITY1'            => mb_strtoupper($comune),//	Località	                                                    CHAR(40)
            'CITY2'            => '',//	Frazione	                                                    CHAR(40)	Non gestito
            'POST_CODE1'       => $soggetto->getCap(),//	Codice di avviamento postale della città	                    CHAR(10)
            'COUNTRY'          => $this->getCodiceIso3166($soggetto->getStato()->getCodice()),//	Codice paese     	                                            CHAR(3)	    IT per l’Italia
            'REGION'           => mb_strtoupper($soggetto->getComune()->getProvincia()->getSiglaAutomobilistica()),//	Regione (stato federale, stato federato, provincia, contea)	    CHAR(3)
            'ALTKN'            => '',//	N. record anagrafico precedente	                                CHAR(10)	Non ammesso
            'KONZS'            => '',//	Chiave gruppo	                                                CHAR(10)	Non ammesso
            'ZZ_NUM_LOC_SPESE' => '',//	Codice localizzazione spese	                                    CHAR(6)	    Non ammesso
            'ZZ_NUM_LOC_OPERE' => '',//	Codice localizzazione opere	                                    CHAR(6)
            'ZZ_COD_CAM_COMM'  => $soggetto->getRea(),//	Codice di iscrizione alla camera di commercio	                CHAR(8)
            'ZZ_NUM_ALBO'      => '',//	Numero albo	                                                    CHAR(8)
            'ZZ_TP_ALBO'       => '',//	Tipo albo	                                                    CHAR(3)
            'ZZ_FLAG_DIP'      => '',//	Flag dipendente	                                                CHAR(1)
            'ZZ_FLAG_INCASSO'  => '',//	Flag incassa direttamente	                                    CHAR(1)	    Non ammesso
            'ZZ_COD_DAT_LAV'   => '',//	Codice datore di lavoro	                                        CHAR(10)	Non ammesso
            'ZZ_RAG_SOC_ENTE'  => '',//	Ragione sociale ente	                                        CHAR(60)	Non ammesso
            'ZZ_IND_ENTE'      => '',//	Indirizzo ente	                                                CHAR(60)	Non ammesso
            'ZZ_NAME_FIRST'    => '',//	Nome	                                                        CHAR(40)	Se si indica occorre inserire anche COGNOME/ DATA DI NASCITA/ SESSO
            'ZZ_NAME_LAST'     => '',//	Cognome	                                                        CHAR(40)	Se si indica occorre inserire anche NOME/ DATA DI NASCITA/ SESSO
            'GBDAT'            => '',//	Data di nascita della persona soggetta a rit. d'acconto	        CHAR(8)	    Se si indica occorre inserire anche NOME/ COGNOME/ SESSO
            'SEXKZ'            => '',//	Chiave sesso utenti con obbligo di rit. d'acconto	            CHAR(1)	    1 = Maschio 2 = Femmina Se si indica occorre inserire anche NOME/ COGNOME/ DATA DI NASCITA
            'ZZ_FISC_COM'      => '',//	Codice Fiscale Comune	                                        CHAR(4)	    Non ammesso
            'ZZ_ISTAT_COMUNE'  => '',//	Codice ISTAT	                                                CHAR(6)	    Derivato
            'ZZ_ISTAT_PR'      => '',//	Codice ISTAT Provincia	                                        CHAR(3)	    Non ammesso
            'ZZ_FISC_PAESE'    => '',//	Codice Fiscale Paese	                                        CHAR(4)	    Non ammesso
            'ZZ_ISTAT_PSEEX'   => '',//	Codice stato estero Agenzia Entrate	                            CHAR(3)	    Derivato
            'ZZ_STRANIERO'     => '',//	Flag straniero	                                                CHAR(1)	    Non ammesso
            'DESCR_BUKRS'      => '',//	Definizione della società o della ditta	                        CHAR(25)	Derivato
            'DESCR_PAESE'      => '',//	Nome paese	                                                    CHAR(15)	Derivato
            'DESCR_PROVINCIA'  => '',//	Denominazione	                                                CHAR(20)	Derivato
            'DESCR_NUOVO_BEN'  => '',//	Nome 1	                                                        CHAR(35)	Derivato
            'DESCR_LOC_S'      => '',//	Descrizione	                                                    CHAR(120)	Derivato
            'DESCR_LOC_O'      => '',//	Descrizione	                                                    CHAR(120)	Derivato
            'DESCR_TP_ALBO'    => '',//	Descrizione	                                                    CHAR(50)	Derivato
            'DESCR_DAT_LAV'    => '',//	Nome 1	                                                        CHAR(35)	Derivato
            'DESCR_COMUNE_N'   => '',//	Descrizione Comune	                                            CHAR(40)	Derivato
            'DESCR_PROV_N'     => '',//	Denominazione	                                                CHAR(20)	Derivato
            'DESCR_PAESE_N'    => '',//	Descrizione Comune	                                            CHAR(40)	Derivato
            'DESCR_COD_770'    => '',//	Descrizione stato estero Agenzia Entrate	                    CHAR(50)	Derivato
            'SPERR'            => '',//	Blocco centrale di registrazione	                            CHAR(1)	    Non ammesso
            'SPERM'            => '',//	Blocco acquisti assegnato a livello centrale	                CHAR(1)	    Non ammesso
            'AKONT'            => '',//	Conto di riconciliazione nella contabilità generale	            CHAR(10)	Non ammesso
            'EXTENSION1'       => '',//	Ampliamento (solo per conversione dati) (per es. linea dati)	CHAR(40)	Non ammesso
            'EXTENSION2'       => '',//	Ampliamento (solo per conversione dati) (per es. telebox)	    CHAR(40)	Non ammesso
            'TEL_NUMBER'       => '',//	Primo numero di telefono: prefisso + numero	CHAR(30)
            'TEL_EXTENS'       => '',//	Primo numero di telefono: interno	                            CHAR(10)
            'MOB_NUMBER'       => '',//	Primo n. cellulare: prefisso + numero abbonato	                CHAR(30)
            'FAX_NUMBER'       => '',//	Primo numero fax: prefisso + numero	        CHAR(30)
            'FAX_EXTENS'       => '',//	Primo numero fax: numero interno	                            CHAR(10)
            'SMTP_ADDR'        => '',//	Indirizzo e-mail	                                            CHAR(241)	Non ammesso
            'ADRNR'            => '',//	Indirizzo	                                                    CHAR(10)	Non ammesso
            'ZZ_PEC'           => '',//	Flag PEC e-mail	                                                CHAR(1)	    Non ammesso
            //'ZZ_CAT_EC_ORG'    => '',//	Categoria economica originale	                                NUMC(3)	    Non ammesso
        ];

        $iTabEmail = [];

        if ($soggetto->getEmail() !== null) {
            $iTabEmail[] = [
                'SMTP_ADDR' => $soggetto->getEmail(), // Indirizzo e-mail CHAR(241)
                'ZZ_PEC'    => 'N'
            ];
        }

        if ($soggetto->getEmailPec() !== null) {
            $iTabEmail[] = [
                'SMTP_ADDR' => $soggetto->getEmailPec(), // Indirizzo e-mail CHAR(241)
                'ZZ_PEC'    => 'S'
            ];
        }

        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_BENEFICIARI.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        return $this->client->Z_WS_MOD_BENEFICIARI(['I_BEN' => $iben, 'I_TAB_EMAIL' => $iTabEmail]);
    }

    /**
     * @param Persona $persona
     * @param string $env
     * @return stdClass
     */
    public function creaPersonaFisica(Persona $persona, $env = 'Prod')
    {
        // Il WS non accetta Forlì ma bensì Forli'
        $comuniDaSanare = [
            "forlì" => "Forli'",
            "roma capitale" => "Roma",
        ];

        $comune = $persona->getLuogoResidenza()->getComune()->getDenominazione();
        if (array_key_exists(mb_strtolower($persona->getLuogoResidenza()->getComune()->getDenominazione()), $comuniDaSanare)) {
            $comune = $comuniDaSanare[mb_strtolower($persona->getLuogoResidenza()->getComune()->getDenominazione())];
        }

        $via = $persona->getLuogoResidenza()->getVia() . ' ' . $persona->getLuogoResidenza()->getNumeroCivico();
        $cap = $persona->getLuogoResidenza()->getCap();
        $provincia = $persona->getLuogoResidenza()->getProvincia()->getSiglaAutomobilistica();

        $iben = [
            'BUKRS'            => 'RER',//	Società	                                                        CHAR(4)	    Obbligatorio
            'EKORG'            => '',//	Organizzazione acquisti	                                            CHAR(4)	    Non ammesso
            'KTOKK'            => '',//	Gruppo conti fornitore	                                            CHAR(4)	    Non ammesso
            'ZZ_CAT_EC'        => $persona->zzCatEc,//	Categoria economica	                                NUMC(3)	    Quelle previste al punto 4
            'ZZ_GR_BEN'        => '',//	Gruppo beneficiari	                                                CHAR(1)	    Non ammesso
            'LIFNR'            => '',//	Numero beneficiario	                                                CHAR(10)
            'RAGIONE_SOCIALE'  => mb_strtoupper(mb_substr($persona->getCognome() . ' ' . $persona->getNome(), 0, 20)),// Ragione sociale	                                                CHAR(140)	Obbligatorio
            'ZZ_RAG_SOC_BREVE' => mb_strtoupper(mb_substr($persona->getCognome() . ' ' . $persona->getNome(), 0, 20)),// Ragione sociale breve	                                        CHAR(20)
            'STCD1'            => $persona->getCodiceFiscale(),//	Codice Fiscale                          CHAR(16)	Obbligatorio
            'STCD2'            => '', //	Partita IVA 	                                                CHAR(11)
            'STREET'           => mb_strtoupper($via), //	Via	                                            CHAR(60)
            'HOUSE_NUM1'       => '', //	Numero civico	                                                CHAR(10)	Non gestito
            'HOUSE_NUM2'       => '', //	Precisazione relativa al numero civico	                        CHAR(10)	Non gestito
            'CITY1'            => mb_strtoupper($comune), //	Località	                                CHAR(40)
            'CITY2'            => '', //	Frazione	                                                    CHAR(40)	Non gestito
            'POST_CODE1'       => $cap, //	Codice di avviamento postale della città	                    CHAR(10)
            'COUNTRY'          => '', //	Codice paese     	                                            CHAR(3)	    IT per l’Italia
            'REGION'           => mb_strtoupper($provincia), //	Regione (stato federale, stato federato, provincia, contea)	    CHAR(3)
            'ALTKN'            => '', //	N. record anagrafico precedente	                                CHAR(10)	Non ammesso
            'KONZS'            => '', //	Chiave gruppo	                                                CHAR(10)	Non ammesso
            'ZZ_NUM_LOC_SPESE' => '', //	Codice localizzazione spese	                                    CHAR(6)	    Non ammesso
            'ZZ_NUM_LOC_OPERE' => '', //	Codice localizzazione opere	                                    CHAR(6)
            'ZZ_COD_CAM_COMM'  => '', //	Codice di iscrizione alla camera di commercio	                CHAR(8)
            'ZZ_NUM_ALBO'      => '', //	Numero albo	                                                    CHAR(8)
            'ZZ_TP_ALBO'       => '', //	Tipo albo	                                                    CHAR(3)
            'ZZ_FLAG_DIP'      => '', //	Flag dipendente	                                                CHAR(1)
            'ZZ_FLAG_INCASSO'  => '', //	Flag incassa direttamente	                                    CHAR(1)	    Non ammesso
            'ZZ_COD_DAT_LAV'   => '', //	Codice datore di lavoro	                                        CHAR(10)	Non ammesso
            'ZZ_RAG_SOC_ENTE'  => '', //	Ragione sociale ente	                                        CHAR(60)	Non ammesso
            'ZZ_IND_ENTE'      => '', //	Indirizzo ente	                                                CHAR(60)	Non ammesso
            'ZZ_NAME_FIRST'    => '', //	Nome	                                                        CHAR(40)	Se si indica occorre inserire anche COGNOME/ DATA DI NASCITA/ SESSO
            'ZZ_NAME_LAST'     => '', //	Cognome	                                                        CHAR(40)	Se si indica occorre inserire anche NOME/ DATA DI NASCITA/ SESSO
            //'GBDAT'            => '', //	Data di nascita della persona soggetta a rit. d'acconto	        CHAR(8)	    Se si indica occorre inserire anche NOME/ COGNOME/ SESSO
            'SEXKZ'            => '', //	Chiave sesso utenti con obbligo di rit. d'acconto	            CHAR(1)	    1 = Maschio 2 = Femmina Se si indica occorre inserire anche NOME/ COGNOME/ DATA DI NASCITA
            'ZZ_FISC_COM'      => '', //	Codice Fiscale Comune	                                        CHAR(4)	    Non ammesso
            'ZZ_ISTAT_COMUNE'  => '', //	Codice ISTAT	                                                CHAR(6)	    Derivato
            'ZZ_ISTAT_PR'      => '', //	Codice ISTAT Provincia	                                        CHAR(3)	    Non ammesso
            'ZZ_FISC_PAESE'    => '', //	Codice Fiscale Paese	                                        CHAR(4)	    Non ammesso
            'ZZ_ISTAT_PSEEX'   => '', //	Codice stato estero Agenzia Entrate	                            CHAR(3)	    Derivato
            'ZZ_STRANIERO'     => '', //	Flag straniero	                                                CHAR(1)	    Non ammesso
            'DESCR_BUKRS'      => '', //	Definizione della società o della ditta	                        CHAR(25)	Derivato
            'DESCR_PAESE'      => '', //	Nome paese	                                                    CHAR(15)	Derivato
            'DESCR_PROVINCIA'  => '', //	Denominazione	                                                CHAR(20)	Derivato
            'DESCR_NUOVO_BEN'  => '', //	Nome 1	                                                        CHAR(35)	Derivato
            'DESCR_LOC_S'      => '', //	Descrizione	                                                    CHAR(120)	Derivato
            'DESCR_LOC_O'      => '', //	Descrizione	                                                    CHAR(120)	Derivato
            'DESCR_TP_ALBO'    => '', //	Descrizione	                                                    CHAR(50)	Derivato
            'DESCR_DAT_LAV'    => '', //	Nome 1	                                                        CHAR(35)	Derivato
            'DESCR_COMUNE_N'   => '', //	Descrizione Comune	                                            CHAR(40)	Derivato
            'DESCR_PROV_N'     => '', //	Denominazione	                                                CHAR(20)	Derivato
            'DESCR_PAESE_N'    => '', //	Descrizione Comune	                                            CHAR(40)	Derivato
            'DESCR_COD_770'    => '', //	Descrizione stato estero Agenzia Entrate	                    CHAR(50)	Derivato
            'SPERR'            => '', //	Blocco centrale di registrazione	                            CHAR(1)	    Non ammesso
            'SPERM'            => '', //	Blocco acquisti assegnato a livello centrale	                CHAR(1)	    Non ammesso
            'AKONT'            => '', //	Conto di riconciliazione nella contabilità generale	            CHAR(10)	Non ammesso
            'EXTENSION1'       => '', //	Ampliamento (solo per conversione dati) (per es. linea dati)	CHAR(40)	Non ammesso
            'EXTENSION2'       => '', //	Ampliamento (solo per conversione dati) (per es. telebox)	    CHAR(40)	Non ammesso
            'TEL_NUMBER'       => '', //	Primo numero di telefono: prefisso + numero                     CHAR(30)
            'TEL_EXTENS'       => '', //	Primo numero di telefono: interno	                            CHAR(10)
            'MOB_NUMBER'       => '', //	Primo n. cellulare: prefisso + numero abbonato	                CHAR(30)
            'FAX_NUMBER'       => '', //	Primo numero fax: prefisso + numero                             CHAR(30)
            'FAX_EXTENS'       => '', //	Primo numero fax: numero interno	                            CHAR(10)
            'SMTP_ADDR'        => '', //	Indirizzo e-mail	                                            CHAR(241)	Non ammesso
            'ADRNR'            => '', //	Indirizzo	                                                    CHAR(10)	Non ammesso
            'ZZ_PEC'           => '', //	Flag PEC e-mail	                                                CHAR(1)	    Non ammesso
            //'ZZ_CAT_EC_ORG'    => '', //	Categoria economica originale	                                NUMC(3)	    Non ammesso
        ];

        $iTabEmail = [];
        if ($persona->getEmailPrincipale() !== null) {
            $iTabEmail[] = [
                'SMTP_ADDR' => $persona->getEmailPrincipale(), // Indirizzo e-mail CHAR(241)
                'ZZ_PEC'    => 'N'
            ];
        }

        if(property_exists($persona, 'emailPec') && $persona->emailPec !== null) {
            $iTabEmail[] = [
                'SMTP_ADDR' => $persona->emailPec, // Indirizzo e-mail CHAR(241)
                'ZZ_PEC'    => 'S'
            ];
        }

        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_BENEFICIARI.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        return $this->client->Z_WS_CREA_BENEFICIARI(['I_BEN' => $iben, 'I_TAB_EMAIL' => $iTabEmail]);
    }

    /**
     * @param        $impegno
     * @param string $env
     * @param null   $posizione
     *
     * @return bool
     */
    public function fattureDaImpegno($impegno, $posizione = null, $env = 'Prod')
    {
        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_IMPEGNI.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        if($posizione !== null) {
            return $this->client->Z_WS_FATTURE_DA_IMPEGNO(['I_BUKRS' => 'RER', 'I_IMPEGNO' => $impegno, 'I_POSIZIONE' => $posizione]);
        }

        return $this->client->Z_WS_FATTURE_DA_IMPEGNO(['I_BUKRS' => 'RER', 'I_IMPEGNO' => $impegno]);
    }

    /**
     * @param        $impegno
     * @param string $env
     *
     * @return bool
     */
    public function totalizzatoriImpegno($impegno, $env = 'Prod')
    {
        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_IMPEGNI.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        return $this->client->Z_WS_TOTALIZZATORI_IMPEGNO(['I_BUKRS' => 'RER', 'I_IMPEGNO' => $impegno]);
    }

    /**
     * @param array  $data
     * @param string $env
     *
     * @return bool
     */
    public function creaPartita(array $data, $env = 'Prod')
    {
        $iFiRp = [
            'BUKRS'          => 'RER', // Società	                                                    CHAR(4)	        X	Fisso RER
            'BLART'          => '', // Tipo di documento	                                        CHAR(4) 	    	Non indicare
            'BUDAT'          => $data['budat'], // Data di registrazione nel documento	                        DATA	        X	Formato AAAA-MM-GG
            'BLDAT'          => $data['bldat'], // Data documento	                                            DATA	        X	Formato AAAA-MM-GG
            'ZLSCH'          => '4', // Tipo partita	                                                CHAR(1) 	    	Fisso 4
            'XBLNR'          => $data['xblnr'], // Riferimento fattura	                                        CHAR(16)	    X
            'ZZ_NUM_LOC'     => $data['zz_num_loc'], // Codice localizzazione opere	                                CHAR(6)	        X	Codice ISTAT comune localizzazione opere
            'LIFNR'          => $data['lifnr'], // Numero beneficiario	                                        CHAR(10)	    X
            'KBLNR'          => $data['kblnr'], // Numero impegno	                                            CHAR(10)	    X
            'KBLPOS'         => $data['kblpos'], // Posizione impegno	                                        NUMC(3)	        X
            'HKONT'          => '', // Conto Co.Ge. contabilità generale	                        CHAR(10)		    Non indicare
            //'CG_SIOPE'       => '', // Codice gestionale SIOPE	                                    NUMC(4)		        Non indicare
            'KOSTL'          => $data['kostl'], // Centro di costo	                                            CHAR(10)	        Obbligat. se previsto dal conto Co. Ge.
            'WRBTR'          => $data['wrbtr'], // Importo lordo	                                            CURRENCY (13,2)	X
            //'IMPONIBILE'     => '', // Importo imponibile	                                        CURRENCY (13,2)		Non indicare
            //'IVA'            => '', // Importo IVA	                                                CURRENCY (13,2)		Non indicare
            //'ESENTI'         => '', // Importo spese esenti	                                        CURRENCY (13,2)		Non indicare
            //'SOGGETTE'       => '', // Importo spese soggette	                                    CURRENC Y(13,2)		Non indicare
            //'QUOTA_ENTE'     => '', // Quota ente	                                                CURRENCY (13,2)		Non indicare
            'DESCR_LIFNR'    => '', // Descrizione fornitore	                                    CHAR(132)		    Non indicare
            'DESCR_KBLNR'    => '', // Testo testata impegno	                                    CHAR(50)		    Non indicare
            'DESCR_HKONT'    => '', // Descrizione conto Co.Ge.	                                    CHAR(50)		    Non indicare
            'DESCR_CG'       => '', // Descrizione codice gestionale SIOPE	                        CHAR(255)		    Non indicare
            'DESCR_KOSTL'    => '', // Descrizione	                                                CHAR(40)		    Non indicare
            'DESCR_FIPEX'    => '', // Descrizione	                                                CHAR(50)		    Non indicare
            'KOKRS'          => '', // Controlling area	                                            CHAR(4)		        Non indicare
            'FICTR'          => '', // Centro di responsabilità	                                    CHAR(16)		    Non indicare
            'FIKRS_IMP'      => '', // Area finanziaria impegno	                                    CHAR(4)		        Non indicare
            //'GJAHR_IMP'      => '', // Anno di bilancio impegno	                                    NUMC(4)		        Non indicare
            'FIPEX'          => '', // Capitolo	                                                    CHAR(24)		    Non indicare
            'FIPOS'          => '', // Capitolo (codice interno)	                                CHAR(14)		    Non indicare
            'CONTO_VDC'      => '', // Conto è voce di costo?	                                    CHAR(1)		        Non indicare
            'XBLNR_10'       => '', // Numero di riferimento	                                    CHAR(10)		    Non indicare
            //'XBLNR_01'       => '', // Numero progressivo	                                        NUMC(1)		        Non indicare
            'DESCR_KBLPOS'   => '', // Testo posizione	                                            CHAR(50)		    Non indicare
            'AUFNR'          => '', // Numero ordine	                                            CHAR(12)		    Non indicare
            'KTEXT'          => '', // Testo breve	                                                CHAR(40)		    Non indicare
            'ZZCONTR_IMP'    => '', // Tipo gestione impegno	                                    CHAR(1)		        Non indicare
            'PTEXT'          => '', // Testo posizione	                                            CHAR(50)	    	Non indicare
            'ZZCUP'          => $data['zzcup'], // Codice unico progetto	                                    CHAR(15)		    Se non indicato viene recuperato da impegno
            'ZZCIG'          => $data['zzcig'], // Codice identificativo gara	                                CHAR(10)	    	Se non indicato viene recuperato da impegno
            //'GJAHR_CONTO_FD' => '', // Esercizio	                                                NUMC(4)		        Non indicare
            'CONTO_FD'       => '', // Conto	                                                    CHAR(9)	        	Non indicare
            'PROTOCOLLO_FD'  => '', // Protocollo	                                                CHAR(14)    		Non indicare
            'GEBER'          => '', // Fondi	                                                    CHAR(10)		    Non indicare
            'DESCR_GEBER'    => '', // Descrizione	                                                CHAR(40)		    Non indicare
            'ZFBDT'          => $data['zfbdt'], // Data base per calcolo delle scadenze	                        DATA		        Formato AAAA-MM-GG. Se non indicato viene presa la data registrazione (BUDAT)
            'ZTERM'          => '', // Chiave condizioni di pagamento	                            CHAR(4)		        Non indicare
            'GG_SCAD'        => $data['gg_scad'], // Giorni di scadenza	                                        DEC(3)		        Giorni dalla data base scadenza (ZFBDT) dopo i quali scadono i termini di pagamento
            //'ZDATA_SCAD'     => '', // Data scadenza	                                            DATS(8)		        Non indicare (calcolato come ZFBDT + GG_SCAD)
            'DESCR_PAG'      => '', // Spiegazione propria delle cond. di pagamento	                CHAR(50)		    Non indicare
            'LOTKZ_RIF'      => '', // Numero di raggruppamento per ordini	                        CHAR(10)		    Non indicare
            'REBZG'          => '', // Numero documento della fattura di cui fa parte l'operazione	CHAR(10)		    Non indicare
            //'REBZJ'          => '', // Esercizio della relativa fattura (per accredito)	            NUMC(4)		        Non indicare
            //'REBZZ'          => '', // Posizione della relativa fattura	                            NUMC(3)		        Non indicare
            'XUMVZ'          => '', // Codice: registrazione con segno +/- inverso	                CHAR(1)		        Non indicare
            //'IMPONIBILE_IVA' => '', // Importo imponibile IVA	                                    CURR(13)		    Non indicare
            //'IVA_SP'         => '', // Importo IVA per split payment	                            CURR(13)		    Non indicare
            'TESTO_IMPORTO'  => '', // Descrizione di 132 caratteri	                                CHAR(132)		    Non indicare
            'ZZLIVELLO1'     => '', // Livello 1	                                                CHAR(1)		        Non indicare
            'ZZLIVELLO2'     => '', // Livello 2	                                                CHAR(2)		        Non indicare
            'ZZLIVELLO3'     => '', // Livello 3 	                                                CHAR(2)		        Non indicare
            'ZZLIVELLO4'     => '', // Livello 4	                                                CHAR(2)		        Non indicare
            'ZZLIVELLO5'     => '', // Livello 5	                                                CHAR(3)		        Non indicare
            'DESCR_L5'       => '', // Descrizione	                                                CHAR(255)	        Non indicare
            'BLART_IMP'      => '', // Tipo impegno	                                                CHAR(2)		        Non indicare
        ];

        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_PARTITE.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        try {
            return $this->client->Z_WS_CREA_PARTITE(['I_FI_RP' => $iFiRp, 'I_NOTE' => $data['i_note']]);
        } catch (SoapFault $e) {
            $result = new stdClass();
            $result->E_RC = -1;
            $result->E_MESSAGES = $e->getMessage();
            return $result;
        }
    }

    /**
     * @param        $iban
     * @param string $env
     *
     * @return bool
     */
    public function checkIban($iban, $env = 'Prod')
    {
        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_QUIETANZE.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        return $this->client->Z_WS_CHECK_IBAN(['I_BUKRS' => 'RER', 'I_IBAN' => $iban]);
    }

    /**
     * @param        $iban
     * @param        $codiceFornitore
     * @param        $tipoQuietanza
     * @param null   $codiceSwift
     * @param null   $nomeBancaEstera
     * @param string $env
     *
     * @return bool
     */
    public function creaQuietanza($iban, $codiceFornitore, $tipoQuietanza, $codiceSwift = null, $nomeBancaEstera = null, $env = 'Prod')
    {
        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/Z_WS_QUIETANZE.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        if ($codiceSwift !== null && $nomeBancaEstera !== null) {
            return $this->client->Z_WS_CREA_QUIETANZA(['I_BUKRS' => 'RER', 'I_IBAN' => $iban, 'I_LIFNR' => $codiceFornitore, 'I_TP_QUIET' => $tipoQuietanza, 'I_SWIFT' => $codiceSwift, 'I_NOME_BANCA_ESTERA' => $nomeBancaEstera]);
        }

        if ($codiceSwift !== null) {
            return $this->client->Z_WS_CREA_QUIETANZA(['I_BUKRS' => 'RER', 'I_IBAN' => $iban, 'I_LIFNR' => $codiceFornitore, 'I_TP_QUIET' => $tipoQuietanza, 'I_SWIFT' => $codiceSwift]);
        }

        if($nomeBancaEstera !== null) {
            return $this->client->Z_WS_CREA_QUIETANZA(['I_BUKRS' => 'RER', 'I_IBAN' => $iban, 'I_LIFNR' => $codiceFornitore, 'I_TP_QUIET' => $tipoQuietanza, 'I_NOME_BANCA_ESTERA' => $nomeBancaEstera]);
        }

        return $this->client->Z_WS_CREA_QUIETANZA(['I_BUKRS' => 'RER', 'I_IBAN' => $iban, 'I_LIFNR' => $codiceFornitore, 'I_TP_QUIET' => $tipoQuietanza]);
    }

    /**
     * @param null   $cup
     * @param null   $cig
     * @param string $env
     *
     * @return bool
     */
    public function datiCapitoliImpegniDaCupEoCig($cup = null, $cig = null, $env = 'Prod')
    {
        if($cup === null && $cig === null) {
            return false;
        }

        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/ZWS_FM_DATI_CIG_CUP.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        if ($cup !== null && $cig !== null) {
            return $this->client->Z_WS_DATI_CAP_IMP_DA_CUP_CIG(['I_BUKRS' => 'RER', 'I_CIG' => $cig, 'I_CUP' => $cup]);
        }

        if($cup !== null) {
            return $this->client->Z_WS_DATI_CAP_IMP_DA_CUP_CIG(['I_BUKRS' => 'RER','I_CUP' => $cup]);
        }

        return $this->client->Z_WS_DATI_CAP_IMP_DA_CUP_CIG(['I_BUKRS' => 'RER', 'I_CIG' => $cig]);
    }

    /**
     * @param null   $cup
     * @param null   $cig
     * @param string $env
     *
     * @return bool
     */
    public function datiFattureDaCupEoCig($cup = null, $cig = null, $env = 'Prod')
    {
        if($cup === null && $cig === null) {
            return false;
        }

        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/ZWS_FM_DATI_CIG_CUP.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        if ($cup !== null && $cig !== null) {
            return $this->client->Z_WS_DATI_FATTURE_DA_CUP_CIG(['I_BUKRS' => 'RER', 'I_CIG' => $cig, 'I_CUP' => $cup]);
        }

        if($cup !== null) {
            return $this->client->Z_WS_DATI_FATTURE_DA_CUP_CIG(['I_BUKRS' => 'RER','I_CUP' => $cup]);
        }

        return $this->client->Z_WS_DATI_FATTURE_DA_CUP_CIG(['I_BUKRS' => 'RER', 'I_CIG' => $cig]);
    }

    /**
     * @param null   $cup
     * @param null   $cig
     * @param string $env
     *
     * @return bool
     */
    public function datiMandatiDaCupEoCig($cup = null, $cig = null, $env = 'Prod')
    {
        if($cup === null && $cig === null) {
            return false;
        }

        try {
            $this->inizializzaClient(__DIR__ . '/SapWsdl/' . $env . '/ZWS_FM_DATI_CIG_CUP.wsdl', $env);
        } catch (SoapFault $e) {
            return false;
        }

        if ($cup !== null && $cig !== null) {
            return $this->client->Z_WS_DATI_MANDATI_DA_CUP_CIG(['I_BUKRS' => 'RER', 'I_CIG' => $cig, 'I_CUP' => $cup]);
        }

        if($cup !== null) {
            return $this->client->Z_WS_DATI_MANDATI_DA_CUP_CIG(['I_BUKRS' => 'RER','I_CUP' => $cup]);
        }

        return $this->client->Z_WS_DATI_MANDATI_DA_CUP_CIG(['I_BUKRS' => 'RER', 'I_CIG' => $cig]);
    }

    /**
     * @param $codiceIstat
     *
     * @return mixed
     */
    private function getCodiceIso3166($codiceIstat)
    {
        $iso3166 = [
            '100' => 'IT',
            '101' => 'IT',
            '201' => 'ALB',
            '202' => 'AND',
            '203' => 'AUT',
            '206' => 'BEL',
            '209' => 'BGR',
            '212' => 'DNK',
            '214' => 'FIN',
            '215' => 'FRA',
            '216' => 'DEU',
            '219' => 'GBR',
            '220' => 'GRC',
            '221' => 'IRL',
            '223' => 'ISL',
            '225' => 'LIE',
            '226' => 'LUX',
            '227' => 'MLT',
            '229' => 'MCO',
            '231' => 'NOR',
            '232' => 'NLD',
            '233' => 'POL',
            '234' => 'PRT',
            '235' => 'ROU',
            '236' => 'SMR',
            '239' => 'ESP',
            '240' => 'SWE',
            '241' => 'CHE',
            '243' => 'UKR',
            '244' => 'HUN',
            '245' => 'RUS',
            '246' => 'VAT',
            '247' => 'EST',
            '248' => 'LVA',
            '249' => 'LTU',
            '250' => 'HRV',
            '251' => 'SVN',
            '252' => 'BIH',
            '253' => 'MKD',
            '254' => 'MDA',
            '255' => 'SVK',
            '256' => 'BLR',
            '257' => 'CZE',
            '270' => 'MNE',
            '271' => 'SRB',
            '272' => 'KOS',
            '301' => 'AFG',
            '302' => 'SAU',
            '304' => 'BHR',
            '305' => 'BGD',
            '306' => 'BTN',
            '307' => 'MMR',
            '309' => 'BRN',
            '310' => 'KHM',
            '311' => 'LKA',
            '314' => 'CHN',
            '315' => 'CYP',
            '319' => 'PRK',
            '320' => 'KOR',
            '322' => 'ARE',
            '323' => 'PHL',
            '324' => 'PSE',
            '326' => 'JPN',
            '327' => 'JOR',
            '330' => 'IND',
            '331' => 'IDN',
            '332' => 'IRN',
            '333' => 'IRQ',
            '334' => 'ISR',
            '335' => 'KWT',
            '336' => 'LAO',
            '337' => 'LBN',
            '338' => 'TLS',
            '339' => 'MDV',
            '340' => 'MYS',
            '341' => 'MNG',
            '342' => 'NPL',
            '343' => 'OMN',
            '344' => 'PAK',
            '345' => 'QAT',
            '346' => 'SGP',
            '348' => 'SYR',
            '349' => 'THA',
            '351' => 'TUR',
            '353' => 'VNM',
            '354' => 'YEM',
            '356' => 'KAZ',
            '357' => 'UZB',
            '358' => 'ARM',
            '359' => 'AZE',
            '360' => 'GEO',
            '361' => 'KGZ',
            '362' => 'TJK',
            '363' => 'TWN',
            '364' => 'TKM',
            '401' => 'DZA',
            '402' => 'AGO',
            '404' => 'CIV',
            '406' => 'BEN',
            '408' => 'BWA',
            '409' => 'BFA',
            '410' => 'BDI',
            '411' => 'CMR',
            '413' => 'CPV',
            '414' => 'CAF',
            '415' => 'TCD',
            '417' => 'COM',
            '418' => 'COG',
            '419' => 'EGY',
            '420' => 'ETH',
            '421' => 'GAB',
            '422' => 'GMB',
            '423' => 'GHA',
            '424' => 'DJI',
            '425' => 'GIN',
            '426' => 'GNB',
            '427' => 'GNQ',
            '428' => 'KEN',
            '429' => 'LSO',
            '430' => 'LBR',
            '431' => 'LBY',
            '432' => 'MDG',
            '434' => 'MWI',
            '435' => 'MLI',
            '436' => 'MAR',
            '437' => 'MRT',
            '438' => 'MUS',
            '440' => 'MOZ',
            '441' => 'NAM',
            '442' => 'NER',
            '443' => 'NGA',
            '446' => 'RWA',
            '448' => 'STP',
            '449' => 'SYC',
            '450' => 'SEN',
            '451' => 'SLE',
            '453' => 'SOM',
            '454' => 'ZAF',
            '455' => 'SDN',
            '456' => 'SWZ',
            '457' => 'TZA',
            '458' => 'TGO',
            '460' => 'TUN',
            '461' => 'UGA',
            '463' => 'COD',
            '464' => 'ZMB',
            '465' => 'ZWE',
            '466' => 'ERI',
            '467' => 'SSD',
            '503' => 'ATG',
            '505' => 'BHS',
            '506' => 'BRB',
            '507' => 'BLZ',
            '509' => 'CAN',
            '513' => 'CRI',
            '514' => 'CUB',
            '515' => 'DMA',
            '516' => 'DOM',
            '517' => 'SLV',
            '518' => 'JAM',
            '519' => 'GRD',
            '523' => 'GTM',
            '524' => 'HTI',
            '525' => 'HND',
            '527' => 'MEX',
            '529' => 'NIC',
            '530' => 'PAN',
            '532' => 'LCA',
            '533' => 'VCT',
            '534' => 'KNA',
            '536' => 'USA',
            '602' => 'ARG',
            '604' => 'BOL',
            '605' => 'BRA',
            '606' => 'CHL',
            '608' => 'COL',
            '609' => 'ECU',
            '612' => 'GUY',
            '614' => 'PRY',
            '615' => 'PER',
            '616' => 'SUR',
            '617' => 'TTO',
            '618' => 'URY',
            '619' => 'VEN',
            '701' => 'AUS',
            '703' => 'FJI',
            '708' => 'KIR',
            '712' => 'MHL',
            '713' => 'FSM',
            '715' => 'NRU',
            '719' => 'NZL',
            '720' => 'PLW',
            '721' => 'PNG',
            '725' => 'SLB',
            '727' => 'WSM',
            '730' => 'TON',
            '731' => 'TUV',
            '732' => 'VUT',
            '902' => 'NCL',
            '904' => 'MAF',
            '905' => 'ESH',
            '906' => 'BLM',
            '908' => 'BMU',
            '909' => 'COK',
            '910' => 'GIB',
            '911' => 'CYM',
            '917' => 'AIA',
            '920' => 'PYF',
            '924' => 'FRO',
            '925' => 'JEY',
            '926' => 'ABW',
            '928' => 'SXM',
            '934' => 'GRL',
            '939' => 'n.d',
            '940' => 'GGY',
            '958' => 'FLK',
            '959' => 'IMN',
            '964' => 'MSR',
            '966' => 'CUW',
            '972' => 'PCN',
            '980' => 'SPM',
            '983' => 'SHN',
            '988' => 'ATF',
            '992' => 'TCA',
            '994' => 'VGB',
            '997' => 'WLF',
        ];

        return $iso3166[$codiceIstat];
    }
}