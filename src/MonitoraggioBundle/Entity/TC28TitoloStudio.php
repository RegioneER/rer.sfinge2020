<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:26
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC28TitoloStudioRepository")
 * @ORM\Table(name="tc28_titolo_studio")
 */
class TC28TitoloStudio extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $titolo_studio;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Assert\Length(max=500, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_titolo_studio;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $isced;

    /**
     * @return mixed
     */
    public function getTitoloStudio() {
        return $this->titolo_studio;
    }

    /**
     * @param mixed $titolo_studio
     */
    public function setTitoloStudio($titolo_studio) {
        $this->titolo_studio = $titolo_studio;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTitoloStudio() {
        return $this->descrizione_titolo_studio;
    }

    /**
     * @param mixed $descrizione_titolo_studio
     */
    public function setDescrizioneTitoloStudio($descrizione_titolo_studio) {
        $this->descrizione_titolo_studio = $descrizione_titolo_studio;
    }

    /**
     * @return mixed
     */
    public function getIsced() {
        return $this->isced;
    }

    /**
     * @param mixed $isced
     */
    public function setIsced($isced) {
        $this->isced = $isced;
    }
}
