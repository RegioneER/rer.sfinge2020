<?php

namespace SfingeBundle\Form\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PassaggioAttuazione {

    /**
     * @Assert\NotNull()
     */
    private $id_procedura;

    public function getIdProcedura() {
        return $this->id_procedura;
    }

    public function setIdProcedura($id_procedura): void {
        $this->id_procedura = $id_procedura;
    }


}