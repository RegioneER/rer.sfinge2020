<?php

namespace FascicoloBundle\Services;

use FascicoloBundle\Entity\Campo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of Fascicolo
 *
 * @author aturdo
 */
class Fascicolo {
	/**
	 * @var ContainerInterface
	 */
	protected static $container;
	
	public function __construct(ContainerInterface $container) {
		self::$container = $container;
	}
	
	public function creaBreadcrumbFrammento($frammento, $accodaFrammento = false) {
		$breadcrumb = array();
		
		if ($accodaFrammento) {
			$breadcrumb[] = array("titolo" => "Frammento: ".$frammento, "url" => self::$container->get('router')->generate('modifica_frammento', array('id_frammento' => $frammento->getId())));
		}
			
		$cursoreFrammento = $frammento;
		while ($cursoreFrammento) {
			$cursorePagina = $cursoreFrammento->getPagina();
			if ($cursorePagina->getFascicolo()) {
				$titoloPagina = $cursorePagina->getTitolo();
				$urlPagina = self::$container->get('router')->generate('modifica_fascicolo', array('id_fascicolo' => $cursorePagina->getFascicolo()->getId()));
			} else {
				$titoloPagina = "Pagina: ".$cursorePagina->getTitolo();
				$urlPagina = self::$container->get('router')->generate('modifica_pagina', array('id_pagina' => $cursorePagina->getId()));
			}
			$cursoreFrammento = $cursorePagina->getFrammentoContenitore();
			$breadcrumb[] = array("titolo" => $titoloPagina, "url" => $urlPagina);
			
			if ($cursoreFrammento) {
				$breadcrumb[] = array("titolo" => "Frammento: ".$cursoreFrammento, "url" => self::$container->get('router')->generate('modifica_frammento', array('id_frammento' => $cursoreFrammento->getId())));
			}
		}
		
		foreach (array_reverse($breadcrumb) as $breadcrumb_ordinato) {
			self::$container->get('pagina')->aggiungiElementoBreadcrumb($breadcrumb_ordinato["titolo"], $breadcrumb_ordinato["url"]);
		}				
	}
	
	public function creaBreadcrumbCampo($campo, $accodaCampo = false) {
		$breadcrumb = array();
		
		if ($accodaCampo) {
			$breadcrumb[] = array("titolo" => "Campo: ".$campo, "url" => self::$container->get('router')->generate('modifica_campo', array('id_campo' => $campo->getId())));
		}		
		
		$frammento = $campo->getFrammento();
		$breadcrumb[] = array("titolo" => "Frammento: ".$frammento, "url" => self::$container->get('router')->generate('modifica_frammento', array('id_frammento' => $frammento->getId())));
	
		$cursoreFrammento = $frammento;
		while ($cursoreFrammento) {
			$cursorePagina = $cursoreFrammento->getPagina();
			if ($cursorePagina->getFascicolo()) {
				$titoloPagina = $cursorePagina->getTitolo();
				$urlPagina = self::$container->get('router')->generate('modifica_fascicolo', array('id_fascicolo' => $cursorePagina->getFascicolo()->getId()));
			} else {
				$titoloPagina = "Pagina: ".$cursorePagina->getTitolo();
				$urlPagina = self::$container->get('router')->generate('modifica_pagina', array('id_pagina' => $cursorePagina->getId()));
			}
			$cursoreFrammento = $cursorePagina->getFrammentoContenitore();
			$breadcrumb[] = array("titolo" => $titoloPagina, "url" => $urlPagina);
			
			if ($cursoreFrammento) {
				$breadcrumb[] = array("titolo" => "Frammento: ".$cursoreFrammento, "url" => self::$container->get('router')->generate('modifica_frammento', array('id_frammento' => $cursoreFrammento->getId())));
			}
		}
		
		foreach (array_reverse($breadcrumb) as $breadcrumb_ordinato) {
			self::$container->get('pagina')->aggiungiElementoBreadcrumb($breadcrumb_ordinato["titolo"], $breadcrumb_ordinato["url"]);
		}				
	}
	
	public function creaBreadcrumbPagina($pagina) {
		$breadcrumb = array();
		$cursorePagina = $pagina;
		$cursoreFrammento = true;
		while ($cursoreFrammento) {		
			$cursoreFrammento = $cursorePagina->getFrammentoContenitore();
			
			if ($cursoreFrammento) {
				$breadcrumb[] = array("titolo" => "Frammento: ".$cursoreFrammento, "url" => self::$container->get('router')->generate('modifica_frammento', array('id_frammento' => $cursoreFrammento->getId())));
				$cursorePagina = $cursoreFrammento->getPagina();
				if ($cursorePagina->getFascicolo()) {
					$titoloPagina = $cursorePagina->getTitolo();
					$urlPagina = self::$container->get('router')->generate('modifica_fascicolo', array('id_fascicolo' => $cursorePagina->getFascicolo()->getId()));
				} else {
					$titoloPagina = "Pagina: ".$cursorePagina->getTitolo();
					$urlPagina = self::$container->get('router')->generate('modifica_pagina', array('id_pagina' => $cursorePagina->getId()));
				}				
				$breadcrumb[] = array("titolo" => $titoloPagina, "url" => $urlPagina);
			}
		}
		
		foreach (array_reverse($breadcrumb) as $breadcrumb_ordinato) {
			self::$container->get('pagina')->aggiungiElementoBreadcrumb($breadcrumb_ordinato["titolo"], $breadcrumb_ordinato["url"]);
		}
	}
	
	public function get($fascicolo, $path): ?array {
		if (!\is_object($fascicolo)) {
			throw new \Exception("Il primo parametro deve essere un oggetto");
		}
		
		if (\is_null($fascicolo)) {
			return null;
		}
		
		$aliases = \explode(".", $path);
		
		$current = $fascicolo;
		foreach ($aliases as $alias) {
			$new_current = $current->getByAlias($alias);
			
			if (\is_null($new_current)) {
				return null;
			} else {
				$current = $new_current;
			}
		}
		
		return $this->getLabel($current);		
	}
	
	public function getLabel($oggetto): array {	
		if ($oggetto instanceof Campo) {
			$result = array("label" => $oggetto->getLabel());
			if ($oggetto->getTipoCampo()->getCodice() == "choice" && $oggetto->getExpanded()) {
				$result["scelte"] = array();
				$servizio = self::$container->get("fascicolo.tipo.choice");
				$scelte = $servizio->calcolaScelte($oggetto);	
				foreach($scelte as $valore => $chiave) {
					$result["scelte"][$chiave] = $valore;
				}
			}
			return $result;
		} elseif ($oggetto instanceof \FascicoloBundle\Entity\Frammento) {
			$result = array("label" => $oggetto->getTitolo());
			foreach ($oggetto->getIstanzeCampiIndicizzate() as $alias => $istanzeCampo) {
				$result["campi"][$alias] = $this->getLabel($istanzeCampo);
			}
			
			foreach ($oggetto->getSottoPagine() as $sottoPagina) {
				$result["sottoPagine"][$sottoPagina->getAlias()] = $this->getLabel($sottoPagina);			
			}			
			return $result;
		} elseif ($oggetto instanceof \FascicoloBundle\Entity\Pagina) {
			$result = array("label" => $oggetto->getTitolo());
			foreach ($oggetto->getFrammenti() as $frammento) {
				$result["frammenti"][$frammento->getAlias()] = $this->getLabel($frammento);
			}
			return $result;
		}
		
		return ['label' => null];
	}	  

}
