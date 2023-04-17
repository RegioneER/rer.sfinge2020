<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_RCO01a extends ACalcoloIndicatore {
    
    public function getValore(): float {
        $fatturatoAnno1 = 0;
        $fatturatoAnno2 = 0;
        $numeroDipendentiAnno1 = 0;
        $numeroDipendentiAnno2 = 0;
        $statoPatrimonialeAnno1 = 0;
        $statoPatrimonialeAnno2 = 0;

        $richiesta = $this->richiesta;
        $procedura = $richiesta->getProcedura();
        $count = 0;
        $misti = array(184);
        foreach ($richiesta->getProponenti() as $proponente) {
            if(in_array($richiesta->getProcedura()->getId(), $misti) && $proponente->isDirittoPrivato() == false ) {
                continue;
            }
            $fatturatoAnno1 = $this->container->get("gestore_proponenti")->getGestore($procedura)->getFatturatoAnno1($proponente);
            $fatturatoAnno2 = $this->container->get("gestore_proponenti")->getGestore($procedura)->getFatturatoAnno2($proponente);

            $numeroDipendentiAnno1 = $this->container->get("gestore_proponenti")->getGestore($procedura)->getNumeroDipendentiAnno1($proponente);
            $numeroDipendentiAnno2 = $this->container->get("gestore_proponenti")->getGestore($procedura)->getNumeroDipendentiAnno2($proponente);

            $statoPatrimonialeAnno1 = $this->container->get("gestore_proponenti")->getGestore($procedura)->getStatoPatrimonialeAnno1($proponente);
            $statoPatrimonialeAnno2 = $this->container->get("gestore_proponenti")->getGestore($procedura)->getStatoPatrimonialeAnno2($proponente);
            
            //$fatturato = $this->fatturatoMicro($fatturatoAnno1, $fatturatoAnno2);
            //$dipendenti = $this->dipendentiMicro($numeroDipendentiAnno1, $numeroDipendentiAnno2);
            //$patrimoniale = $this->patrimonialeMicro($statoPatrimonialeAnno1, $statoPatrimonialeAnno2);
            
            //if($dipendenti && ($fatturato || $patrimoniale)) {
            //    $count++;
            //}
            
            $anno1 = $this->verificaAnno1($fatturatoAnno1, $numeroDipendentiAnno1, $statoPatrimonialeAnno1);
            $anno2 = $this->verificaAnno2($fatturatoAnno2, $numeroDipendentiAnno2, $statoPatrimonialeAnno2);
            
            if($anno1 || $anno2) {
                $count++;
            }
        }
        return $count;
    }
    
    public function verificaAnno1($fatturatoAnno1, $numeroDipendentiAnno1, $statoPatrimonialeAnno1) {
        return ($numeroDipendentiAnno1 <= 10) && ($fatturatoAnno1 <= 2000000 || $statoPatrimonialeAnno1 <= 2000000);
    }
    
    public function verificaAnno2($fatturatoAnno2, $numeroDipendentiAnno2, $statoPatrimonialeAnno2) {
        return ($numeroDipendentiAnno2 <= 10) && ($fatturatoAnno2 <= 2000000 || $statoPatrimonialeAnno2 <= 2000000);
    }

    public function fatturatoMicro($fatturatoAnno1, $fatturatoAnno2) {
        return ($fatturatoAnno1 <= 2000000) || ($fatturatoAnno2 <= 2000000);
    }
    
    public function dipendentiMicro($numeroDipendentiAnno1, $numeroDipendentiAnno2) {
        return ($numeroDipendentiAnno1 <= 10) || ($numeroDipendentiAnno2 <= 10);
    }
    
    public function patrimonialeMicro($statoPatrimonialeAnno1, $statoPatrimonialeAnno2) {
        return ($statoPatrimonialeAnno1 <= 2000000) || ($statoPatrimonialeAnno2 <= 2000000);
    }

}
