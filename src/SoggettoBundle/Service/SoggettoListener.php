<?php

namespace SoggettoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use SoggettoBundle\Entity\Soggetto;

class SoggettoListener
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
    
    public function postUpdate(Soggetto $soggetto): void {
        $soggettoVersion = $this->versioning->creaSoggettoVersion($soggetto);

        $this->em->persist($soggettoVersion);
        $this->em->flush($soggettoVersion);
    }
}