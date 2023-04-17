<?php

namespace AttuazioneControlloBundle\Service\Job;

use BaseBundle\Service\BaseService;

/**
 * Description of BonificaGiustificativiBando8
 *
 * @author Antonio Turdo <aturdo@schema31.it>
 */
class CreazioneGiustificativiDaDiCui extends BaseService {
    
    public function bonifica() {
        ini_set('memory_limit', '1G');
		set_time_limit(0);
	    
		$em = $this->getEm();
		$output_info = "";
		
		$di_cui = $em->getRepository("AttuazioneControlloBundle\Entity\DiCui")->findAll();
		
		$output_info .= 'Di Cui in tabella: ' . count($di_cui) . '<br>';
		
		$vpcg_da_di_cui_creati     = 0;
		$vpcg_da_di_cui_aggiornati = 0;
		
		$giustificativi_da_di_cui_creati     = 0;
		$giustificativi_da_di_cui_aggiornati = 0;
		
		$giustificativo_gestito = array();
		
		foreach ($di_cui as $d) {
			
			$voce_piano_costo         = $d->getVocePianoCostoGiustificativo();
			//$pagamento_destinazione   = $d->getPagamentoDestinazione();
			//$giustificativo_pagamento = $voce_piano_costo->getGiustificativoPagamento();
			//$pagamento_giustificativo = $giustificativo_pagamento->getPagamento();
			
			/* TEST POINT */
			// $output_info .= $voce_piano_costo->getId() . ' ' . $pagamento_destinazione->getId() . ' ' . $giustificativo_pagamento->getId() . ' ' . $pagamento_giustificativo->getId() . '<br>';
			
			$vpcg_da_di_cui = $em->getRepository("AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo")
					->findOneBy(
							array(
								"creato_da_di_cui" => $d->getId()
					));			
			
			if(is_null($vpcg_da_di_cui)) {
				$vpcg_da_di_cui = new \AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo();				
				$vpcg_da_di_cui_creati++;
				
			} else {
				$vpcg_da_di_cui_aggiornati++;
			}
									
			// E' già stato clonato stu giustificativo?
			$giustificativo_da_clonare = $voce_piano_costo->getGiustificativoPagamento();
			
			$giustificativo_clonato = $em->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")
					->findOneBy(
							array(
								"giustificativo_origine" => $giustificativo_da_clonare->getId()
					));
			
			if(is_null($giustificativo_clonato)) {
				$giustificativo_clonato = new \AttuazioneControlloBundle\Entity\GiustificativoPagamento();				
				$giustificativi_da_di_cui_creati++;				
			} else {
				if(!in_array($giustificativo_da_clonare->getId(), $giustificativo_gestito)){
					$giustificativo_gestito[] = $giustificativo_da_clonare->getId();
					$giustificativi_da_di_cui_aggiornati++;
				}
			}			
			
			// SETTO LE INFO MINIMALI (not nullable) + il giustificativo di origine + tipologia:
			$giustificativo_clonato->setPagamento($d->getPagamentoDestinazione());
			$giustificativo_clonato->setGiustificativoOrigine($giustificativo_da_clonare);
			$giustificativo_clonato->setTipologiaGiustificativo($giustificativo_da_clonare->getTipologiaGiustificativo());
			
			$em->persist($giustificativo_clonato);
			$em->flush();
			
			$vpcg_da_di_cui->setGiustificativoPagamento($giustificativo_clonato);
			
			$vpcg_da_di_cui->setVocePianoCosto($voce_piano_costo->getVocePianoCosto());
			$vpcg_da_di_cui->setImporto($d->getImporto());
			$vpcg_da_di_cui->setAnnualita($voce_piano_costo->getAnnualita());
			$vpcg_da_di_cui->setSpesaSoggettaLimite30($voce_piano_costo->getSpesaSoggettaLimite30());
					
			$vpcg_da_di_cui->setCreatoDaDiCui($d);		
			
			$em->persist($vpcg_da_di_cui);
			$em->flush();
		}
				
		$output_info .= 'Voci Piano Costi Giustificativi da DI CUI creati: '     . $vpcg_da_di_cui_creati     . '<br>';
		$output_info .= 'Voci Piano Costi Giustificativi da DI CUI aggiornati: ' . $vpcg_da_di_cui_aggiornati . '<br>';		
		$output_info .= 'Giustificativi CLONATI creati: '     . $giustificativi_da_di_cui_creati     . '<br>';
		$output_info .= 'Giustificativi CLONATI aggiornati: ' . $giustificativi_da_di_cui_aggiornati . '<br>';
		
		
		$output_info .= '<b>Controllo:</b><br>';
		$output_info .= 'lanciare le seguenti query e verificare che<br>';
		$output_info .= '------------------------------------------------------<br>';
		$output_info .= '<i>select count(id) from voci_piano_costo_giustificativi where creato_da_di_cui_id is not null</i> = Voci Piano Costi Giustificativi da DI CUI creati + Voci Piano Costi Giustificativi da DI CUI aggiornati<br>';
		$output_info .= '<i>select count(id) from giustificativi_pagamenti where giustificativo_origine_id is not null</i> = Giustificativi CLONATI creati + Giustificativi CLONATI aggiornati<br>';

		
		/******	QUERY DI CONTROLLO ********/
		/*
			select count(id) from voci_piano_costo_giustificativi
			select count(id) from voci_piano_costo_giustificativi where creato_da_di_cui_id is null
			select count(id) from voci_piano_costo_giustificativi where creato_da_di_cui_id is not null
		 
			# 1. Il numero di righe tornate dalla seguente query deve essere uguale al numero di giustificativi creati/aggiornati dal processo
			# 2. SUM(numero_voci_in_di_cui) nella seguente query deve essere uguale al numero di righe nella tabella di_cui

			select
			gp.id as giustificativo_con_voci_in_di_cui,
			count(vpcg.id) as numero_voci_in_di_cui
			from di_cui
			join voci_piano_costo_giustificativi vpcg on vpcg.id = di_cui.voce_piano_costo_giustificativo_id
			join giustificativi_pagamenti gp on vpcg.giustificativo_pagamento_id = gp.id
			group by gp.id

			select vpcg.id, giustificativo_pagamento_id from voci_piano_costo_giustificativi vpcg where creato_da_di_cui_id is not null

			select giustificativo_pagamento_id, count(vpcg.id) from voci_piano_costo_giustificativi vpcg
			where creato_da_di_cui_id is not null
			group by giustificativo_pagamento_id

 
		 */
		

        return new \Symfony\Component\HttpFoundation\Response($output_info);
    }
}
