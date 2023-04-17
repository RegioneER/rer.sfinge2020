<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity()
 * @ORM\Table(name="stati_procedura")
 */
class StatoProcedura extends EntityTipo
{
    const IN_CORSO = 'IN_CORSO';

}
