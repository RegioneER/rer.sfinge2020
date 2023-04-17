<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:13
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC17ModalitaFormativaRepository")
 * @ORM\Table(name="tc17_modalita_formativa")
 */
class TC17ModalitaFormativa extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_modalita_formativa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_modalita_formativa_sottoclasse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_classe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_macro_categoria;

    /**
     * @return mixed
     */
    public function getCodModalitaFormativa() {
        return $this->cod_modalita_formativa;
    }

    /**
     * @param mixed $cod_modalita_formativa
     */
    public function setCodModalitaFormativa($cod_modalita_formativa) {
        $this->cod_modalita_formativa = $cod_modalita_formativa;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneModalitaFormativaSottoclasse() {
        return $this->descrizione_modalita_formativa_sottoclasse;
    }

    /**
     * @param mixed $descrizione_modalita_formativa_sottoclasse
     */
    public function setDescrizioneModalitaFormativaSottoclasse($descrizione_modalita_formativa_sottoclasse) {
        $this->descrizione_modalita_formativa_sottoclasse = $descrizione_modalita_formativa_sottoclasse;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneClasse() {
        return $this->descrizione_classe;
    }

    /**
     * @param mixed $descrizione_classe
     */
    public function setDescrizioneClasse($descrizione_classe) {
        $this->descrizione_classe = $descrizione_classe;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneMacroCategoria() {
        return $this->descrizione_macro_categoria;
    }

    /**
     * @param mixed $descrizione_macro_categoria
     */
    public function setDescrizioneMacroCategoria($descrizione_macro_categoria) {
        $this->descrizione_macro_categoria = $descrizione_macro_categoria;
    }
}
