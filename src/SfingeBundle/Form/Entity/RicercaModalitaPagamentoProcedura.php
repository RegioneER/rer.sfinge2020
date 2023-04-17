<?php

namespace SfingeBundle\Form\Entity;

use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use BaseBundle\Service\AttributiRicerca;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Form\RicercaModalitaPagamentoProceduraType;

class RicercaModalitaPagamentoProcedura extends AttributiRicerca {
    /**
     * @var Procedura
     */
    public $procedura;

    /**
     * @var ModalitaPagamento
     */
    public $modalita;

    public function getType() {
        return RicercaModalitaPagamentoProceduraType::class;
    }

    public function getNomeRepository() {
        return ModalitaPagamentoProcedura::class;
    }

    public function getNomeMetodoRepository() {
        return "getRicercaModalita";
    }

    public function getNumeroElementiPerPagina() {
        return null;
    }

    public function getNomeParametroPagina() {
        return "page";
    }
}
