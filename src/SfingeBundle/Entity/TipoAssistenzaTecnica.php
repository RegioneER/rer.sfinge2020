<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipi_assistenza_tecnica")
 */
class TipoAssistenzaTecnica extends EntityTipo {
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $checklist_pagamento;
	
	function getChecklistPagamento() {
		return $this->checklist_pagamento;
	}

	function setChecklistPagamento($checklist_pagamento) {
		$this->checklist_pagamento = $checklist_pagamento;
	}

}
