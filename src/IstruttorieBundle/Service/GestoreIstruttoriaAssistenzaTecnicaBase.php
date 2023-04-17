<?php

namespace IstruttorieBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use AttuazioneControlloBundle\Entity\IterProgetto;
use BaseBundle\Exception\SfingeException;


class GestoreIstruttoriaAssistenzaTecnicaBase extends GestoreIstruttoriaProcedureParticolariBase {

    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     *
     * Metodo richiamato contestualmente al passaggio della richiesta in ATTUAZIONE, tramite il pulsante VALIDA
     * serve per popolare automaticamente le info sui SOGGETTI CORRELATI; utile ai fini del monitoraggio
     */
    protected function popolaSoggettiCollegatiRER( Richiesta $richiesta){

        $em = $this->getEm();

        // REGIONE EMILIA ROMAGNA - "programmatore"
        $soggettoProgrammatore = $em->getRepository('SoggettoBundle:Soggetto')->findOneBy(array("denominazione" => "Regione Emilia-Romagna", "forma_giuridica" => 42));
        if (is_null($soggettoProgrammatore)) {
            throw new SfingeException('Risorsa non trovata');
        }
        $soggettoCollegatoProgrammatore = new SoggettiCollegati($richiesta, $soggettoProgrammatore);
        $soggettoCollegatoProgrammatore->setCodUniIpa(SoggettiCollegati::COD_UNI_IPA_ER);

        $ruoloSoggettoProgrammatore = $em->getRepository("MonitoraggioBundle:TC24RuoloSoggetto")->findOneBy(array("cod_ruolo_sog" => 1));

        $soggettoCollegatoProgrammatore->setTc24RuoloSoggetto($ruoloSoggettoProgrammatore);  // Programmatore del progetto

        $richiesta->addMonSoggettiCorrelati($soggettoCollegatoProgrammatore);

        // ------------------------------------------------------------------

        // BENEFICIARIO del progetto
        $soggettoCollegatoBeneficiario = new SoggettiCollegati();
        $soggettoCollegatoBeneficiario->setRichiesta($richiesta);

        // Il beneficiario è RER o COMUNI....
        // I SOGGETTI MANDATARI per le ASS. TECNICHE sono tutti o RER o COMUNI (OK!!!)
        $soggettoBeneficiario = $richiesta->getSoggetto();
        $soggettoCollegatoBeneficiario->setSoggetto($soggettoBeneficiario);

        $ruoloSoggettoBeneficiario = $em->getRepository("MonitoraggioBundle\Entity\TC24RuoloSoggetto")->findBy(array("cod_ruolo_sog" => 2));

        $soggettoCollegatoBeneficiario->setTc24RuoloSoggetto($ruoloSoggettoBeneficiario[0]);    // Beneficiario del progetto

        $richiesta->addMonSoggettiCorrelati($soggettoCollegatoBeneficiario);

        return;
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

            if($naturaCup->getId() == 2){

                // REALIZZAZIONE E ACQUISTO DI SERVIZI - FASE INIZIALE - STIPULA CONTRATTO
                $faseProcedurale = $em->getRepository("MonitoraggioBundle\Entity\TC46FaseProcedurale")->find(3);
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
