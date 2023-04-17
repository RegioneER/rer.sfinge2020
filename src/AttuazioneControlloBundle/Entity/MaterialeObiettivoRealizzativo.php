<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="materiali_obiettivi_realizzativi")
 */
class MaterialeObiettivoRealizzativo extends EntityLoggabileCancellabile {
	
		/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\EstensionePagamento", inversedBy="materiali_or")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $estensione_pagamento;
	
	/**
	 * @ORM\Column(type="string", length=250, nullable=true)
	 */
	protected $tipo_materiale;
	
	/**
	 * @ORM\Column(type="string", length=250, nullable=true)
	 */
	protected $tipo_target;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $link;
	
	public function getId() {
		return $this->id;
	}

	public function getEstensionePagamento() {
		return $this->estensione_pagamento;
	}

	public function getTipoMateriale() {
		return $this->tipo_materiale;
	}

	public function getTipoTarget() {
		return $this->tipo_target;
	}

	public function getLink() {
		return $this->link;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setEstensionePagamento($estensione_pagamento) {
		$this->estensione_pagamento = $estensione_pagamento;
	}

	public function setTipoMateriale($tipo_materiale) {
		$this->tipo_materiale = $tipo_materiale;
	}

	public function setTipoTarget($tipo_target) {
		$this->tipo_target = $tipo_target;
	}

	public function setLink($link) {
		$this->link = $link;
	}



}
