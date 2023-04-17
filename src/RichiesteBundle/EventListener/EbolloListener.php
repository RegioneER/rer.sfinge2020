<?php
namespace RichiesteBundle\EventListener;

use Performer\PayERBundle\Event\EbolloNotificaEsitoEvent;
use RichiesteBundle\Entity\Richiesta;

class EbolloListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onNotificaEsito(EbolloNotificaEsitoEvent $event)
    {
        $em = $this->container->get("doctrine")->getManager();
        /** @var Richiesta $richiesta */
        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")
            ->findOneBy(['acquistoMarcaDaBollo' => $event->getAcquistoMarcaDaBollo()->getId()]);

        if ($event->getAcquistoMarcaDaBollo()->isPagamentoEseguito()) {
            $richiesta->setNumeroMarcaDaBolloDigitale($event->getAcquistoMarcaDaBollo()->getIuv());
            $em->persist($richiesta);
            $em->flush();
        }
    }
}
