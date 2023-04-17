<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\VariazioneDatiBancari;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\VariazioneReferente;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use AttuazioneControlloBundle\Entity\VariazioneGenerica;
use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use AttuazioneControlloBundle\Service\Variazioni\GestoreVariazioniGenerica;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioni;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniConcreta;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniDatiBancari;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniPianoCosti;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniReferenti;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniSedeOperativa;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreVariazioniService {
    const NAMESPACE_GESTORE = 'AttuazioneControlloBundle\GestoriVariazioni';
    protected static $RADICE_CLASSE = [
        VariazionePianoCosti::class => 'GestoreVariazioniPianoCosti',
        VariazioneDatiBancari::class => 'GestoreVariazioniDatiBancari',
        VariazioneGenerica::class => 'GestoreVariazioniStandard',
        VariazioneSedeOperativa::class => 'GestoreVariazioniSedeOperativa',
        VariazioneReferente::class => 'GestoreVariazioniReferenti',
    ];
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @throws \Exception
     */
    public function getGestore(Procedura $procedura = null): IGestoreVariazioniBando {
        if (!is_null($procedura)) {
            $id_procedura = $procedura->getId();
        } else {
            $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->getSession()->get("id_richiesta");
            if (is_null($id_richiesta)) {
                throw new \Exception("Nessun id_richiesta indicato");
            }
            $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
            if (is_null($richiesta)) {
                throw new \Exception("Nessuna richiesta trovata");
            }
            $id_procedura = $richiesta->getProcedura()->getId();
        }

        //cerco un gestore per quel bando
        $nomeClasse = self::NAMESPACE_GESTORE . "\GestoreVariazioniBando_$id_procedura";
        try {
            $gestoreBandoReflection = new \ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (\ReflectionException $ex) {
        }

        return new GestoreVariazioniBase($this->container);
    }

    /**
     * @return IGestoreVariazioniConcreta|IGestoreVariazioniPianoCosti|IGestoreVariazioniDatiBancari|IGestoreVariazioniSedeOperativa|IGestoreVariazioniReferenti
     * @throws \LogicException
     */
    public function getGestoreVariazione(VariazioneRichiesta $variazione): IGestoreVariazioniConcreta {
        $className = \get_class($variazione);
        if (!\array_key_exists($className, self::$RADICE_CLASSE)) {
            throw new \LogicException('Template non presente per questa tipologia di variazione');
        }

        $gestoreGenerico = $this->getGestoreGenerica($variazione);
        $classeVariazione = self::NAMESPACE_GESTORE . '\\' . self::$RADICE_CLASSE[$className] . '_' . $variazione->getProcedura()->getId();
        if (\class_exists($classeVariazione)) {
            return new $classeVariazione($variazione, $gestoreGenerico, $this->container);
        }

        $classeVariazione = 'AttuazioneControlloBundle\Service\Variazioni\\' . self::$RADICE_CLASSE[$className] . 'Base';
        return new $classeVariazione($variazione, $gestoreGenerico, $this->container);
    }

    protected function getGestoreGenerica(VariazioneRichiesta $variazione): IGestoreVariazioni {
        $className = \get_class($variazione);
        if (!\array_key_exists($className, self::$RADICE_CLASSE)) {
            throw new \LogicException('Tipo di variazione non gestita');
        }

        $classeVariazione = self::NAMESPACE_GESTORE . "\GestoreVariazioniGenerica_" . $variazione->getProcedura()->getId();
        if (\class_exists($classeVariazione)) {
            return new $classeVariazione($variazione, $this->container);
        }

        return new GestoreVariazioniGenerica($variazione, $this->container);
    }
}
