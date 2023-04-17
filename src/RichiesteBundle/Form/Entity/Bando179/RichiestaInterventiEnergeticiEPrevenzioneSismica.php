<?php
namespace RichiesteBundle\Form\Entity\Bando179;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaInterventiEnergeticiEPrevenzioneSismica extends Richiesta
{
	/**
	 * @Assert\NotBlank()
	 */
	protected $tipo_soggetto;

	protected $contributo_a;
	protected $intervento_a;
	protected $intervento_b;
	protected $intervento_c;

    /**
     * @return mixed
     */
    public function getTipoSoggetto()
    {
        return $this->tipo_soggetto;
    }

    /**
     * @param mixed $tipo_soggetto
     */
    public function setTipoSoggetto($tipo_soggetto): void
    {
        $this->tipo_soggetto = $tipo_soggetto;
    }

    /**
     * @return mixed
     */
    public function getContributoA()
    {
        return $this->contributo_a;
    }

    /**
     * @param mixed $contributo_a
     */
    public function setContributoA($contributo_a): void
    {
        $this->contributo_a = $contributo_a;
    }

    /**
     * @return mixed
     */
    public function getInterventoA()
    {
        return $this->intervento_a;
    }

    /**
     * @param mixed $intervento_a
     */
    public function setInterventoA($intervento_a): void
    {
        $this->intervento_a = $intervento_a;
    }

    /**
     * @return mixed
     */
    public function getInterventoB()
    {
        return $this->intervento_b;
    }

    /**
     * @param mixed $intervento_b
     */
    public function setInterventoB($intervento_b): void
    {
        $this->intervento_b = $intervento_b;
    }

    /**
     * @return mixed
     */
    public function getInterventoC()
    {
        return $this->intervento_c;
    }

    /**
     * @param mixed $intervento_c
     */
    public function setInterventoC($intervento_c): void
    {
        $this->intervento_c = $intervento_c;
    }
}
