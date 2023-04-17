<?php

namespace BaseBundle\Validator\Constraints;


use AnagraficheBundle\Entity\Persona;
use Symfony\Component\Validator\Constraint;

/**
 * Class CfConstraint
 *
 * @Annotation
 */
class CfSoggettoConstraint extends Constraint
{
    /**
     * @var string
     */
    public $messageCf = 'Il codice fiscale è errato.';

    /**
     * @var string
     */
    public $messagePiva = 'La partita iva è errata.';

    /**
     * @var string
     */
    public $messageLen = 'La lunghezza del dato non è corretta.';

    /** @var bool|mixed  */
    protected $obbligatorio = false;

    /**
     * @var Persona|null
     */
    protected $legaleRappresentante = null;

    public function __construct($options = null)
    {
        if($options['obbligatorio']) {
            $this->obbligatorio = $options['obbligatorio'];
        }

        if($options['legaleRappresentante'] && $options['legaleRappresentante'] instanceof Persona) {
            $this->legaleRappresentante = $options['legaleRappresentante'];
        }
    }

    /**
     * @return bool|mixed
     */
    public function getObbligatorio()
    {
        return $this->obbligatorio;
    }

    /**
     * @return mixed|null
     */
    public function getLegaleRappresentante(): ?Persona
    {
        return $this->legaleRappresentante;
    }

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'codice_fiscale_soggetto_checks';
    }
}