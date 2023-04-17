<?php

namespace RichiesteBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/bonifiche")
 */
class BonificheController extends BaseController {
    /**
     * @Route("/bonifica_richieste_senza_versions", name="bonifica_richieste_senza_versions")
     */
    public function bonificaRichiesteSenzaVersionsAction() {
        $richieste = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->getRichiesteSenzaVersions();
        foreach ($richieste as $richiesta) {
            $gestore = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura());
            try {
                $gestore->creaOggettiVersions($richiesta);
                $this->getEm()->flush();
            } catch (\Exception $e) {
                $this->get("logger")->error($e->getMessage());
            }
        }
        return $this->redirectToRoute("home");
    }
}
