<?php

namespace AttuazioneControlloBundle\Service;

use RichiesteBundle\Entity\Richiesta;

interface IGestoreRichiesteATC {
    public function accettaContributo($id_richiesta);

    public function riepilogoRichiestaPA($id_richiesta);

    public function documentiRichiestaPA($richiesta);

    public function eliminaDocumentoAttuazione($richiesta, $id_documento);

    public function riepilogoBeneficiari($richiesta);

    public function getQuadroEconomico(Richiesta $richiesta);
}
