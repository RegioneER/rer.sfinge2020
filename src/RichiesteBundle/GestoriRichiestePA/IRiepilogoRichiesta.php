<?php

namespace RichiesteBundle\GestoriRichiestePA;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface IRiepilogoRichiesta
{
    const ROTTA = 'procedura_pa_dettaglio_richiesta';

    public function __construct(ContainerInterface $container, Richiesta $richiesta);

    public function inizializzaAzioni();

    public function getBarraAvanzamento();

    /**
     * @return ISezioneRichiesta[]
     */
    public function getSezioni();

    public function addSezione(ISezioneRichiesta $sezione);

    /**
     * @return boolean
     */
    public function isValido();

    /**
     * @return null
     */
    public function validaRichiesta();

    /**
     * @return string[]
     */
    public function getMessaggi();

    public function getVociMenu();

    /**
     * @return Richiesta
     */
    public function getRichiesta();

    public function visualizzaSezione($nome_sezione, array $parametri);

    /**
     * @return boolean
     */
    public function isRichiestaDisabilitata();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return ContainerInterface
     */
    public function getContainer();
}
