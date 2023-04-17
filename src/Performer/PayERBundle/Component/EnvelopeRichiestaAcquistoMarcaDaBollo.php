<?php


namespace Performer\PayERBundle\Component;

use Performer\PayERBundle\Entity\RichiestaAcquistoMarcaDaBollo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EnvelopeRichiestaAcquistoMarcaDaBollo
 */
class EnvelopeRichiestaAcquistoMarcaDaBollo
{
    /**
     * @var string
     * @Assert\Length(max="11")
     */
    protected $codicePortale;

    /**
     * @var string
     * @Assert\Length(max="36")
     */
    protected $codiceServizio;

    /**
     * @var RichiestaAcquistoMarcaDaBollo
     * @Assert\Valid()
     */
    protected $richiestaAcquisto;

    /**
     * @var string
     * @Assert\Length(max="2083")
     */
    protected $urlRitorno;

    /**
     * @var string
     * @Assert\Length(max="2083")
     */
    protected $urlNotifica;

    /**
     * @var string
     * @Assert\Length(max="2083")
     */
    protected $urlIndietro;

    /**
     * @var string
     * @Assert\Choice(choices={"S", "N"})
     */
    protected $commitNotifica;

    /**
     * @var string
     * @Assert\Choice(choices={"S", "N"})
     */
    protected $notificaEsitiNegativi;

    /**
     * EnvelopeRichiestaAcquistoMarcaDaBollo constructor.
     * @param RichiestaAcquistoMarcaDaBollo $richiestaAcquisto
     */
    public function __construct(RichiestaAcquistoMarcaDaBollo $richiestaAcquisto)
    {
        $this->richiestaAcquisto = $richiestaAcquisto;
    }

    /**
     * @return string
     */
    public function getCodicePortale(): string
    {
        return $this->codicePortale;
    }

    /**
     * @param string $codicePortale
     * @return self
     */
    public function setCodicePortale(string $codicePortale): self
    {
        $this->codicePortale = $codicePortale;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceServizio(): string
    {
        return $this->codiceServizio;
    }

    /**
     * @param string $codiceServizio
     * @return self
     */
    public function setCodiceServizio(string $codiceServizio): self
    {
        $this->codiceServizio = $codiceServizio;
        return $this;
    }

    /**
     * @return RichiestaAcquistoMarcaDaBollo
     */
    public function getRichiestaAcquisto(): RichiestaAcquistoMarcaDaBollo
    {
        return $this->richiestaAcquisto;
    }

    /**
     * @param RichiestaAcquistoMarcaDaBollo $richiestaAcquisto
     * @return self
     */
    public function setRichiestaAcquisto(RichiestaAcquistoMarcaDaBollo $richiestaAcquisto): self
    {
        $this->richiestaAcquisto = $richiestaAcquisto;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlRitorno(): string
    {
        return $this->urlRitorno;
    }

    /**
     * @param string $urlRitorno
     * @return self
     */
    public function setUrlRitorno(string $urlRitorno): self
    {
        $this->urlRitorno = $urlRitorno;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlNotifica(): string
    {
        return $this->urlNotifica;
    }

    /**
     * @param string $urlNotifica
     * @return self
     */
    public function setUrlNotifica(string $urlNotifica): self
    {
        $this->urlNotifica = $urlNotifica;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlIndietro(): string
    {
        return $this->urlIndietro;
    }

    /**
     * @param string $urlIndietro
     * @return self
     */
    public function setUrlIndietro(string $urlIndietro): self
    {
        $this->urlIndietro = $urlIndietro;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommitNotifica(): string
    {
        return $this->commitNotifica;
    }

    /**
     * @param string $commitNotifica
     * @return self
     */
    public function setCommitNotifica(string $commitNotifica): self
    {
        $this->commitNotifica = $commitNotifica;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotificaEsitiNegativi(): string
    {
        return $this->notificaEsitiNegativi;
    }

    /**
     * @param string $notificaEsitiNegativi
     * @return self
     */
    public function setNotificaEsitiNegativi(string $notificaEsitiNegativi): self
    {
        $this->notificaEsitiNegativi = $notificaEsitiNegativi;
        return $this;
    }

    public function normalizeForSend()
    {
        $data = [
            'codicePortale' => $this->codicePortale,
            'idOperazionePortale' => $this->richiestaAcquisto->getId(),
            'urlRitorno' => $this->urlRitorno,
            'urlNotifica' => $this->urlNotifica,
            'urlIndietro' => $this->urlIndietro,
            'commitNotifica' => $this->commitNotifica,
            'notificaEsitiNegativi' => $this->notificaEsitiNegativi,
            'versante' => [
                'identificativo' => $this->richiestaAcquisto->getIdentificativoVersante(),
                'denominazione' => $this->richiestaAcquisto->getDenominazioneVersante(),
                'email' => $this->richiestaAcquisto->getEmailVersante()
            ],
            'dettagliMBD' => []
        ];

        foreach ($this->richiestaAcquisto->getAcquistoMarcaDaBollos() as $acquistoMarcaDaBollo) {
            $data['dettagliMBD'][] = [
                'codiceServizio' => $this->codiceServizio,
                'pagatore' => [ // Non sarebbe obbligatorio, ma se non lo inseriamo viene generato un errore 500
                    'identificativo' => $acquistoMarcaDaBollo->getIdentificativoPagatore() ?: $this->richiestaAcquisto->getIdentificativoVersante(),
                    'denominazione' => $acquistoMarcaDaBollo->getDenominazionePagatore() ?: $this->richiestaAcquisto->getDenominazioneVersante(),
                    'email' => $acquistoMarcaDaBollo->getEmailPagatore() ?: $this->richiestaAcquisto->getEmailVersante()
                ],
                'importo' => $acquistoMarcaDaBollo->getMarcaDaBollo()->getImporto(),
                'tipoBollo' => $acquistoMarcaDaBollo->getMarcaDaBollo()->getTipo()->getId(),
                'nomeDocumento' => $acquistoMarcaDaBollo->getNomeDocumento(),
                'hashDocumento' => $acquistoMarcaDaBollo->getHashDocumento(),
                'algoritmoHash' => "http://www.w3.org/2001/04/xmlenc#sha256", // Valore fisso, eventualmente sostituire con una costante
                'provinciaResidenza' => $acquistoMarcaDaBollo->getProvinciaResidenzaPagatore()
            ];
        }

        return $data;
    }
}