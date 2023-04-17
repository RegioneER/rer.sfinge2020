<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait Id {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }
}
