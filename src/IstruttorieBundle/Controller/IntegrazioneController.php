<?php

namespace IstruttorieBundle\Controller;

use BaseBundle\Controller\BaseController;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntegrazioneController extends BaseController
{
    /**
     * @ParamConverter("procedura", options={"mapping": {"id_procedura" : "id"}})
     * @Route("/{id_procedura}/esportazione_cruscotto_comunicazioni_istruttoria", name="esportazione_cruscotto_comunicazioni_istruttoria")
     *
     * @param Procedura $procedura
     * @return StreamedResponse
     * @throws Exception
     */
    public function esportazioneCruscottoComunicazioniIstruttoriaAction(Procedura $procedura)
    {
        \ini_set("memory_limit", "512M");
        $this->get('base')->checkCsrf('token');
        return $this->get("gestore_integrazione")->getGestore($procedura)->esportazioneCruscottoComunicazioniIstruttoria($procedura);
    }
}
