<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:43.
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\AP00AnagraficaProgettiRepository")
 * @ORM\Table(name="ap00_anagrafica_progetti")
 */
class AP00AnagraficaProgetti extends EntityEsportazione {
    use StrutturaCancellabile;
    use HasCodLocaleProgetto;
    use Id;

    const CODICE_TRACCIATO = 'AP00';
    const SEPARATORE = '|';
    const TITOLO_LEN = 500;
    const SINTESI_LEN = 1300;

    /**
     * @ORM\ManyToOne(targetEntity="TC5TipoOperazione")
     * @ORM\JoinColumn(name="tipo_operazione_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @var TC5TipoOperazione
     */
    protected $tc5_tipo_operazione;

    /**
     * @ORM\ManyToOne(targetEntity="TC6TipoAiuto")
     * @ORM\JoinColumn(name="tipo_aiuto_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @var TC6TipoAiuto
     */
    protected $tc6_tipo_aiuto;

    /**
     * @ORM\ManyToOne(targetEntity="TC48TipoProceduraAttivazioneOriginaria")
     * @ORM\JoinColumn(name="tip_proc_att_orig_id", referencedColumnName="id", nullable=true)
     * @var TC48TipoProceduraAttivazioneOriginaria
     */
    protected $tc48_tipo_procedura_attivazione_originaria;

    /**
     * @ORM\Column(type="string", length=500, nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="500", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $titolo_progetto;

    /**
     * @ORM\Column(type="string", length=1300, nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="1300", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $sintesi_prg;

    /**
     * @ORM\Column(type="string", length=15, nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="15", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $cup;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_inizio;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_fine_prevista;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_fine_effettiva;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(max="30", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $codice_proc_att_orig;

    /**
     * @return TC5TipoOperazione
     */
    public function getTc5TipoOperazione() {
        return $this->tc5_tipo_operazione;
    }

    /**
     * @param TC5TipoOperazione $tc5_tipo_operazione
     * @return AP00AnagraficaProgetti
     */
    public function setTc5TipoOperazione($tc5_tipo_operazione) {
        $this->tc5_tipo_operazione = $tc5_tipo_operazione;
        return $this;
    }

    /**
     * @return TC6TipoAiuto
     */
    public function getTc6TipoAiuto() {
        return $this->tc6_tipo_aiuto;
    }

    /**
     * @param mixed $tc6_tipo_aiuto
     * @return AP00AnagraficaProgetti
     */
    public function setTc6TipoAiuto($tc6_tipo_aiuto) {
        $this->tc6_tipo_aiuto = $tc6_tipo_aiuto;
        return $this;
    }

    /**
     * @return TC48TipoProceduraAttivazioneOriginaria
     */
    public function getTc48TipoProceduraAttivazioneOriginaria() {
        return $this->tc48_tipo_procedura_attivazione_originaria;
    }

    /**
     * @param TC48TipoProceduraAttivazioneOriginaria $tc48_tipo_procedura_attivazione_originaria
     * @return AP00AnagraficaProgetti
     */
    public function setTc48TipoProceduraAttivazioneOriginaria($tc48_tipo_procedura_attivazione_originaria) {
        $this->tc48_tipo_procedura_attivazione_originaria = $tc48_tipo_procedura_attivazione_originaria;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitoloProgetto() {
        return $this->titolo_progetto;
    }

    /**
     * @param string $titolo_progetto
     * @return AP00AnagraficaProgetti
     */
    public function setTitoloProgetto($titolo_progetto) {
        $this->titolo_progetto = $titolo_progetto;
        return $this;
    }

    /**
     * @return string
     */
    public function getSintesiPrg() {
        return $this->sintesi_prg;
    }

    /**
     * @param string $sintesi_prg
     * @return AP00AnagraficaProgetti
     */
    public function setSintesiPrg($sintesi_prg) {
        $this->sintesi_prg = $sintesi_prg;
        return $this;
    }

    /**
     * @return string
     */
    public function getCup() {
        return $this->cup;
    }

    /**
     * @param string $cup
     * @return AP00AnagraficaProgetti
     */
    public function setCup($cup) {
        $this->cup = $cup;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataInizio() {
        return $this->data_inizio;
    }

    /**
     * @param \DateTime $data_inizio
     * @return AP00AnagraficaProgetti
     */
    public function setDataInizio($data_inizio) {
        $this->data_inizio = $data_inizio;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataFinePrevista() {
        return $this->data_fine_prevista;
    }

    /**
     * @param \DateTime $data_fine_prevista
     * @return AP00AnagraficaProgetti
     */
    public function setDataFinePrevista($data_fine_prevista) {
        $this->data_fine_prevista = $data_fine_prevista;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataFineEffettiva() {
        return $this->data_fine_effettiva;
    }

    /**
     * @param \DateTime $data_fine_effettiva
     */
    public function setDataFineEffettiva($data_fine_effettiva) {
        $this->data_fine_effettiva = $data_fine_effettiva;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceProcAttOrig() {
        return $this->codice_proc_att_orig;
    }

    /**
     * @param string $codice_proc_att_orig
     * @return AP00AnagraficaProgetti
     */
    public function setCodiceProcAttOrig($codice_proc_att_orig) {
        $this->codice_proc_att_orig = $codice_proc_att_orig;
        return $this;
    }

    public function getTracciato() {
        return (\is_null($this->getCodLocaleProgetto()) ? '' : $this->getCodLocaleProgetto())
            . self::SEPARATORE . (\is_null($this->getTitoloProgetto()) ? '' : $this->getTitoloProgetto())
            . self::SEPARATORE . (\is_null($this->getSintesiPrg()) ? '' : $this->getSintesiPrg())
            . self::SEPARATORE . (\is_null($this->getTc5TipoOperazione()) ? '' : $this->getTc5TipoOperazione()->getTipoOperazione())
            . self::SEPARATORE . (\is_null($this->getCup()) ? '' : $this->getCup())
            . self::SEPARATORE . (\is_null($this->getTc6TipoAiuto()) ? '' : $this->getTc6TipoAiuto()->getTipoAiuto())
            . self::SEPARATORE . (\is_null($this->getDataInizio()) ? '' : $this->getDataInizio()->format('d/m/Y'))
            . self::SEPARATORE . (\is_null($this->getDataFinePrevista()) ? '' : $this->getDataFinePrevista()->format('d/m/Y'))
            . self::SEPARATORE . (\is_null($this->getDataFineEffettiva()) ? '' : $this->getDataFineEffettiva()->format('d/m/Y'))
            . self::SEPARATORE . (\is_null($this->getTc48TipoProceduraAttivazioneOriginaria()) ? '' : $this->getTc48TipoProceduraAttivazioneOriginaria()->getTipProcAttOrig())
            . self::SEPARATORE . (\is_null($this->getCodiceProcAttOrig()) ? '' : $this->getCodiceProcAttOrig())
            . self::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? '' : $this->getFlgCancellazione());
    }
}
