<?php

namespace IstruttorieBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use AttuazioneControlloBundle\Entity\IterProgetto;
use BaseBundle\Exception\SfingeException;


class GestoreIstruttoriaIngegneriaFinanziariaBase extends GestoreIstruttoriaProcedureParticolariBase {

    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     *
     * Metodo richiamato contestualmente al passaggio della richiesta in ATTUAZIONE, tramite il pulsante VALIDA
     * serve per popolare automaticamente le info sui SOGGETTI CORRELATI; utile ai fini del monitoraggio
     */
    protected function popolaSoggettiCollegatiRER(Richiesta $richiesta){

        $em = $this->getEm();

        $soggettoProgrammatore = $em->getRepository('SoggettoBundle:Soggetto')->findOneBy(array("denominazione" => "Regione Emilia-Romagna", "forma_giuridica" => 42));
        if (is_null($soggettoProgrammatore)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $soggettoCollegatoProgrammatore = new SoggettiCollegati($richiesta, $soggettoProgrammatore);

        $ruoloSoggettoProgrammatore = $em->getRepository("MonitoraggioBundle:TC24RuoloSoggetto")->findOneBy(array("cod_ruolo_sog" => 1));

        $soggettoCollegatoProgrammatore->setTc24RuoloSoggetto($ruoloSoggettoProgrammatore);  // Programmatore del progetto
        $soggettoCollegatoProgrammatore->setCodUniIpa(SoggettiCollegati::COD_UNI_IPA_ER);

        $richiesta->addMonSoggettiCorrelati($soggettoCollegatoProgrammatore);

        // ------------------------------------------------------------------

        // BENEFICIARIO del progetto

        // TODO Il beneficiario è il fondo gestore (UNIFIDI) - Da inserire in SFINGE
        $soggettoBeneficiario = $em->getRepository('SoggettoBundle:Soggetto')->findOneBy(array("denominazione" => "UNIFIDI"));
        if (is_null($soggettoBeneficiario)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $soggettoCollegatoBeneficiario = new SoggettiCollegati($richiesta, $soggettoBeneficiario);


        $ruoloSoggettoBeneficiario = $em->getRepository("MonitoraggioBundle:TC24RuoloSoggetto")->findOneBy(array("cod_ruolo_sog" => 2));

        $soggettoCollegatoBeneficiario->setTc24RuoloSoggetto($ruoloSoggettoBeneficiario);    // Beneficiario del progetto
        

        $richiesta->addMonSoggettiCorrelati($soggettoCollegatoBeneficiario);
    }


    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     *
     * Metodo richiamato contestualmente al passaggio della richiesta in ATTUAZIONE, tramite il pulsante VALIDA
     * serve per popolare automaticamente le info sulle FASI PROCEDURALI - ITER DI PROGETTO; utile ai fini del monitoraggio
     */
    protected function popolaIterProgetto( Richiesta $richiesta){

        $em = $this->getEm();

        // DESTINAZIONE
        $iterProgettoMon = new IterProgetto();
        $iterProgettoMon->setRichiesta($richiesta);

        // Recupero il CODICE NATURA CUP della TC46  // 01,02,03, 06, 07, 08
        $naturaCup = $richiesta->getIstruttoria()->getCupNatura();

        if(!is_null($naturaCup)) {

            if($naturaCup->getId() == 6){

                // ACQUISTO DI PARTECIPAZIONI AZIONARIE E CONFERIMENTI DI CAPITALE - FASE INIZIALE - ATTRIBUZIONE FINANZIAMENTO
                $faseProcedurale = $em->getRepository("MonitoraggioBundle\Entity\TC46FaseProcedurale")->find(16);
                $iterProgettoMon->setFaseProcedurale($faseProcedurale);

                $iterProgettoMon->setDataFineEffettiva(new \DateTime());
                $iterProgettoMon->setDataFinePrevista(new \DateTime());
                $iterProgettoMon->setDataInizioEffettiva(new \DateTime());
                $iterProgettoMon->setDataInizioPrevista(new \DateTime());

                // Associa la ENTITY alla richiesta
                $richiesta->addMonIterProgetti($iterProgettoMon);

            }

        }

    }


}

