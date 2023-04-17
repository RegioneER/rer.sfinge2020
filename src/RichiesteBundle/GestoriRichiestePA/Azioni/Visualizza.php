<?php

namespace RichiesteBundle\GestoriRichiestePA\Azioni;

use RichiesteBundle\GestoriRichiestePA\Azione;
use Symfony\Component\Routing\RouterInterface;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Visualizza extends Azione
{
    const NOME_AZIONE = 'visualizza';
    
    public function __construct(RouterInterface $router, IRiepilogoRichiesta $riepilogo, $nome_azione = self::NOME_AZIONE)
    {
        parent::__construct($router, $riepilogo, $nome_azione);
        $this->titolo = 'Visualizza';
    }

    /**
     * { @inheritdoc }
     */
    public function isVisibile(){
        return true;
    }

    /**
     * @return RedirectResponse
     */
    public function getRisultatoEsecuzione()
    {
        if(\is_null($this->getRichiesta()->getStato())){
            return $this->redirectToRoute('procedura_pa_nuova_richiesta', array('id_richiesta' => $this->getRichiesta()->getId()));
        }
        return $this->redirect($this->riepilogo->getUrl());
    }

}
