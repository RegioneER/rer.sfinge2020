<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Description of Fascicolo
 *
 * @author aturdo
 * 
 * @ORM\Entity()
 * @ORM\Table(name="fascicoli_fascicoli")
 */
class Fascicolo {
	
    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var Pagina $indice
	 * 
	 * @ORM\OneToOne(targetEntity="Pagina", inversedBy="fascicolo", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */	
	protected $indice;
	
	/**
	 * @Assert\NotNull
	 * @Assert\Length(max=255, maxMessage="Campo limitato a 255 caratteri")
	 */
	protected $titolo;
	
	/**
	 * @Assert\Regex(pattern="/^[a-z0-9_]+$/", message="L'alias può contenere solo lettere minuscole, cifre ed underscore")
	 * @Assert\NotNull
	 * @Assert\Length(max=255, maxMessage="Campo limitato a 255 caratteri")
	 */
	protected $alias;
	
	protected $callback;
	
	/**
	 * @var string $template
	 * 
	 * @ORM\Column(name="template", type="string", length=255, nullable=false)
	 * @Assert\Length(max=255, maxMessage="Campo limitato a 255 caratteri")
	 * @Assert\NotNull
	 */
	protected $template;	

	public function __construct() {
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getIndice() {
		return $this->indice;
	}

	public function setIndice($indice) {
		$this->indice = $indice;
	}
	
	public function getTitolo() {
		return $this->titolo;
	}

	public function setTitolo($titolo) {
		$this->titolo = $titolo;
	}
	
	function getAlias() {
		return $this->alias;
	}

	function setAlias($alias) {
		$this->alias = $alias;
	}
	
	function getCallback() {
		return $this->callback;
	}

	function setCallback($callback) {
		$this->callback = $callback;
	}
	
	public function getTemplate() {
		return $this->template;
	}

	public function setTemplate($template) {
		$this->template = $template;
	}
	
	public function __clone() {
		if($this->id){
			$indiceClonato = clone $this->indice;
			$indiceClonato->setFascicolo($this);
			$alias = $indiceClonato->getAlias()."_cloned";
			$indiceClonato->setAlias($alias);
			$this->setIndice($indiceClonato);
		}
	}
	
	public function getByAlias(string $alias = ''): ?Pagina {
		return $this->checkAlias($alias) ?
				$this->getIndice() :
				null;
	}

	public function checkAlias(string $alias): bool
	{
		return $this->indice && $this->indice->getAlias() == $alias;
	}
    
	public function getAllLeafPath() {	
        $indice = $this->getIndice();
        return $indice->getAllLeafPath();       
	}      

}
