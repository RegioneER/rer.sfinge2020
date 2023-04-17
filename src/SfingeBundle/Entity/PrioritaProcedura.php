<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * PrioritaProcedura
 *
 * @ORM\Table(name="priorita_procedura")
 * @ORM\Entity(repositoryClass="SfingeBundle\Repository\PrioritaProceduraRepository")
 */
class PrioritaProcedura  extends EntityTipo
{


}
