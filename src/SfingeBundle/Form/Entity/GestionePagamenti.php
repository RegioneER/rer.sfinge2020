<?php

namespace SfingeBundle\Form\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class GestionePagamenti {

    /**
     * @Assert\NotNull()
     */
    private $id_pagamento;

    public function getIdPagamento() {
        return $this->id_pagamento;
    }

    public function setIdPagamento($id_pagamento): void {
        $this->id_pagamento = $id_pagamento;
    }


}