<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Acquisizioni extends Procedura {

    public function getTipo() {
        return "ACQUISIZIONI";
    }

}