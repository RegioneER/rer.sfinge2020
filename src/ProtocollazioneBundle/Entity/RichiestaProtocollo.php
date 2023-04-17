<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
/**
 * RichiestaProtocollo
 *
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\RichiestaProtocolloRepository")
 * @ORM\Table(name="richieste_protocollo",
 *  indexes={
 *      @ORM\Index(name="idx_processo_id", columns={"processo_id"}),
 *      @ORM\Index(name="idx_istanza_processo_id", columns={"istanza_processo_id"})
 *  })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"GENERICA"="ProtocollazioneBundle\Entity\RichiestaProtocollo",
 *                        "INTEGRAZIONE"="ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazione",
 *                        "RISPOSTA_INTEGRAZIONE"="ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaIntegrazione",
 *                        "FINANZIAMENTO"="ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento",
 *                        "PAGAMENTO"="ProtocollazioneBundle\Entity\RichiestaProtocolloPagamento",
 *                        "VARIAZIONE"="ProtocollazioneBundle\Entity\RichiestaProtocolloVariazione",
 *                        "PROROGA"="ProtocollazioneBundle\Entity\RichiestaProtocolloProroga",
 *                        "INTEGRAZIONE_PAGAMENTO"="ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazionePagamento",
 *						  "RISPOSTA_INTEGRAZIONE_PAGAMENTO"="ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaIntegrazionePagamento",
 *						  "ESITO_ISTRUTTORIA"="ProtocollazioneBundle\Entity\RichiestaProtocolloEsitoIstruttoria",
 *						  "RISPOSTA_ESITO_ISTRUTTORIA" = "RichiestaProtocolloRispostaEsitoIstruttoria",
 *						  "ESITO_ISTRUTTORIA_PAGAMENTO"="ProtocollazioneBundle\Entity\RichiestaProtocolloEsitoIstruttoriaPagamento",
 *					      "RICHIESTA_CHIARIMENTI" = "ProtocollazioneBundle\Entity\RichiestaProtocolloRichiestaChiarimenti",
 *						  "RISPOSTA_RICHIESTA_CHIARIMENTI" = "ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaRichiestaChiarimenti",
 *  					  "COMUNICAZIONE_PROGETTO"="ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazioneProgetto",
 *						  "RISPOSTA_COMUNICAZIONE_PROGETTO" = "ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazioneProgetto",
 *						  "COMUNICAZIONE_ATTUAZIONE"="ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazioneAttuazione",
 *						  "RISPOSTA_COMUNICAZIONE_ATTUAZIONE" = "ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazioneAttuazione",
 *                        "COMUNICAZIONE_PAGAMENTO" = "ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazionePagamento",
 *                        "RISPOSTA_COMUNICAZIONE_PAGAMENTO"="ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazionePagamento"
 * })
 */
class RichiestaProtocollo extends EntityLoggabileCancellabile {

    const POST_PROTOCOLLAZIONE = 'POST_PROTOCOLLAZIONE';
    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Processo", inversedBy="richieste_protocollo")
     * @ORM\JoinColumn(name="processo_id", referencedColumnName="id")
     */
    protected $processo;

    /**
     * @ORM\ManyToOne(targetEntity="IstanzaProcesso", inversedBy="richieste_protocollo")
     * @ORM\JoinColumn(name="istanza_processo_id", referencedColumnName="id")
     */
    protected $istanza_processo;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="richieste_protocollo")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id")
     */
    protected $procedura;
    protected $tipo;

    /**
     * @var \DateTime $data_creazione
     *
     * @ORM\Column(name="data_creazione_richiesta", type="datetime", nullable=true)
     */
    protected $data_creazione_richiesta;

    /**
     * @var \DateTime $data_invio_PA
     *
     * @ORM\Column(name="data_invio_PA", type="datetime", nullable=true)
     */
    protected $data_invio_PA;

    /**
     * @var string $oggetto
     *
     * @ORM\Column(name="oggetto", type="text", nullable=true)
     */
    protected $oggetto;

    /**
     * @var string $stato
     *
     * @ORM\Column(name="stato", type="string", length=255, nullable=true)
     */
    protected $stato;

    /**
     * @var integer $fase
     *
     * @ORM\Column(name="fase", type="integer", nullable=true)
     */
    private $fase;

    /**
     * @var integer $esito_fase
     *
     * @ORM\Column(name="esito_fase", type="integer", nullable=true)
     */
    protected $esito_fase;

    /**
     * @var string $fascicolo
     *
     * @ORM\Column(name="fascicolo", type="string", length=45, nullable=true)
     */
    protected $fascicolo;

    /**
     * @var string $anno_pg
     *
     * @ORM\Column(name="anno_pg", type="string", length=50, nullable=true)
     * @Assert\NotNull(groups={"legge14"})
     * @Assert\Regex(pattern="/^\d{4}$/", message="sfinge.monitoraggio.invalidNumber", groups={"legge14"})
     */
    protected $anno_pg;

    /**
     * @var \DateTime $data_pg
     *
     * @ORM\Column(name="data_pg", type="datetime", nullable=true)
     */
    protected $data_pg;

    /**
     * @var string $num_pg
     *
     * @ORM\Column(name="num_pg", type="string", length=50, nullable=true)
     * @Assert\NotNull( groups={"legge14"})
     */
    protected $num_pg;
    
    /**
     * @var string $anno_pg_validazione
     *
     * @ORM\Column(name="anno_pg_validazione", type="string", length=50, nullable=true)
     */
    protected $anno_pg_validazione;


    /**
     * @var string $num_pg_validazione
     *
     * @ORM\Column(name="num_pg_validazione", type="string", length=50, nullable=true)
     */
    protected $num_pg_validazione;

    /**
     * @var string $oggetto_pg
     *
     * @ORM\Column(name="oggetto_pg", type="text", nullable=true)
     */
    protected $oggetto_pg;

    /**
     * @var string $registro_pg
     *
     * @ORM\Column(name="registro_pg", type="string", length=1024, nullable=true)
     * @Assert\NotNull( groups={"legge14"})
     */
    protected $registro_pg;
    
    /**
     * @var \DateTime $registro_pg_validazione
     *
     * @ORM\Column(name="registro_pg_validazione", type="string", length=50, nullable=true)
     */
    protected $registro_pg_validazione;

    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloDocumento", mappedBy="richiesta_protocollo")
     */
    protected $richiesta_protocollo_documenti;
    
    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\EmailProtocollo", mappedBy="richiestaProtocollo")
     * @var Collection|EmailProtocollo[]
     */
    protected $emailProtocollo;
    
    function __construct() {
        $this->richiesta_protocollo_documenti = new ArrayCollection();
        $this->emailProtocollo = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getProcesso() {
        return $this->processo;
    }

    public function getIstanzaProcesso() {
        return $this->istanza_processo;
    }

    public function getProcedura() {
        return $this->procedura;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getDataCreazioneRichiesta() {
        return $this->data_creazione_richiesta;
    }

    function getDataInvioPA() {
        return $this->data_invio_PA;
    }

    public function getOggetto() {
        return $this->oggetto;
    }

    public function getStato() {
        return $this->stato;
    }

    public function getFase() {
        return $this->fase;
    }

    public function getEsitoFase() {
        return $this->esito_fase;
    }

    public function getFascicolo() {
        return $this->fascicolo;
    }

    public function getAnno_pg() {
        return $this->anno_pg;
    }
    
    public function getAnnoPg() {
        return $this->getAnno_pg();
    }

    public function getData_pg() {
        return $this->data_pg;
    }
    
    public function getDataPg() {
        return $this->getData_pg();
    }

    public function getNum_pg() {
        return $this->num_pg;
    }

    public function getNumPg() {
        return $this->getNum_pg();
    }
    
    public function getOggetto_pg() {
        return $this->oggetto_pg;
    }
    
    public function getOggettoPg() {
        return $this->getOggetto_pg();
    }

    public function getRegistro_pg() {
        return $this->registro_pg;
    }
    
    public function getRegistroPg() {
        return $this->getRegistro_pg();
    }

    public function getRichiestaProtocolloDocumenti() {
        return $this->richiesta_protocollo_documenti;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setProcesso($processo) {
        $this->processo = $processo;
    }

    public function setIstanzaProcesso($istanza_processo) {
        $this->istanza_processo = $istanza_processo;
    }

    public function setProcedura($procedura) {
        $this->procedura = $procedura;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setDataCreazioneRichiesta($data_creazione) {
        $this->data_creazione_richiesta = $data_creazione;
    }

    function setDataInvioPA($data_invio_PA) {
        $this->data_invio_PA = $data_invio_PA;
    }

    public function setOggetto($oggetto) {
        $this->oggetto = $oggetto;
    }

    public function setStato($stato) {
        $this->stato = $stato;
    }

    public function setFase($fase) {
        $this->fase = $fase;

        return $this;
    }

    public function setEsitoFase($esito_fase) {
        $this->esito_fase = $esito_fase;
    }

    public function setFascicolo($fascicolo) {
        $this->fascicolo = $fascicolo;
    }

    public function setAnno_pg($anno_pg) {
        $this->anno_pg = $anno_pg;
    }
    
    public function setAnnoPg($anno_pg) {
        $this->setAnno_pg($anno_pg);
    }

    public function setData_pg($data_pg) {
        $this->data_pg = $data_pg;
    }
    
    public function setDataPg($data_pg) {
        $this->setData_pg($data_pg);
    }

    public function setNum_pg($num_pg) {
        $this->num_pg = $num_pg;
    }
    
    public function setNumPg($num_pg) {
        $this->setNum_pg($num_pg);
    }

    public function setOggetto_pg($oggetto_pg) {
        $this->oggetto_pg = $oggetto_pg;
    }
    
    public function setOggettoPg($oggetto_pg) {
        $this->setOggetto_pg($oggetto_pg);
    }

    public function setRegistro_pg($registro_pg) {
        $this->registro_pg = $registro_pg;
    }
    
    public function setRegistroPg($registro_pg) {
        $this->setRegistro_pg($registro_pg);
    }
    
    function getAnnoPgValidazione() {
        return $this->anno_pg_validazione;
    }

    function getRegistroPgValidazione() {
        return $this->registro_pg_validazione;
    }

    function getNumPgValidazione() {
        return $this->num_pg_validazione;
    }

    function setAnnoPgValidazione($anno_pg_validazione) {
        $this->anno_pg_validazione = $anno_pg_validazione;
    }

    function setRegistroPgValidazione($registro_pg_validazione) {
        $this->registro_pg_validazione = $registro_pg_validazione;
    }

    function setNumPgValidazione($num_pg_validazione) {
        $this->num_pg_validazione = $num_pg_validazione;
    }

    public function setRichiestaProtocolloDocumenti($richiesta_protocollo_documenti) {
        $this->richiesta_protocollo_documenti = $richiesta_protocollo_documenti;
    }

    public function addRichiestaProtocolloDocumento($RichiestaProtocolloDocumento) {
        $this->richiesta_protocollo_documenti->add($RichiestaProtocolloDocumento);
    }
    
    /**
     * @return EmailProtocollo[]|Collection
     */
    function getEmailProtocollo(): Collection {
        return $this->emailProtocollo;
    }

    function setEmailProtocollo(Collection $emailProtocollo): self {
        $this->emailProtocollo = $emailProtocollo;

        return $this;
    }

    function addEmailProtocollo(EmailProtocollo $emailProtocollo): self {
        $emailProtocollo->setRichiestaProtocollo($this);
        $this->emailProtocollo->add($emailProtocollo);

        return $this;
    }
    
    function isPostProtocollazione(): bool {
        return $this->stato == 'POST_PROTOCOLLAZIONE';
    }
    
    function getProtocollo(): string {
        if(!is_null($this->num_pg)){
            return $this->registro_pg . "/" . $this->anno_pg . "/" . $this->num_pg;
        }else{
            return '-';
        }
    }

}
