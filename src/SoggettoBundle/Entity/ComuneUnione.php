<?php
namespace SoggettoBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use SfingeBundle\Entity\ComuneUnioniComuni;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\ComuneUnioneRepository")
 * @ORM\Table(
 *  indexes={
 *      @ORM\Index(name="idx_comune_unione_comune_id", columns={"comune_unione_comune_id"}),
 *  })
 */
class ComuneUnione extends Soggetto
{

    /**
     * @var ComuneUnioniComuni
     * @ORM\OneToOne(targetEntity="SfingeBundle\Entity\ComuneUnioniComuni")
     * @ORM\JoinColumn(name="comune_unione_comune_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    protected $comune_unione_comune;

    /**
     * @return ComuneUnioniComuni
     */
    public function getComuneUnioneComune()
    {
        return $this->comune_unione_comune;
    }

    /**
     * @param ComuneUnioniComuni $comune_unione_comune
     */
    public function setComuneUnioneComune(ComuneUnioniComuni $comune_unione_comune)
    {
        $this->comune_unione_comune = $comune_unione_comune;
    }

	public function getTipo() {
        return "COMUNE";
    }

}