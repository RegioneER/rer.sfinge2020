<?php

namespace DocumentoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AnagraficheBundle\Entity\Utente;

/**
 * DocumentoBundle\Entity\Documento
 *
 * @ORM\Entity(repositoryClass="DocumentoBundle\Entity\DocumentoRepository")
 * @ORM\Table(name="documenti",
 *  indexes={
 *      @ORM\Index(name="idx_tipologia_documento_id", columns={"tipologia_documento_id"}),
 *  }
 * )
 * @ORM\MappedSuperclass
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"FILE"="DocumentoBundle\Entity\DocumentoFile"})
 */
abstract class Documento extends EntityLoggabileCancellabile{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $nome_originale
     *
     * @ORM\Column(name="nome_originale", type="string", length=1024)
     */
    protected $nome_originale;

    /**
     * @var string $nome
     *
     * @ORM\Column(name="nome", type="string", length=1024)
     */
    protected $nome;

    /**
     * @var string $md5
     *
     * @ORM\Column(name="md5", type="string", length=255)
     */
    protected $md5;


    /**
     * @ORM\ManyToOne(targetEntity="TipologiaDocumento", cascade={"persist"})
     * @ORM\JoinColumn(name="tipologia_documento_id", referencedColumnName="id")
	 * @Assert\NotNull(message = "Selezionare una tipologia")
     */
    protected $tipologia_documento;

    public function __construct(?TipologiaDocumento $tipologia=null){
        $this->tipologia_documento = $tipologia;
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @param string $md5
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return string
     */
    public function getNomeOriginale()
    {
        return $this->nome_originale;
    }

    /**
     * @param string $nome_originale
     */
    public function setNomeOriginale($nome_originale)
    {
        $this->nome_originale = $nome_originale;
    }

    /**
     * @return TipologiaDocumento
     */
    public function getTipologiaDocumento()
    {
        return $this->tipologia_documento;
    }

    /**
     * @param TipologiaDocumento $tipologia_documento
     */
    public function setTipologiaDocumento($tipologia_documento)
    {
        $this->tipologia_documento = $tipologia_documento;
    }


}