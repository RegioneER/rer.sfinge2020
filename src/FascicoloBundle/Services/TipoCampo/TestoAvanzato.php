<?php

namespace FascicoloBundle\Services\TipoCampo;

use FascicoloBundle\Form\Type\AdvancedTextType;

class TestoAvanzato extends AreaTesto {
    public function getType() {
        return AdvancedTextType::class;
    }
}
