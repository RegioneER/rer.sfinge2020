<?php


namespace Performer\PayERBundle\Service;


use Performer\PayERBundle\Entity\RichiestaAcquistoMarcaDaBollo;
use Symfony\Component\HttpFoundation\Response;

interface EBolloInterface
{
    /**
     * @return string
     */
    public function getUrlAcquistoCarrelloMbd(): string;

    /**
     * @return string
     */
    public function getUrlInvioCarrelloMbd(): string;

    /**
     * @return string
     */
    public function getUrlEsitoCarrelloMbd(): string;

    public function send(RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo);

    public function esitoCarrello(string $pid);

    public function confermaRicezioneEsito(RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo): Response;

    public function aggiornaEsito(RichiestaAcquistoMarcaDaBollo $richiestaAcquistoMarcaDaBollo);
}