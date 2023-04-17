<?php


namespace Performer\PayERBundle\Event;


use Performer\PayERBundle\Entity\AcquistoMarcaDaBollo;
use Symfony\Component\EventDispatcher\Event;

class EbolloNotificaEsitoEvent extends Event
{
    public const NAME = 'payer.ebollo.notifica_esito';

    /**
     * @var AcquistoMarcaDaBollo
     */
    protected $acquistoMarcaDaBollo;

    /**
     * EbolloNotificaEsitoEvent constructor.
     * @param AcquistoMarcaDaBollo $acquistoMarcaDaBollo
     */
    public function __construct(AcquistoMarcaDaBollo $acquistoMarcaDaBollo)
    {
        $this->acquistoMarcaDaBollo = $acquistoMarcaDaBollo;
    }

    /**
     * @return AcquistoMarcaDaBollo
     */
    public function getAcquistoMarcaDaBollo(): AcquistoMarcaDaBollo
    {
        return $this->acquistoMarcaDaBollo;
    }

    /**
     * @param AcquistoMarcaDaBollo $acquistoMarcaDaBollo
     * @return self
     */
    public function setAcquistoMarcaDaBollo(AcquistoMarcaDaBollo $acquistoMarcaDaBollo): self
    {
        $this->acquistoMarcaDaBollo = $acquistoMarcaDaBollo;
        return $this;
    }
}