<?php

namespace RichiesteBundle\Service;

use BaseBundle\Service\BaseService;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SfingeBundle\Entity\Procedura;
use RichiesteBundle\Entity\Richiesta;

class GestoreRichiestaPAService extends BaseService implements ContainerAwareInterface
{
    const NAMESPACE_GESTORE = '\RichiesteBundle\GestoriRichiestePA\\';
    const PREFISSO_CLASSE = 'GestoreRichiestePA_';
    const SUFFISSO_CLASSE_BASE = 'Base';

    /**
     * @var Procedura
     */
    protected $procedura;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @throws \Exception
     */
    public function getGestore(Richiesta $richiesta): IGestoreRichiestaPA
    {
        $this->richiesta = $richiesta;
        $this->procedura = $this->richiesta->getProcedura();
        $id_procedura = $this->procedura->getId();

        try {
            return $this->instantiateGestore($id_procedura);
        } catch (\Exception $e) {
            throw new \Exception('Impossibile inizializzare gestore richieste PA', 0, $e);
        }
        throw new \Exception('Non dovrei trovarmi assolutamente qui!');
    }

    /**
     * @throws \Exception
     */
    private function instantiateGestore($nomeGestore): IGestoreRichiestaPA
    {
        $nomeGestore = self::NAMESPACE_GESTORE . self::PREFISSO_CLASSE . $nomeGestore;
        if (!class_exists($nomeGestore)) {
            $nomeGestore = self::NAMESPACE_GESTORE . self::PREFISSO_CLASSE . self::SUFFISSO_CLASSE_BASE;
        }
        $this->container->get('logger')->debug("Istanzio gestore $nomeGestore per procedura con ID ".$this->procedura->getId());
        return new $nomeGestore( $this->container, $this->richiesta);
    }
}
