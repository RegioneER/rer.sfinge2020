<?php


namespace RichiesteBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;


interface IGestorePianoCosto {

    /**
     * @param $id_proponente
     * @param array $opzioni
     * @return GestoreResponse
     */
	public function aggiornaPianoDeiCostiProponente($id_proponente,$opzioni = array(), $twig = null);
	public function generaPianoDeiCostiProponente($id_proponente,$opzioni = array());
    public function validaPianoDeiCostiProponente($id_proponente,$opzioni = array());

    public function calcolaContributo(Richiesta $oggetto, array $opzioni = array()): float;
    public function calcolaCostoTotale($id_richiesta,$opzioni = array());
    public function validaPianoDeiCosti($id_richiesta,$opzioni = array());

    public function generaArrayVista($id_proponente, $opzioni = array());
	
	public function getSezioni($id_proponente);
	public function getVociSpesa($id_proponente);
	public function getAnnualita($id_proponente);
}
