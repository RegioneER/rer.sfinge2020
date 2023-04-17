<?php

namespace AnagraficheBundle\Service;

use AnagraficheBundle\Entity\Personale;
use BaseBundle\Service\BaseService;
use SfingeBundle\Entity\Utente;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;


class InserimentoPersonale extends BaseService
{
    private $em;

    /**
     * InserimentoPersona constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->em = $this->container->get('doctrine')->getManager();
    }

}