<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Utility\EsitoValidazione;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\VoceModalitaFinanziamento;

class GestoreModalitaFinanziamentoBase extends AGestoreModalitaFinanziamento {


	public function generaModalitaFinanziamentoRichiesta($id_proponente, $opzioni = array()) {
		$em = $this->getEm();
		/** @var Proponente $proponente */
		$proponente = $em->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
		$richiesta = $proponente->getRichiesta();
		$procedura = $richiesta->getProcedura();
		$vociModalita = $procedura->getModalitaFinanziamento();
		if ($vociModalita->count() == 0) {
			throw new SfingeException("Non sono state definite le voci di finanziamento per la procedura selezionata");
		}

		try {
			foreach ($vociModalita as $modalita) {
				$voce = new VoceModalitaFinanziamento($proponente, $modalita);
				$proponente->addVociModalitaFinanziamento($voce);
				$richiesta->addVociModalitaFinanziamento($voce);
				$em->persist($voce);
			}
		} catch (\Exception $e) {
			throw new SfingeException("Errore durante la generazione  delle voci di finanziamento per la procedura selezionata", 0, $e);
		}
	}

	public function ordina(Collection $array, $oggettoInterno, $campo = null) {
		$valori = $array->getValues();
		usort($valori, function ($a, $b) use ($oggettoInterno, $campo) {
			$oggettoInterno = 'get' . $oggettoInterno;
			if ($campo) {
				$campo = 'get' . $campo;
				return $a->$oggettoInterno()->$campo() > $b->$oggettoInterno()->$campo();
			} else {
				return $a->$oggettoInterno() > $b->$oggettoInterno();
			}
		});
		return $valori;
	}

	public function validaModalitaFinanziamentoRichiesta($id_proponente, $id_richiesta, $opzioni = array()) {
		throw new \Exception('Metodo non implementato');
	}

}
