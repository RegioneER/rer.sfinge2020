<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Revoche\RataRecuperoRepository")
 * @ORM\Table(name="rate_recuperi")
 */
class RataRecupero extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\Recupero", inversedBy="rate")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $recupero;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Assert\NotBlank
	 */
	protected $numero_incasso;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Assert\NotBlank
	 */
	protected $data_incasso;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 * @Assert\NotBlank
	 */
	protected $importo_interesse_legale;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 * @Assert\NotBlank
	 */
	protected $importo_interesse_mora;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_sanzione;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 * @Assert\NotBlank
	 */
	protected $importo_rata;

	/**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\RichiestaPagamento", mappedBy="rata_recupero")
     * @var RichiestaPagamento
     */
    protected $pagamento_monitoraggio;

	public function __construct(Recupero $recupero = null) {
		$this->recupero = $recupero;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getRecupero(): ?Recupero {
		return $this->recupero;
	}

	public function getNumeroIncasso() {
		return $this->numero_incasso;
	}

	public function getDataIncasso() {
		return $this->data_incasso;
	}

	public function getImportoInteresseLegale() {
		return $this->importo_interesse_legale;
	}

	public function getImportoInteresseMora() {
		return $this->importo_interesse_mora;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setRecupero($recupero) {
		$this->recupero = $recupero;
	}

	public function setNumeroIncasso($numero_incasso) {
		$this->numero_incasso = $numero_incasso;
	}

	public function setDataIncasso($data_incasso) {
		$this->data_incasso = $data_incasso;
	}

	public function setImportoInteresseLegale($importo_interesse_legale) {
		$this->importo_interesse_legale = $importo_interesse_legale;
	}

	public function setImportoInteresseMora($importo_interesse_mora) {
		$this->importo_interesse_mora = $importo_interesse_mora;
	}

	public function getImportoRata() {
		return $this->importo_rata;
	}

	public function setImportoRata($importo_rata) {
		$this->importo_rata = $importo_rata;
	}
	
	public function getImportoSanzione() {
		return $this->importo_sanzione;
	}

	public function setImportoSanzione($importo_sanzione) {
		$this->importo_sanzione = $importo_sanzione;
	}

	public function getPagamentoMonitoraggio(): ?RichiestaPagamento {
        return $this->pagamento_monitoraggio;
    }

    public function setPagamentoMonitoraggio(?RichiestaPagamento $p): self {
        $this->pagamento_monitoraggio = $p;

        return $this;
    }
}
