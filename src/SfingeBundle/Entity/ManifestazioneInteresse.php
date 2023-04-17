<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\ManifestazioneInteresseRepository")
 * @ORM\Table(name="manifestazioni_interesse")
 */
class ManifestazioneInteresse extends Bando
{

    public function getTipo() {
        return "MANIFESTAZIONE_INTERESSE";
    }

}