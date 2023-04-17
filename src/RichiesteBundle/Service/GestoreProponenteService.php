<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 21/01/16
 * Time: 17:23
 */

namespace RichiesteBundle\Service;

use Exception;
use ReflectionClass;
use ReflectionException;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreProponenteService
{
    protected $container;

    /**
     * GestoreProponenteService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Procedura|null $procedura
     * @return object|IGestoreProponenti
     * @throws Exception
     */
    public function getGestore(Procedura $procedura = null)
    {
        if (!is_null($procedura)) {
            $id_bando = $procedura->getId();
        } else {
            $id_bando = $this->container->get("request_stack")->getCurrentRequest()->get("id_bando");
            if (is_null($id_bando)) {
                $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
                if (is_null($id_richiesta)) {
                    throw new Exception("Nessun id_richiesta indicato");
                }
                $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
                if (is_null($richiesta)) {
                    throw new Exception("Nessuna richiesta trovata");
                }
                $id_bando = $richiesta->getProcedura()->getId();
            }
        }


        //cerco un gestore per quel bando
        $nomeClasse = "RichiesteBundle\GestoriProponenti\GestoreProponenteBando_".$id_bando;
        try {
            $gestoreBandoReflection = new ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (ReflectionException $ex) {

        }

        return new GestoreProponenteBase($this->container);
    }
}