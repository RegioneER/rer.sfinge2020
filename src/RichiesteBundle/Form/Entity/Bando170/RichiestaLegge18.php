<?php


namespace RichiesteBundle\Form\Entity\Bando170;

use BaseBundle\Service\AttributiRicerca;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaLegge18 extends Richiesta
{
	/**
	 * @Assert\NotBlank()
	 */
	protected $tipo_soggetto;

	/**
	 * @Assert\NotBlank()
	 */    
	protected $tipo_istituto;


        /**
	 * @Assert\NotBlank()
	 */ 
	protected $tipo_intervento;

	public function getTipoSoggetto() {
            return $this->tipo_soggetto;
        }

        public function getTipoIstituto() {
            return $this->tipo_istituto;
        }

        public function getTipoIntervento() {
            return $this->tipo_intervento;
        }

        public function setTipoSoggetto($tipo_soggetto): void {
            $this->tipo_soggetto = $tipo_soggetto;
        }

        public function setTipoIstituto($tipo_istituto): void {
            $this->tipo_istituto = $tipo_istituto;
        }

        public function setTipoIntervento($tipo_intervento): void {
            $this->tipo_intervento = $tipo_intervento;
        }

}