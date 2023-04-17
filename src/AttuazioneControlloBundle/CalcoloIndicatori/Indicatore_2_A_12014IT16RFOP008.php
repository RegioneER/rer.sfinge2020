<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;


class Indicatore_2_A_12014IT16RFOP008 extends ACalcoloIndicatore
{
    const PATH = 'banda_larga_2016.indice.sezione_1.ubicazione_area.elenco_vie.form';

    public function getValore(): float {
        /** @var \FascicoloBundle\Service\IstanzaFascicolo $istanzaFascicoloService  */
        $istanzaFascicoloService = $this->container->get('fascicolo.istanza');
        /** @var \RichiesteBundle\Entity\OggettoRichiesta $oggettoRichiesta  */
        $oggettoRichiesta = $this->richiesta->getOggettiRichiesta()->first();
        if($oggettoRichiesta === false){
            throw new \Exception('Calcolo implementato solo per bando "banda larga 2016"');         
        }
        $risultati = $istanzaFascicoloService->get($oggettoRichiesta->getIstanzaFascicolo(), self::PATH);
        return \is_null($risultati) ? 0 : \count($risultati);
    }
}
