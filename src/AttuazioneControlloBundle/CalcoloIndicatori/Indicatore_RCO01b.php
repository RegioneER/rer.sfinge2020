<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_RCO01b extends ACalcoloIndicatore {

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

        $precedente = new Indicatore_RCO01a($this->container, $richiesta);

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

//            $fatturato = $this->fatturatoPiccola($fatturatoAnno1, $fatturatoAnno2) && !$precedente->fatturatoMicro($fatturatoAnno1, $fatturatoAnno2);
//            $dipendenti = $this->dipendentiPiccola($numeroDipendentiAnno1, $numeroDipendentiAnno2) && !$precedente->dipendentiMicro($numeroDipendentiAnno1, $numeroDipendentiAnno2);
//            $patrimoniale = $this->patrimonialePiccola($statoPatrimonialeAnno1, $statoPatrimonialeAnno2) && !$precedente->patrimonialeMicro($statoPatrimonialeAnno1, $statoPatrimonialeAnno2);
//            
//            if($dipendenti && ($fatturato || $patrimoniale)) {
//                $count++;
//            }

            $anno1 = $this->verificaAnno1($fatturatoAnno1, $numeroDipendentiAnno1, $statoPatrimonialeAnno1);
            $anno2 = $this->verificaAnno2($fatturatoAnno2, $numeroDipendentiAnno2, $statoPatrimonialeAnno2);


            $resPrecedente = $precedente->getValore();
            
            if ($resPrecedente == 0 && ($anno1 || $anno2)) {
                $count++;
            }
            if($resPrecedente != 0 && $anno1 && $anno1) {
                $count++;
            }
        }
        if($precedente->getValore() != 0) {
            $val = $count - $precedente->getValore();
            return $val > 0 ? $val : 0;
        }
        return $count;
    }

    public function verificaAnno1($fatturatoAnno1, $numeroDipendentiAnno1, $statoPatrimonialeAnno1) {
        return $numeroDipendentiAnno1 < 50 && ($fatturatoAnno1 <= 10000000 || $statoPatrimonialeAnno1 <= 10000000);
    }

    public function verificaAnno2($fatturatoAnno2, $numeroDipendentiAnno2, $statoPatrimonialeAnno2) {
        return $numeroDipendentiAnno2 < 50 && ($fatturatoAnno2 <= 10000000 || $statoPatrimonialeAnno2 <= 10000000);
    }

    public function fatturatoPiccola($fatturatoAnno1, $fatturatoAnno2) {
        return ( $fatturatoAnno1 <= 10000000 || $fatturatoAnno2 <= 10000000);
    }

    public function dipendentiPiccola($numeroDipendentiAnno1, $numeroDipendentiAnno2) {
        return ($numeroDipendentiAnno1 < 50 || $numeroDipendentiAnno2 < 50);
    }

    public function patrimonialePiccola($statoPatrimonialeAnno1, $statoPatrimonialeAnno2) {
        return ($statoPatrimonialeAnno1 <= 10000000 || $statoPatrimonialeAnno2 <= 10000000 );
    }

}
