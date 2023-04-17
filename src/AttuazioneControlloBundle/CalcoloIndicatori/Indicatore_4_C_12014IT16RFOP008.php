<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;


class Indicatore_4_C_12014IT16RFOP008 extends ACalcoloIndicatore{

    const CODICI_AZIONI_AMMESSI = ['4.1.2'];
    /**
     * @throws \Exception
     */
    public function getValore():float{
        $this->verificaAzioniBando();

        /** @var \RichiesteBundle\Entity\Bando5\OggettoUbicazioneEdificio $oggetto */
        $oggetto = $this->richiesta->getOggettiRichiesta()->first();


        return $oggetto->getIndirizziCatastali()->count();
    }

    /**
     * @throws \Exception
     */
    private function verificaAzioniBando():void{
        $azioni = $this->richiesta->getProcedura()->getAzioni();
        foreach ($azioni as $azione) {
           if(\in_array($azione->getCodice(), self::CODICI_AZIONI_AMMESSI)){
               return;
           }
        }
        throw new \Exception('Azione non prevista per questo indicatore');
    }
}