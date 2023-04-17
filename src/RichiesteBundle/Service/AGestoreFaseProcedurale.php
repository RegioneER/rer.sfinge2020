<?php

namespace RichiesteBundle\Service;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use BaseBundle\Service\BaseService;
use Doctrine\ORM\EntityManagerInterface;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class AGestoreFaseProcedurale extends BaseService implements IGestoreFaseProcedurale {

    public function getSoggetto(){
        $soggetto = $this->getSession()->get(BaseController::SESSIONE_SOGGETTO);
        if(is_null($soggetto)){
            throw new \Exception("Soggetto non specificato");
        }
        $soggetto = $this->getEm()->merge($soggetto);
        return $soggetto;
    }

    /**
     * @return Procedura
     * @throws SfingeException
     */
    public function getProcedura()
    {
        $id_bando = $this->container->get("request_stack")->getCurrentRequest()->get("id_bando");
        if (is_null($id_bando)) {
            $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
            if (is_null($id_richiesta)) {
                throw new SfingeException("Nessun id_richiesta indicato");
            }
            $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
            if (is_null($richiesta)) {
                throw new SfingeException("Nessuna richiesta trovata");
            }
            return $richiesta->getProcedura();
        }
        throw new SfingeException("Nessuna richiesta trovata");
    }
}
