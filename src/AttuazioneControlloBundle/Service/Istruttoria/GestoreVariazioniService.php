<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\VariazioneDatiBancari;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\VariazioneReferente;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use AttuazioneControlloBundle\Entity\VariazioneGenerica;
use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreVariazioniService {
    const NAMESPACE_GESTORE = 'AttuazioneControlloBundle\Istruttoria\GestoriVariazioni';
    protected static $RADICE_CLASSE = [
        VariazionePianoCosti::class => 'GestoreVariazioniPianoCosti',
        VariazioneDatiBancari::class => 'GestoreVariazioniDatiBancari',
        VariazioneGenerica::class => 'GestoreVariazioniGeneriche',
        VariazioneSedeOperativa::class => 'GestoreVariazioniSedeOperativa',
        VariazioneReferente::class => 'GestoreVariazioniReferente',
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getGestore(VariazioneRichiesta $variazione): IGestoreVariazioni {
        $className = \get_class($variazione);
        if (!\array_key_exists($className, self::$RADICE_CLASSE)) {
            throw new \LogicException('Tipo di variazione non gestita');
        }
        $classeVariazione = self::NAMESPACE_GESTORE . "\\" . self::$RADICE_CLASSE[$className] . "_" . $variazione->getProcedura()->getId();
        if (\class_exists($classeVariazione)) {
            return new $classeVariazione($variazione, $this->container);
        }
        $classeVariazione = 'AttuazioneControlloBundle\Service\Istruttoria\Variazioni\\' . self::$RADICE_CLASSE[$className] . 'Base';

        return new $classeVariazione($variazione, $this->container);
    }
}
