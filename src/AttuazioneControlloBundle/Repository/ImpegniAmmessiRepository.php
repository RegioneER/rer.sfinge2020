<?php

namespace AttuazioneControlloBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;

class ImpegniAmmessiRepository extends EntityRepository {
	public function findOneByImpegno(RichiestaImpegni $impegno, TC36LivelloGerarchico $tc36)
	{
		$qb = $this->createQueryBuilder('a');
		$expr = $qb->expr();
		return $qb
			->join('a.richiesta_livello_gerarchico', 'rl')
			->where(
				$expr->eq('rl.tc36_livello_gerarchico', ':tc36'),
				$expr->eq('a.richiesta_impegni', ':impegno')
			)
			->setParameter('tc36', $tc36)
			->setParameter('impegno', $impegno)
			->getQuery()
			->getOneOrNullResult();
	}
}
