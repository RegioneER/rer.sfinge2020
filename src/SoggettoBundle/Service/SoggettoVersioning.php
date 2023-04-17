<?php

namespace SoggettoBundle\Service;

/**
 * Description of SoggettoVersioning
 *
 * @author aturdo <aturdo@schema31.it>
 */
class SoggettoVersioning {
	
	public function creaSoggettoVersion($soggetto) {
		if ($soggetto instanceof \SoggettoBundle\Entity\ComuneUnione) {
			$soggettoVersion = new \SoggettoBundle\Entity\ComuneUnioneVersion();
			$soggettoVersion->setComuneUnioneComune($soggetto->getComuneUnioneComune());
		} elseif ($soggetto instanceof \SoggettoBundle\Entity\Azienda) {
			$soggettoVersion = new \SoggettoBundle\Entity\AziendaVersion();
			$soggettoVersion->setFatturato($soggetto->getFatturato());
			$soggettoVersion->setBilancio($soggetto->getBilancio());
			$soggettoVersion->setCcia($soggetto->getCcia());
			$soggettoVersion->setDataCcia($soggetto->getDataCcia());
			$soggettoVersion->setRea($soggetto->getRea());
			$soggettoVersion->setDataRea($soggetto->getDataRea());
			$soggettoVersion->setRegistroEquivalente($soggetto->getRegistroEquivalente());
		} else {
			$soggettoVersion = new \SoggettoBundle\Entity\SoggettoVersion();
		}

		$soggettoVersion->setSoggetto($soggetto);
		
		$soggettoVersion->setCap($soggetto->getCap());
		$soggettoVersion->setCcnl($soggetto->getCcnl());
		$soggettoVersion->setCivico($soggetto->getCivico());
		$soggettoVersion->setCodiceAteco($soggetto->getCodiceAteco());
		$soggettoVersion->setCodiceFiscale($soggetto->getCodiceFiscale());
		$soggettoVersion->setCodiceOrganismo($soggetto->getCodiceOrganismo());
		$soggettoVersion->setComune($soggetto->getComune());
		$soggettoVersion->setStato($soggetto->getStato());
		$soggettoVersion->setComuneEstero($soggetto->getComuneEstero());
		$soggettoVersion->setProvinciaEstera($soggetto->getProvinciaEstera());
		$soggettoVersion->setDataCostituzione($soggetto->getDataCostituzione());
		$soggettoVersion->setDataRegistrazione($soggetto->getDataRegistrazione());
		$soggettoVersion->setDenominazione($soggetto->getDenominazione());
		$soggettoVersion->setDimensione($soggetto->getDimensione());
		$soggettoVersion->setDimensioneImpresa($soggetto->getDimensioneImpresa());
		$soggettoVersion->setEmail($soggetto->getEmail());
		$soggettoVersion->setEmailPec($soggetto->getEmailPec());
		$soggettoVersion->setFax($soggetto->getFax());
		$soggettoVersion->setFormaGiuridica($soggetto->getFormaGiuridica());
		$soggettoVersion->setImpresaIscrittaInail($soggetto->getImpresaIscrittaInail());
		$soggettoVersion->setImpresaIscrittaInailDi($soggetto->getImpresaIscrittaInailDi());
		$soggettoVersion->setImpresaIscrittaInps($soggetto->getImpresaIscrittaInps());
		$soggettoVersion->setLocalita($soggetto->getLocalita());
		$soggettoVersion->setMatricolaInps($soggetto->getMatricolaInps());
		$soggettoVersion->setMotivazioniNonIscrizioneInail($soggetto->getMotivazioniNonIscrizioneInail());
		$soggettoVersion->setMotivazioniNonIscrizioneInps($soggetto->getMotivazioniNonIscrizioneInps());
		$soggettoVersion->setNumeroCodiceDittaImpresaAssicurata($soggetto->getNumeroCodiceDittaImpresaAssicurata());
		$soggettoVersion->setPartitaIva($soggetto->getPartitaIva());
		$soggettoVersion->setSitoWeb($soggetto->getSitoWeb());
		$soggettoVersion->setTel($soggetto->getTel());
		$soggettoVersion->setTipoSoggetto($soggetto->getTipoSoggetto());
		$soggettoVersion->setVia($soggetto->getVia());
		$soggettoVersion->setCodiceAtecoSecondario($soggetto->getCodiceAtecoSecondario());
		
		
		return $soggettoVersion;
	} 
	
	public function creaSedeVersion($sede) {
		$sedeVersion = new \SoggettoBundle\Entity\SedeVersion();
		$sedeVersion->setSede($sede);
		
		$sedeVersion->setAteco($sede->getAteco());
		$sedeVersion->setDenominazione($sede->getDenominazione());
		$sedeVersion->setIndirizzo(clone $sede->getIndirizzo());
		$sedeVersion->setNumeroRea($sede->getNumeroRea());
		$sedeVersion->setAtecoSecondario($sede->getAtecoSecondario());
		
		return $sedeVersion;
	} 	
}
