<?php

namespace ProtocollazioneBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SfingeBundle\Form\Entity\RicercaRichiestaProtocollo;

class RichiestaProtocolloRepository extends EntityRepository
{
    public function getRichiestaProtocolloByProtocollo(RicercaRichiestaProtocollo $ricercaRichiestaProtocollo)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT rp
                 FROM ProtocollazioneBundle:RichiestaProtocollo rp 
                 WHERE rp.registro_pg = :registro_pg AND rp.anno_pg = :anno_pg AND rp.num_pg = :num_pg'
            )
            ->setParameter('registro_pg', $ricercaRichiestaProtocollo->getRegistroPg())
            ->setParameter('anno_pg', $ricercaRichiestaProtocollo->getAnnoPg())
            ->setParameter('num_pg', $ricercaRichiestaProtocollo->getNumPg());
        return $query->getResult();
    }
}
