<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:29
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC30DurataRicercaRepository")
 * @ORM\Table(name="tc30_durata_ricerca")
 */
class TC30DurataRicerca extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $durata_ricerca;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_durata_ricerca;

    /**
     * @return mixed
     */
    public function getDurataRicerca() {
        return $this->durata_ricerca;
    }

    /**
     * @param mixed $durata_ricerca
     */
    public function setDurataRicerca($durata_ricerca) {
        $this->durata_ricerca = $durata_ricerca;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneDurataRicerca() {
        return $this->descrizione_durata_ricerca;
    }

    /**
     * @param mixed $descrizione_durata_ricerca
     */
    public function setDescrizioneDurataRicerca($descrizione_durata_ricerca) {
        $this->descrizione_durata_ricerca = $descrizione_durata_ricerca;
    }
}
