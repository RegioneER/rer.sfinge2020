<?php

namespace SoggettoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use SoggettoBundle\Entity\Soggetto;

class SoggettoListenerPersist
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    
    /**
     * @var SoggettoVersioning
     */
    private $versioning;

    public function __construct(EntityManagerInterface $em, SoggettoVersioning $versioning){
        $this->em = $em;
        $this->versioning = $versioning;
    }
    
    public function postPersist(Soggetto $soggetto): void {
        $soggettoVersion = $this->versioning->creaSoggettoVersion($soggetto);

        $this->em->persist($soggettoVersion);
        $this->em->flush($soggettoVersion);
    }
}