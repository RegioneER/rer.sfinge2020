<?php

namespace IstruttorieBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use AttuazioneControlloBundle\Entity\IterProgetto;
use BaseBundle\Exception\SfingeException;


class GestoreIstruttoriaAcquisizioniBase extends GestoreIstruttoriaProcedureParticolariBase {

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

        $ruoloSoggettoProgrammatore = $em->getRepository("MonitoraggioBundle:TC24RuoloSoggetto")->findOneBy(array("cod_ruolo_sog" => 1));
        $soggettoCollegatoProgrammatore->setCodUniIpa(SoggettiCollegati::COD_UNI_IPA_ER);
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



}
