<?php

namespace RichiesteBundle\Fascicoli;

/**
 * Description of EsempioIstanza
 *
 * @author aturdo
 */
class EsempioIstanza {
	
	protected static $container;

	public function __construct($container) {
		self::$container = $container;
	}
	
	/**
	 * Funzione di esempio utilizzabile come callback di validazione.
	 * 
	 * I parametri significativi da passare al costruttore di ConstraintViolation
	 * sono due: il primo è il messaggio di errore, mentre il quinto è il path al
	 * campo che contiene l'errore. Dovrà essere costituito dall'alias del frammento
	 * e dall'alias del campo, separati da un punto. Se si passa null l'errore verrà
	 * visualizzato in testa alla pagina e non direttamente collegato al campo.
	 * 
	 * @param FascicoloBundle\Entity\IstanzaPagina $istanzaPagina
	 * @return \Symfony\Component\Validator\ConstraintViolationList
	 */
	public function callbackValidazione($istanzaPagina) {
		$violazioni = new \Symfony\Component\Validator\ConstraintViolationList();
		// aggiungere errori
//		$violazioni->add(new \Symfony\Component\Validator\ConstraintViolation("Errore su un campo specifico", null, array(), $istanzaPagina, "form.denominazione", null));
//		$violazioni->add(new \Symfony\Component\Validator\ConstraintViolation("Errore generico", null, array(), $istanzaPagina, null, null));
		
		// ritornare una \Symfony\Component\Validator\ConstraintViolationList
		return $violazioni;
	}
	
	/**
	 * Funzione di esempio utilizzabile come callback di presenza frammento.
	 * 
	 * @param FascicoloBundle\Entity\IstanzaPagina $istanzaFascicolo
	 * @param string $path
	 * @return boolean
	 */
	public function callbackPresenzaPagina($istanzaFascicolo, $path) {
		return false;
	}	
	
	/**
	 * Funzione di esempio utilizzabile come callback di presenza frammento.
	 * 
	 * @param FascicoloBundle\Entity\IstanzaPagina $istanzaFascicolo
	 * @param string $path
	 * @return boolean
	 */
	public function callbackPresenzaFrammento($istanzaFascicolo, $path) {
		return false;
	}
	
	/**
	 * Funzione di esempio utilizzabile come callback di presenza campo.
	 * 
	 * @param FascicoloBundle\Entity\IstanzaPagina $istanzaFascicolo
	 * @param string $path
	 * @return boolean
	 */	
	public function callbackPresenzaCampo($istanzaFascicolo, $path) {
		return false;
	}
	
}
