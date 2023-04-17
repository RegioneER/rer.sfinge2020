<?php

namespace SfingeBundle\Service\Importazione;

use BaseBundle\Service\BaseService;
use BaseBundle\Entity\Indirizzo;
use AnagraficheBundle\Entity\Persona;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
//use RichiesteBundle\Entity\OggettoRichiesta;
use RichiesteBundle\Entity\Bando7\OggettoImportazione380;
use RichiesteBundle\Entity\Bando8\OggettoImportazione373;
use RichiesteBundle\Entity\Bando32\OggettoImportazione383;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\VocePianoCosto;
use SoggettoBundle\Entity\IncaricoPersona;
use RichiesteBundle\Entity\Proponente;
use SfingeBundle\Entity\Utente;

abstract class AGestoreImportazioneProcedura extends BaseService implements IGestoreImportazioneProcedura {

	public function getQueryRichieste() {
		return "SELECT * FROM myfesr_aziende.pre_richieste_finanziamento WHERE _kf_bando = :_kf_bando and statoRichiesta = 'F'";
	}

	public function importaRichieste() {


		$em = $this->getEm();
		$em->getConnection()->getConfiguration()->setSQLLogger(null);

		// Both of these return the "customer" entity manager
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$customerEm->getConnection()->getConfiguration()->setSQLLogger(null);
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare($this->getQueryRichieste());
		$statement->bindValue('_kf_bando', $this->getIdProcedura2013());
		$statement->execute();
		$pre_richieste_finanziamento = $statement->fetchAll();

		foreach ($pre_richieste_finanziamento as $key => $pre_richiesta) {
			set_time_limit(30);

			$richiesta_importata = $em->getRepository("RichiesteBundle\Entity\Richiesta")->findBy(array("id_sfinge_2013" => $pre_richiesta['_kp_richiesta_finanziamento']));
			if (count($richiesta_importata) > 0) {
				continue;
			}

			//recupero il progetto da myfesr
			$progetto = $this->dammiProgetto($pre_richiesta);

			//recupero l'azienda mandataria da myfesr
			$azienda_mandataria = $this->dammiAziendaMandataria($progetto);

			//creo l'utente principale
			$persona = $this->creaPersona($em, $azienda_mandataria);

			//creo il soggetto corrispondente all'azienda mandataria
			$soggettoMandatario = $this->creaAzienda($em, $azienda_mandataria);

			//assegno l'incarico di UTENTE PRINCIPALE per l'azienda mandataria
			$incaricoUP = $this->creaIncarico($em, $persona, $soggettoMandatario);

			//assegno l'incarico di LR per l'azienda mandataria
			$incaricoLR = $this->creaIncaricoLR($em, $persona, $soggettoMandatario);

			//creo la richiesta
			$richiesta = $this->creaRichiesta($em, $azienda_mandataria, $pre_richiesta);

			//importo i documenti associati alla richiesta
			$this->importaDocumentiPresentazione($richiesta, $pre_richiesta);

			//recupero le altre eventuali aziende proponenti
			$altre_aziende_proponenti = $this->dammiAltreAziendeProponenti($progetto);

			//creo i proponenti (associo richiesta con soggetti)
			$this->creaProponenti($em, $progetto, $richiesta, $pre_richiesta, $soggettoMandatario, $altre_aziende_proponenti, $this->isMultiPianoCosto(), $azienda_mandataria); // 0 = PIANO COSTO SINGOLO | 1 = MULTI PIANO COSTO
			//creo la richiesta di protocollo
			$this->creaRichiestaProtocollo($em, $richiesta, $pre_richiesta);

			//creo l'Oggetto_Richiesta
			$this->creaOggettoRichiesta($em, $richiesta, $pre_richiesta);

			//creo le sedi

			$this->creaIstruttoria($richiesta, $pre_richiesta);

			$this->creaAtc($richiesta, $pre_richiesta);

			try {
				$em->flush();
			} catch (\Exception $e) {
				echo 'errore: ' . $e->getMessage();
			}
		}

		echo '<br>';
		echo 'fine';
		exit;
	}

	public function dammiProgetto($pre_richiesta) {
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("
                        SELECT *
                        FROM myfesr_aziende.pre_progetti p
                        LEFT JOIN myfesr_aziende.pre_sp_asse6 a on (p._kp_progetto = a._kf_progetto)
                        WHERE _kf_richiesta_finanziamento = :_kf_richiesta_finanziamento");
		$statement->bindValue('_kf_richiesta_finanziamento', $pre_richiesta['_kp_richiesta_finanziamento']);
		$statement->execute();
		$progetto = $statement->fetch();
		return $progetto;
	}

	public function dammiAziendaMandataria($progetto) {

		// Both of these return the "customer" entity manager
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("
                      SELECT a.*, c.denominazione as 'comuneNascita', d.denominazione as 'comuneResidenza', e.denominazione as 'comuneSede', f.codice as 'codiceFormaGiuridica', g.codice as 'codiceDim'
                      FROM myfesr_aziende.pre_aziende a
                      LEFT JOIN myfesr.tab_comuni c on (a.leg_rap_comune_nascita = c._kp_comune)
                      LEFT JOIN myfesr.tab_comuni d on (a.leg_rap_comune_residenza = d._kp_comune)
                      LEFT JOIN myfesr.tab_comuni e on (a.sede_comune = e._kp_comune)
                      LEFT JOIN myfesr.tab_formegiuridiche f on (a._kf_formagiuridica = f._kp_formagiuridica)
                      LEFT JOIN myfesr.tab_dimensioni g on (a._kf_dimensione = g._kp_dimensione)
                      WHERE _kf_progetto = :_kf_progetto and mandataria = 1");
		$statement->bindValue('_kf_progetto', $progetto['_kp_progetto']);
		$statement->execute();
		$azienda = $statement->fetch();

		return $azienda;
	}

	//Restituisce tutte le aziende proponenti del progetto che NON sono mandatarie
	public function dammiAltreAziendeProponenti($progetto) {

		// Both of these return the "customer" entity manager
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("
                      SELECT a.*, c.denominazione as 'comuneNascita', d.denominazione as 'comuneResidenza', e.denominazione as 'comuneSede', f.codice as 'codiceFormaGiuridica', g.codice as 'codiceDim'
                      FROM myfesr_aziende.pre_aziende a
                      LEFT JOIN myfesr.tab_comuni c on (a.leg_rap_comune_nascita = c._kp_comune)
                      LEFT JOIN myfesr.tab_comuni d on (a.leg_rap_comune_residenza = d._kp_comune)
                      LEFT JOIN myfesr.tab_comuni e on (a.sede_comune = e._kp_comune)
                      LEFT JOIN myfesr.tab_formegiuridiche f on (a._kf_formagiuridica = f._kp_formagiuridica)
                      LEFT JOIN myfesr.tab_dimensioni g on (a._kf_dimensione = g._kp_dimensione)
                      WHERE _kf_progetto = :_kf_progetto and mandataria != 1");
		$statement->bindValue('_kf_progetto', $progetto['_kp_progetto']);
		$statement->execute();
		$proponenti = $statement->fetchAll();

		return $proponenti;
	}

	/* public function dammiAziende($progetto)
	  {
	  $em = $this->getDoctrine()->getManager();

	  // Both of these return the "customer" entity manager
	  $customerEm = $this->container->get('doctrine')->getManager('customer2');
	  $connection = $customerEm->getConnection();
	  $statement = $connection->prepare("SELECT * FROM myfesr_aziende.pre_aziende WHERE _kf_progetto = :_kf_progetto");
	  $statement->bindValue('_kf_progetto', $progetto['_kp_progetto']);
	  $statement->execute();
	  $aziende = $statement->fetchAll();
	  return $aziende;
	  } */

	//Restituisce l'eventuale altra sede dell'azienda associata al progetto (Unità Locale di pre_aziende), altrimenti FALSE
	public function dammiUnitaLocale($progetto) {

		$em = $this->getEm();

		// Both of these return the "customer" entity manager
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("
                      SELECT unitalocale_comune, sigla_provincia_unitalocale, unitalocale_indirizzo, unitalocale_numero, unitalocale_cap, unitalocale_telefono, unitalocale_fax,
                            unitalocale_email, unitalocale_frazione, unitalocale_macrosettore, unitalocale_ateco, unitalocale_ateco_secondaria, unitalocale_motivazione, unitalocale_attiva
                      FROM myfesr_aziende.pre_aziende a
                      WHERE _kf_progetto = :_kf_progetto");
		$statement->bindValue('_kf_progetto', $progetto['_kp_progetto']);
		$statement->execute();
		$sede = $statement->fetch();


		//IF (uguale a sede legale) return false ELSE return sede
	}

	public function creaPersona($em, $azienda_mandataria) {

		$persona = $em->getRepository('AnagraficheBundle\Entity\Persona')->findOneBy(array('codice_fiscale' => $azienda_mandataria['leg_rap_codicefiscale']));

		if (!$persona) {
			//Recupero informazioni di TEST --------------------
			$nazione = $em->getRepository('GeoBundle\Entity\GeoStato')->find(1);
			$comune = $em->getRepository('GeoBundle\Entity\GeoComune')->find(1);
			// ------------------------------------

			$comuneNascita = $em->getRepository('GeoBundle\Entity\GeoComune')->findOneByDenominazione($azienda_mandataria['comuneNascita']);
			if (!is_null($azienda_mandataria['comuneResidenza'])) {
				$comuneResidenza = $em->getRepository('GeoBundle\Entity\GeoComune')->findOneByDenominazione($azienda_mandataria['comuneResidenza']);
			} else {
				$comuneResidenza = $em->getRepository('GeoBundle\Entity\GeoComune')->findOneByDenominazione($azienda_mandataria['comuneNascita']);
			}

			$indirizzo = new Indirizzo();
			$indirizzo->setStato($nazione);
			$indirizzo->setComune($comune);
			$indirizzo->setVia('VIA DI PROVA');
			$indirizzo->setNumeroCivico('123456');
			$indirizzo->setCap('99999');
			$indirizzo->setLocalita('LOCALITA DI PROVA');


			//creo la persona
			$persona = new Persona();
			$persona->setNazionalita($nazione);
			$persona->setStatoNascita($nazione);
			$persona->setComune($comuneNascita);
			$persona->setLuogoResidenza($indirizzo);
			//CARTA_IDENTITA_ID?? (Riferimento al documento)
			$persona->setNome($azienda_mandataria['leg_rap_nome']);
			$persona->setCognome($azienda_mandataria['leg_rap_cognome']);
			$dataNascita = \is_string($azienda_mandataria['leg_rap_data_nascita']) ? new \DateTime($azienda_mandataria['leg_rap_data_nascita']) : $azienda_mandataria['leg_rap_data_nascita'];
			$persona->setDataNascita($dataNascita);
			$persona->setSesso("NA");
			$persona->setCodiceFiscale($azienda_mandataria['leg_rap_codicefiscale']);
			$persona->setProvinciaEstera("NULL");
			$persona->setComuneEstero("NULL");
			$persona->setTelefonoPrincipale(isset($azienda_mandataria['resp_tecnico_telefono']) ? $azienda_mandataria['resp_tecnico_telefono'] : "telefono_principale");
			$persona->setFaxPrincipale("NA");
			$persona->setEmailPrincipale(isset($azienda_mandataria['leg_rap_email']) ? $azienda_mandataria['leg_rap_email'] : "email_principale");
			$persona->setTelefonoSecondario("NA");
			$persona->setFaxSecondario("NA");
			$persona->setEmailSecondario("NA");
			$persona->setIdSfinge2013($azienda_mandataria['_kp_azienda']);

			$em->persist($persona);
		}

		$user = $em->getRepository('SfingeBundle\Entity\Utente')->findOneByUsername($persona->getCodiceFiscale());
		$userEmail = $em->getRepository('SfingeBundle\Entity\Utente')->findOneByEmail($persona->getEmailPrincipale());
		if (!$user) {
			$userManager = $this->container->get('fos_user.user_manager');
			//creo un nuovo utente
			$user = $userManager->createUser();
			$user->setUsername($persona->getCodiceFiscale());

			$factory = $this->container->get('security.encoder_factory');
			$encoder = $factory->getEncoder($user);
			$password = $encoder->encodePassword('password', $user->getSalt());
			$user->setPassword($password);

			$user->setEnabled(true);
			// se esiste un'utente con la stessa mail devo inserirne una provvisoria per il vincolo di unique
			if ($userEmail) {
				$user->setEmail('provvisoria' . time() . '@email');
			} else {
				$user->setEmail($persona->getEmailPrincipale());
			}

			$user->setRoles(array('ROLE_USER', 'ROLE_UTENTE'));

			$user->setCreatoDa($user->getUsername());
			$user->setCreatoIl(new \DateTime());

			if (is_null($persona->getUtente())) {
				$user->setPersona($persona);
			}
			$user->setDatiPersonaInseriti(true);
			$userManager->updateUser($user);
		}


		echo('Persona: ' . $persona->getNome() . ' ' . $persona->getCognome());

		return $persona;
	}

	public function creaAzienda($em, $azienda_mandataria) {
		//Effettuo una ricerca su tutti i Soggetti (Aziende, Comuni, Altri)
		$azienda = $em->getRepository('SoggettoBundle\Entity\Soggetto')->findOneBy(array('codice_fiscale' => $azienda_mandataria['codicefiscale']));

		if ($this->getIdProcedura2013() == 373) {
			$azienda = $em->getRepository('SoggettoBundle\Entity\Soggetto')->findOneBy(
					array('codice_fiscale' => $azienda_mandataria['codicefiscale'], 'acronimo_laboratorio' => $azienda_mandataria['acronimo_laboratorio']));
		}


		//Se il CF non è già presente a sistema, creo una nuova azienda
		if (!$azienda) {
			$comune = $em->getRepository('GeoBundle\Entity\GeoComune')->findOneByDenominazione($azienda_mandataria['comuneSede']);
			$formaGiuridica = $em->getRepository('SoggettoBundle\Entity\FormaGiuridica')->findOneByCodice($azienda_mandataria['codiceFormaGiuridica']);
			$ateco = $em->getRepository('SoggettoBundle\Entity\Ateco')->findOneByCodice($azienda_mandataria['sede_ateco']);
			if (isset($azienda_mandataria['codiceDim'])) {
				$dimensione = $em->getRepository('SoggettoBundle\Entity\DimensioneImpresa')->find($azienda_mandataria['codiceDim']);
			}


			//Recupero informazioni di TEST ---------
			$nazione = $em->getRepository('GeoBundle\Entity\GeoStato')->find(1);
			// --------------------------------------

			$classe_soggetto = $this->determinaClasseSoggetto($azienda_mandataria);
			$azienda = new $classe_soggetto();
			$azienda->setCodiceAteco($ateco);
			$azienda->setComune($comune);
			$azienda->setFormaGiuridica($formaGiuridica);
			//tipo_soggetto_id = NULL
			//comune_unione_comune_id = NULL

			$azienda->setAcronimoLaboratorio($azienda_mandataria['acronimo_laboratorio']);
			$azienda->setKPAzienda($azienda_mandataria['_kp_azienda']);

			$azienda->setDenominazione($azienda_mandataria['ragionesociale']);
			$azienda->setPartitaIva($azienda_mandataria['partitaiva']);
			$azienda->setCodiceFiscale($azienda_mandataria['codicefiscale']);
			$azienda->setDataCostituzione(isset($azienda_mandataria['datacostituzione']) ? new \DateTime($azienda_mandataria['datacostituzione']) : null);
			$azienda->setDimensione($azienda_mandataria['numerodipendenti']);
			$azienda->setEmail(isset($azienda_mandataria['sede_email']) ? $azienda_mandataria['sede_email'] : "email");
			$azienda->setTel(isset($azienda_mandataria['sede_telefono']) ? $azienda_mandataria['sede_telefono'] : "tel");
			$azienda->setFax($azienda_mandataria['sede_fax']);
			$azienda->setVia($azienda_mandataria['sede_indirizzo']);
			$azienda->setCivico($azienda_mandataria['sede_numero']);
			$azienda->setCap($azienda_mandataria['sede_cap']);
			$azienda->setComune($comune);
			//$azienda->setTipoSoggetto('AZIENDA'); //per il bando 380 sono tutte aziende

			if ($classe_soggetto == 'SoggettoBundle\Entity\Azienda') {
				$azienda->setFatturato($azienda_mandataria['fatturato']);
				$azienda->setBilancio($azienda_mandataria['bilancio']);
				$azienda->setCcia($azienda_mandataria['cciaa']);
			}

			$azienda->setEmailPec($azienda_mandataria['indirizzo_pec']);
			if (isset($dimensione)) {
				$azienda->setDimensioneImpresa($dimensione); //la kf della dimensione coincide con "codice" di my_fesr_aziende.pre_aziende
			}
			$azienda->setMatricolaInps($azienda_mandataria['numero_matricola_inps']);
			$azienda->setImpresaIscrittaInps($azienda_mandataria['inps_iscrizione']);
			$azienda->setMotivazioniNonIscrizioneInps($azienda_mandataria['motivazione_non_iscrizione_inps']);

			$iscrittaInail = $azienda_mandataria['iscritta_inail'];
			if ($iscrittaInail == 'S') {
				$azienda->setImpresaIscrittaInail(1);
			} else {
				$azienda->setImpresaIscrittaInail(0);
			}

			$azienda->setImpresaIscrittaInailDi($azienda_mandataria['inail_iscrizione']);
			$azienda->setNumeroCodiceDittaImpresaAssicurata($azienda_mandataria['codice_assicurazione_inail']);
			$azienda->setMotivazioniNonIscrizioneInail($azienda_mandataria['codice_assicurazione_inail']);
			$azienda->setCcnl($azienda_mandataria['contratto_nazionale']);
			$azienda->setStato($nazione);
			$azienda->setIdSfinge2013($azienda_mandataria['_kp_azienda']);

			$azienda->setDataRegistrazione(new \DateTime()); //Informazioni di TEST
			$azienda->setCodiceOrganismo('00000'); //Informazioni di TEST

			$em->persist($azienda);
		}

		return $azienda;
	}

	public function creaIncarico($em, $persona, $soggetto) {

		$tipoincarico = $em->getRepository('SoggettoBundle\Entity\TipoIncarico')->findOneById(5);
		$stato = $em->getRepository('SoggettoBundle\Entity\StatoIncarico')->find(1);

		$incarico = new IncaricoPersona();
		$incarico->setSoggetto($soggetto);
		$incarico->setIncaricato($persona);
		$incarico->setTipoIncarico($tipoincarico);
		$incarico->setStato($stato);

		$em->persist($incarico);

		return $incarico;
	}

	public function creaIncaricoLR($em, $persona, $soggetto) {

		$tipoincarico = $em->getRepository('SoggettoBundle\Entity\TipoIncarico')->findOneById(2);
		$stato = $em->getRepository('SoggettoBundle\Entity\StatoIncarico')->find(1);

		$incarico = new IncaricoPersona();
		$incarico->setSoggetto($soggetto);
		$incarico->setIncaricato($persona);
		$incarico->setTipoIncarico($tipoincarico);
		$incarico->setStato($stato);

		$em->persist($incarico);

		return $incarico;
	}

	public function getTitoloAbstractContributoRichiesto($pre_richiesta) {
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();

		//recupero titolo e abstract della richiesta
		$statement = $connection->prepare("
                        SELECT a.titolo, a._abstract,istp.contributorichiesto
                        FROM myfesr_aziende.pre_progetti p
                        LEFT JOIN myfesr_aziende.pre_progetti_laboratorio_2020 a on (p._kp_progetto = a._kf_progetto)
						LEFT JOIN myfesr.ist_progetti istp ON istp._kf_progetto=p._kp_progetto
                        WHERE _kf_richiesta_finanziamento = :_kf_richiesta_finanziamento");
		$statement->bindValue('_kf_richiesta_finanziamento', $pre_richiesta['_kp_richiesta_finanziamento']);
		$statement->execute();
		return $statement->fetch();
	}

	public function creaRichiesta($em, $azienda_mandataria, $pre_richiesta) { //Implementare idSfinge2013??
		// Both of these return the "customer" entity manager
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();

		//recupero titolo e abstract della richiesta
		$titoloAbstract = $this->getTitoloAbstractContributoRichiesto($pre_richiesta);

		//recupero la data di invio, di creazione e di modifica
		$statement = $connection->prepare("
                        SELECT *
                        FROM myfesr_aziende.richieste_protocollo p
                        LEFT JOIN myfesr_aziende.pre_richieste_finanziamento a ON (p._kp_richiesta_protocollo = a._kf_richiesta_protocollo)
                        LEFT JOIN myfesr_aziende.pre_progetti b ON (a._kp_richiesta_finanziamento = b._kf_richiesta_finanziamento)				
                        WHERE _kf_richiesta_finanziamento = :_kf_richiesta_finanziamento");
		$statement->bindValue('_kf_richiesta_finanziamento', $pre_richiesta['_kp_richiesta_finanziamento']);
		$statement->execute();
		$date = $statement->fetch();

		$procedura = $em->getRepository('SfingeBundle\Entity\Procedura')->find($this->getIdProcedura2020());

		$stato = $em->getRepository('BaseBundle\Entity\StatoRichiesta')->find(5);

		$dataRichiesta = new \DateTime($date['data_richiesta']);
		$dataCreazione = new \DateTime($date['datacreazione']);
		$dataModifica = new \DateTime($date['data_richiesta']);

		$richiesta = new Richiesta();
		$richiesta->setProcedura($procedura);
		$richiesta->setStato($stato);
		$richiesta->setTitolo($titoloAbstract['titolo']);
		$richiesta->setAbstract($titoloAbstract['_abstract']);
		$richiesta->setDataInvio($dataRichiesta);
		$richiesta->setDataCreazione($dataCreazione);
		$richiesta->setDataModifica($dataModifica);
		$richiesta->setAbilitaGestioneBandoChiuso(0);
		$richiesta->setModificatoDa('DMCVCN81A15G273T');
		$richiesta->setIdSfinge2013($pre_richiesta['_kp_richiesta_finanziamento']);
		$richiesta->setContributoRichiesta($titoloAbstract['contributorichiesto']);

		$coppia = $this->getParametriBollo();

		if (!is_null($coppia)) {
			$statement = $connection->prepare("
                            SELECT a._kf_sp_voceasse, a.valore
                            FROM myfesr_aziende.pre_progetti p
                            LEFT JOIN myfesr_aziende.pre_sp_asse6 a on (p._kp_progetto = a._kf_progetto)
                            WHERE _kf_richiesta_finanziamento = :_kf_richiesta_finanziamento AND (_kf_sp_voceasse = :_kf_sp_voceasse OR _kf_sp_voceasse = :_kf_sp_voceasse2)
                            ORDER BY _kf_sp_voceasse");


			$statement->bindValue('_kf_richiesta_finanziamento', $pre_richiesta['_kp_richiesta_finanziamento']);
			$statement->bindValue('_kf_sp_voceasse', $coppia[0]); //data marca da bollo
			$statement->bindValue('_kf_sp_voceasse2', $coppia[1]); //numero marca da bollo
			$statement->execute();
			$valoriBollo = $statement->fetchAll();

			if (count($valoriBollo) > 1) {
				$dataBollo = \DateTime::createFromFormat('d/m/Y', $valoriBollo[0]['valore']); //alcune date saranno da correggere manualmente a DB perchè hanno un formato diverso
				$numeroBollo = $valoriBollo[1]['valore'];

				if ($dataBollo) {
					$richiesta->setDataMarcaDaBollo($dataBollo);
				}
				if ($numeroBollo) {
					$richiesta->setNumeroMarcaDaBollo($numeroBollo);
				} else {
					$richiesta->setEsenteMarcaDaBollo(1);
				}
			} else {
				$richiesta->setEsenteMarcaDaBollo(1);
			}
		}

		$em->persist($richiesta);

		return $richiesta;
	}

	protected function creaSoggettoVersion($soggetto2020, $azienda) {
		$em = $this->getEm();

		$comune = $em->getRepository('GeoBundle\Entity\GeoComune')->findOneByDenominazione($azienda['comuneSede']);
		$formaGiuridica = $em->getRepository('SoggettoBundle\Entity\FormaGiuridica')->findOneByCodice($azienda['codiceFormaGiuridica']);
		$ateco = $em->getRepository('SoggettoBundle\Entity\Ateco')->findOneByCodice($azienda['sede_ateco']);
		if (isset($azienda['codiceDim'])) {
			$dimensione = $em->getRepository('SoggettoBundle\Entity\DimensioneImpresa')->find($azienda['codiceDim']);
		}

		//Recupero informazioni di TEST ---------
		$nazione = $em->getRepository('GeoBundle\Entity\GeoStato')->find(1);
		// --------------------------------------

		if ($soggetto2020 instanceof \SoggettoBundle\Entity\ComuneUnione) {
			$soggettoVersion = new \SoggettoBundle\Entity\ComuneUnioneVersion();
			// $soggettoVersion->setComuneUnioneComune($soggetto->getComuneUnioneComune());
		} elseif ($soggetto2020 instanceof \SoggettoBundle\Entity\Azienda) {
			$soggettoVersion = new \SoggettoBundle\Entity\AziendaVersion();
			$soggettoVersion->setFatturato($azienda['fatturato']);
			$soggettoVersion->setBilancio($azienda['bilancio']);
			$soggettoVersion->setCcia($azienda['cciaa']);
		} else {
			$soggettoVersion = new \SoggettoBundle\Entity\SoggettoVersion();
		}
		$soggettoVersion->setCodiceAteco($ateco);
		$soggettoVersion->setComune($comune);
		$soggettoVersion->setFormaGiuridica($formaGiuridica);
		//tipo_soggetto_id = NULL
		$soggettoVersion->setDenominazione($azienda['ragionesociale']);
		$soggettoVersion->setPartitaIva($azienda['partitaiva']);
		$soggettoVersion->setCodiceFiscale($azienda['codicefiscale']);
		$soggettoVersion->setDataCostituzione(isset($azienda['datacostituzione']) ? new \DateTime($azienda['datacostituzione']) : null);
		$soggettoVersion->setDimensione($azienda['numerodipendenti']);
		$soggettoVersion->setEmail(isset($azienda['sede_email']) ? $azienda['sede_email'] : "email");
		$soggettoVersion->setTel(isset($azienda['sede_telefono']) ? $azienda['sede_telefono'] : "tel");
		$soggettoVersion->setFax($azienda['sede_fax']);
		$soggettoVersion->setVia($azienda['sede_indirizzo']);
		$soggettoVersion->setCivico($azienda['sede_numero']);
		$soggettoVersion->setCap($azienda['sede_cap']);
		$soggettoVersion->setComune($comune);
		//$azienda->setTipoSoggetto('AZIENDA'); //per il bando 380 sono tutte aziende
		$soggettoVersion->setEmailPec($azienda['indirizzo_pec']);
		if (isset($dimensione)) {
			$soggettoVersion->setDimensioneImpresa($dimensione); //la kf della dimensione coincide con "codice" di my_fesr_aziende.pre_aziende
		}
		$soggettoVersion->setMatricolaInps($azienda['numero_matricola_inps']);
		$soggettoVersion->setImpresaIscrittaInps($azienda['inps_iscrizione']);
		$soggettoVersion->setMotivazioniNonIscrizioneInps($azienda['motivazione_non_iscrizione_inps']);

		$iscrittaInail = $azienda['iscritta_inail'];
		if ($iscrittaInail == 'S') {
			$soggettoVersion->setImpresaIscrittaInail(1);
		} else {
			$soggettoVersion->setImpresaIscrittaInail(0);
		}

		$soggettoVersion->setImpresaIscrittaInailDi($azienda['inail_iscrizione']);
		$soggettoVersion->setNumeroCodiceDittaImpresaAssicurata($azienda['codice_assicurazione_inail']);
		$soggettoVersion->setMotivazioniNonIscrizioneInail($azienda['codice_assicurazione_inail']);
		$soggettoVersion->setCcnl($azienda['contratto_nazionale']);
		$soggettoVersion->setStato($nazione);

		$soggettoVersion->setDataRegistrazione(new \DateTime()); //Informazioni di TEST
		$soggettoVersion->setCodiceOrganismo('00000'); //Informazioni di TEST    

		return $soggettoVersion;
	}

	public function creaProponenti($em, $progetto, $richiesta, $pre_richiesta, $soggettoMandatario, $altre_aziende_proponenti, $multiPianoCosto, $azienda_mandataria) {

		//Creazione del proponente mandatario
		$proponente = new Proponente();
		$proponente->setRichiesta($richiesta);
		$proponente->setSoggetto($soggettoMandatario);
		$proponente->setMandatario(1);
		$proponente->setDimensioneImpresa($soggettoMandatario->getDimensioneImpresa());
		if (method_exists($soggettoMandatario, "getFatturato")) {
			$proponente->setFatturato($soggettoMandatario->getFatturato());
			$proponente->setBilancio($soggettoMandatario->getBilancio());
		}

		$soggettoVersion = $this->creaSoggettoVersion($soggettoMandatario, $azienda_mandataria);
		$proponente->setSoggettoVersion($soggettoVersion);

		//$proponente->setSedeLegaleComeOperativa();

		$em->persist($proponente);

		//importo i documenti associati ai proponenti
		$this->importaDocumentiProponenti($richiesta, $pre_richiesta, $proponente, $azienda_mandataria);

		//CREARE IL PIANO COSTI DEL MANDATARIO
		$this->creaVociPianoCosto($em, $progetto, $proponente, $richiesta, $azienda_mandataria);

		//Creazione degli eventuali altri proponenti
		foreach ($altre_aziende_proponenti as $key => $azienda) {

			$aziendaProponente = $this->creaAzienda($em, $azienda);

			$proponente = new Proponente();
			$proponente->setRichiesta($richiesta);
			$proponente->setSoggetto($aziendaProponente);
			$proponente->setMandatario(0);
			$proponente->setDimensioneImpresa($aziendaProponente->getDimensioneImpresa());
			if (method_exists($aziendaProponente, "getFatturato")) {
				$proponente->setFatturato($aziendaProponente->getFatturato());
				$proponente->setBilancio($aziendaProponente->getBilancio());
			}

			$soggettoVersion = $this->creaSoggettoVersion($aziendaProponente, $azienda);
			$proponente->setSoggettoVersion($soggettoVersion);

			$em->persist($proponente);

			//importo i documenti associati ai proponenti
			$this->importaDocumentiProponenti($richiesta, $pre_richiesta, $proponente, $azienda);

			if ($multiPianoCosto) {
				$this->creaVociPianoCosto($em, $progetto, $proponente, $richiesta, $azienda);
			}
		}

		return true;
	}

// DA COMPLETARE CON MULTIPIANOCOSTO

	public function creaOggettoRichiesta($em, $richiesta, $pre_richiesta) {

		$num_bando = $this->getIdProcedura2013();

		$oggettoRichiesta = null;

		switch ($num_bando) {

			case 373:
				$oggettoRichiesta = new OggettoImportazione373();
				$oggettoRichiesta->setDescrizione('Import_373');

				break;

			case 380:
				$oggettoRichiesta = new OggettoImportazione380();
				$oggettoRichiesta->setDescrizione('Import_380');
				break;
			
			case 383:
				$oggettoRichiesta = new OggettoImportazione383();
				$oggettoRichiesta->setDescrizione('Import_383');
				break;

			default :
				return;
		}

		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("SELECT * FROM myfesr_aziende.pre_progetti p WHERE _kf_richiesta_finanziamento = :_kf_richiesta_finanziamento");
		$statement->bindValue('_kf_richiesta_finanziamento', $pre_richiesta['_kp_richiesta_finanziamento']);
		$statement->execute();

		$pre_progetto = $statement->fetch();

		$oggettoRichiesta->setRichiesta($richiesta);
		$oggettoRichiesta->setCostoTotaleImportato($pre_progetto['costototale']);
		$oggettoRichiesta->setContributoImportato($pre_progetto['contributo']);

		if ($num_bando == 380) {
			$oggettoRichiesta->setTipologia($pre_progetto['tipologia']);
			$oggettoRichiesta->setTipologiaAzienda($pre_progetto['tipologia_azienda']);
		}

		$em->persist($oggettoRichiesta);
	}

	public function creaVociPianoCosto($em, $progetto, $proponente, $richiesta, $azienda) {

		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("SELECT aprev.codice, prev.valorecosto
                                           FROM myfesr_aziende.pre_preventivi prev
                                           LEFT JOIN myfesr_aziende.pre_progetti a on (prev._kf_progetto = a._kp_progetto)
                                           LEFT JOIN myfesr.aam_preventivi aprev ON prev._kf_preventivo=aprev._kp_preventivo 
                                           WHERE _kf_progetto = :_kf_progetto AND prev._kf_azienda = :_kf_azienda
                                           ORDER BY prev._kp_preventivo");
		$statement->bindValue('_kf_progetto', $progetto['_kp_progetto']);
		$statement->bindValue('_kf_azienda', $azienda['_kp_azienda']);
		$statement->execute();
		$valoriPianoCosto = $statement->fetchAll();

		//Recupero le voci del piano costo ordinate per ID crescente
		$piani_costo = $em->getRepository('RichiesteBundle\Entity\PianoCosto')->findBy(array('procedura' => $this->getIdProcedura2020()), array('id' => 'asc'));
		$piani_costo_indicizzati = array();

		$sezioni = array();

		// indicizzo i piani costo e le sezioni
		foreach ($piani_costo as $piano) {
			if (!array_key_exists($piano->getSezionePianoCosto()->getCodice(), $sezioni)) {
				$sezioni[$piano->getSezionePianoCosto()->getCodice()] = array("piano" => null, "totale" => 0);
			}

			if ($piano->getCodice() != 'TOT') {
				$piani_costo_indicizzati[$piano->getCodice()] = $piano;
			} else {
				$sezioni[$piano->getSezionePianoCosto()->getCodice()]["piano"] = $piano;
			}
		}

		// ciclo le voci trovate nel db vecchio
		foreach ($valoriPianoCosto as $valore) {
			if (!array_key_exists($valore['codice'], $piani_costo_indicizzati)) {
				throw new \Exception("Errore nel piano costo");
			}

			$vocePianoCosto = new VocePianoCosto();
			$vocePianoCosto->setPianoCosto($piani_costo_indicizzati[$valore['codice']]);
			$vocePianoCosto->setImportoAnno1($valore['valorecosto']);
			$vocePianoCosto->setProponente($proponente);
			$vocePianoCosto->setRichiesta($richiesta);
			$richiesta->addVociPianoCosto($vocePianoCosto);

			$codice_sezione = $piani_costo_indicizzati[$valore['codice']]->getSezionePianoCosto()->getCodice();
			$sezioni[$codice_sezione]["totale"] += $valore['valorecosto'];

			$em->persist($vocePianoCosto);
		}

		// ciclo le sezioni per salvare il relativo totale
		foreach ($sezioni as $sezione) {
			$vocePianoCosto = new VocePianoCosto();
			$vocePianoCosto->setPianoCosto($sezione["piano"]);
			if (is_null($vocePianoCosto->getPianoCosto())) {
				echo 'ciao';
			}
			$vocePianoCosto->setImportoAnno1($sezione["totale"]);
			$vocePianoCosto->setProponente($proponente);
			$vocePianoCosto->setRichiesta($richiesta);
			$richiesta->addVociPianoCosto($vocePianoCosto);

			$em->persist($vocePianoCosto);
		}
	}

	public function creaRichiestaProtocollo($em, $richiesta, $pre_richiesta) {
		
	}

	public function creaSede() { //DA FARE
	}

// DA FARE

	public function creaIstruttoria($richiesta, $pre_richiesta) {
		
	}

	public function creaAtc($richiesta, $pre_richiesta) {
		
	}

	public function importaDocumentiProponenti($richiesta, $pre_richiesta, $proponente, $azienda) {
		
	}

	public function importaDocumentiPresentazione($richiesta, $pre_richiesta) {
		
	}

	public function getFilePathFromHash($hash) {

		$path = base64_encode($hash);
		$path = $this->shift_chr($path, 17, 14, 22);
		$path = urlencode($path);

		return $path;
	}

	function shift_chr($plain, $shift1, $shift2, $shift3) {
		$cipher = "";

		for ($i = 0; $i < strlen($plain); $i++) {
			$p = substr($plain, $i, 1);
			$p = ord($p);

			if (($p >= 33) && ($p <= 63)) {
				$c = $p + $shift1;
				if ($c > 63)
					$c = $c - 31;
			}
			if (($p >= 65) && ($p <= 95)) {
				$c = $p + $shift2;
				if ($c > 95)
					$c = $c - 31;
			}
			if (($p >= 96) && ($p <= 126)) {
				$c = $p + $shift3;
				if ($c > 126)
					$c = $c - 31;
			}
			else {
				$c = $p;
			}

			$c = chr($c);
			$cipher = $cipher . $c;
		}

		return $cipher;
	}

	public function isMultiPianoCosto() {
		return false;
	}

	public function dammiObiettiviRealizzativi($pre_richiesta) {
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("
                        SELECT obb.*, b._kf_richiesta_finanziamento
                        FROM myfesr_aziende.pre_obiettivi_realizzativi_2020 obb
                        LEFT JOIN myfesr_aziende.pre_progetti b ON (obb._kf_progetto = b._kp_progetto)	
						LEFT JOIN myfesr_aziende.pre_richieste_finanziamento a ON (a._kp_richiesta_finanziamento = b._kp_progetto)
                        WHERE b._kf_richiesta_finanziamento = :_kf_richiesta_finanziamento");
		$statement->bindValue('_kf_richiesta_finanziamento', $pre_richiesta['_kp_richiesta_finanziamento']);
		$statement->execute();
		$or = $statement->fetchAll();
		return $or;
	}

	public function dammiObiettiviRealizzativiAcronimo($pre_richiesta) {
		$customerEm = $this->container->get('doctrine')->getManager('customer2');
		$connection = $customerEm->getConnection();
		$statement = $connection->prepare("
                        SELECT obb.*,a.acronimo_laboratorio, b._kf_richiesta_finanziamento
                        FROM myfesr_aziende.pre_obiettivi_realizzativi_2020 obb
                        LEFT JOIN myfesr_aziende.pre_progetti b ON (obb._kf_progetto = b._kp_progetto)	
						LEFT JOIN myfesr_aziende.pre_aziende a ON (a._kp_azienda = obb._kf_partner_responsabile)
						LEFT JOIN myfesr_aziende.pre_richieste_finanziamento r ON (r._kp_richiesta_finanziamento = b._kf_richiesta_finanziamento)	
                        WHERE b._kf_richiesta_finanziamento = :_kf_richiesta_finanziamento");
		$statement->bindValue('_kf_richiesta_finanziamento', $pre_richiesta['_kp_richiesta_finanziamento']);
		$statement->execute();
		$or = $statement->fetchAll();
		return $or;
	}

	public function creaObiettiviRealizzativiPerRichiesta($pre_richiesta) {
		$obiettivi = $this->dammiObiettiviRealizzativi($pre_richiesta);
		$em = $this->getEm();
		$countImportati = 0;
		foreach ($obiettivi as $obiettivo) {
			$obiettivo2020 = new \RichiesteBundle\Entity\ObiettivoRealizzativo();
			if (!is_null($obiettivo["_kf_richiesta_finanziamento"])) {
				$richiesta = $em->getRepository('RichiesteBundle\Entity\Richiesta')->findOneBy(array('id_sfinge_2013' => $obiettivo["_kf_richiesta_finanziamento"]));
				if (is_null($richiesta) || count($richiesta->getObiettiviRealizzativi()) > 0) {
					continue;
				}
				// per la richiesta
				$obiettivo2020->setRichiesta($richiesta);
				$obiettivo2020->setProponente($richiesta->getMandatario()); 
			}

			$obiettivo2020->setCodiceOr($obiettivo["codice_or"]);
			$obiettivo2020->setTitoloOr($obiettivo["titolo_or"]);
			$obiettivo2020->setMeseAvvioPrevisto($obiettivo["mese_avvio"]);
			$obiettivo2020->setMeseFinePrevisto($obiettivo["mese_fine"]);

			$obiettivo2020->setObiettiviPrevisti($obiettivo["obiettivi"]);
			$obiettivo2020->setAttivitaPreviste($obiettivo["attività_previste"]);
			$obiettivo2020->setRisultatiAttesi($obiettivo["risultati_attesi"]);
			$obiettivo2020->setPercentualeRi($obiettivo["percentuale_ri"]);
			$obiettivo2020->setPercentualeSs($obiettivo["percentuale_sp"]);
			$obiettivo2020->setIdSfinge2013($obiettivo["_kp_obiettivorealizzativo"]);

			$em->persist($obiettivo2020);
			$countImportati++;
		}

		return $countImportati;
	}

	public function creaObiettiviRealizzativiPerRichiestaEAcronimo($pre_richiesta) {
		$obiettivi = $this->dammiObiettiviRealizzativiAcronimo($pre_richiesta);
		$em = $this->getEm();
		$countImportati = 0;

		$richiesta = $em->getRepository('RichiesteBundle\Entity\Richiesta')->findOneBy(array('id_sfinge_2013' => $pre_richiesta['_kp_richiesta_finanziamento']));
		if (is_null($richiesta)) {
			return;
		}
		foreach ($obiettivi as $obiettivo) {
			$obiettivo2020 = new \RichiesteBundle\Entity\ObiettivoRealizzativo();
//			if (!is_null($obiettivo["_kf_richiesta_finanziamento"])) {
			$proponenteOR = null;
			foreach ($richiesta->getProponenti() as $proponente) {
				if ($proponente->getSoggetto()->getAcronimoLaboratorio() == $obiettivo['acronimo_laboratorio']) {
					$proponenteOR = $proponente;
					break;
				}
			}
			if (is_null($proponenteOR) || count($proponenteOR->getObiettiviRealizzativi()) > 0) {
				continue;
			}
			// per la richiesta e proponente
			$obiettivo2020->setRichiesta($richiesta);
			$obiettivo2020->setProponente($proponenteOR);
//			}

			$obiettivo2020->setCodiceOr($obiettivo["codice_or"]);
			$obiettivo2020->setTitoloOr($obiettivo["titolo_or"]);
			$obiettivo2020->setMeseAvvioPrevisto($obiettivo["mese_avvio"]);
			$obiettivo2020->setMeseFinePrevisto($obiettivo["mese_fine"]);

			$obiettivo2020->setObiettiviPrevisti($obiettivo["obiettivi"]);
			$obiettivo2020->setAttivitaPreviste($obiettivo["attività_previste"]);
			$obiettivo2020->setRisultatiAttesi($obiettivo["risultati_attesi"]);
			$obiettivo2020->setPercentualeRi($obiettivo["percentuale_ri"]);
			$obiettivo2020->setPercentualeSs($obiettivo["percentuale_sp"]);
			$obiettivo2020->setIdSfinge2013($obiettivo["_kp_obiettivorealizzativo"]);

			$em->persist($obiettivo2020);
			$countImportati++;
		}

		return $countImportati;
	}

}
