<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Processo
 *
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\ProcessoRepository")
 * @ORM\Table(name="processi",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="uniq_codice", columns={"codice"})    
 * })
 */
class Processo extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var int $attivo
	 *
	 * @ORM\Column(name="attivo", type="smallint")
	 */
	private $attivo = 0;

	/**
	 * @var string $codice
	 *
	 * @ORM\Column(name="codice", type="string", length=50)
	 */
	private $codice;

	/**
	 * @var string $descrizione
	 *
	 * @ORM\Column(name="descrizione", type="string", length=1024, nullable=true)
	 */
	private $descrizione;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocollo", mappedBy="processo")
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\IstanzaProcesso", mappedBy="processo")
	 */
	protected $istanze_processi;

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set attivo
	 *
	 * @param int $attivo
	 *
	 * @return Processo
	 */
	public function setAttivo($attivo) {
		$this->attivo = $attivo;

		return $this;
	}

	/**
	 * Get attivo
	 *
	 * @return int
	 */
	public function getAttivo() {
		return $this->attivo;
	}

	/**
	 * Set codice
	 *
	 * @param string $codice
	 *
	 * @return Processo
	 */
	public function setCodice($codice) {
		$this->codice = $codice;

		return $this;
	}

	/**
	 * Get codice
	 *
	 * @return string
	 */
	public function getCodice() {
		return $this->codice;
	}

	/**
	 * Set descrizione
	 *
	 * @param string $descrizione
	 *
	 * @return Processo
	 */
	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;

		return $this;
	}

	/**
	 * Get descrizione
	 *
	 * @return string
	 */
	public function getDescrizione() {
		return $this->descrizione;
	}

	public function __construct() {
		$this->richieste_protocollo = new ArrayCollection();
	}

	/**
	 * Add richiestaProtocolloId
	 *
	 * @param \ProtocollazioneBundle\Entity\RichiestaProtocollo $richiestaProtocollo
	 *
	 * @return Processo
	 */
	public function addRichiesteProtocollo(\ProtocollazioneBundle\Entity\RichiestaProtocollo $richiestaProtocollo) {
		$this->richieste_protocollo[] = $richiestaProtocollo;

		return $this;
	}

	/**
	 * Remove richiestaProtocolloId
	 *
	 * @param \ProtocollazioneBundle\Entity\RichiestaProtocollo $richiestaProtocollo
	 */
	public function removeRichiesteProtocollo(\ProtocollazioneBundle\Entity\RichiestaProtocollo $richiestaProtocollo) {
		$this->richieste_protocollo->removeElement($richiestaProtocollo);
	}

	/**
	 * Get richiestaProtocolloId
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getRichiesteProtocollo() {
		return $this->richieste_protocollo;
	}

	/**
	 * Add istanzaProcessoId
	 *
	 * @param \ProtocollazioneBundle\Entity\IstanzaProcesso $istanzaProcessoId
	 *
	 * @return Processo
	 */
	public function addIstanzaProcesso(\ProtocollazioneBundle\Entity\IstanzaProcesso $istanzaProcesso) {
		$this->istanze_processi[] = $istanzaProcesso;

		return $this;
	}

	/**
	 * Remove istanzaProcessoId
	 *
	 * @param \ProtocollazioneBundle\Entity\IstanzaProcesso $istanzaProcessoId
	 */
	public function removeIstanzaProcessoId(\ProtocollazioneBundle\Entity\IstanzaProcesso $istanzaProcesso) {
		$this->istanze_processi->removeElement($istanzaProcesso);
	}
}
