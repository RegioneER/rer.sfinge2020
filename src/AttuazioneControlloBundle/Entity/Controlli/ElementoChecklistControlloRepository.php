<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\EntityRepository;

class ElementoChecklistControlloRepository extends EntityRepository {

    public function getElementiSpecificoStandard() {

        $dql = "SELECT e FROM AttuazioneControlloBundle:Controlli\ElementoChecklistControllo e "
                . "JOIN e.sezione_checklist sez "
                . "JOIN sez.checklist ch "
                . "WHERE e.specifica = 1 AND ch.codice IN ('CHECK_DESK_DEFAULT','CHECK_SPR_DEFAULT')";

        $em = $this->getEntityManager();
        $query = $em->createQuery($dql);
        $result = $query->getResult();

        return $result;
    }
    
    public function getElementiSpecificoStabilita() {

        $dql = "SELECT e FROM AttuazioneControlloBundle:Controlli\ElementoChecklistControllo e "
                . "JOIN e.sezione_checklist sez "
                . "JOIN sez.checklist ch "
                . "WHERE e.specifica = 1 AND ch.codice IN ('CHECK_SPR_STABILITA')";

        $em = $this->getEntityManager();
        $query = $em->createQuery($dql);
        $result = $query->getResult();

        return $result;
    }
    
    public function getElementiSpecificoPuntuale() {

        $dql = "SELECT e FROM AttuazioneControlloBundle:Controlli\ElementoChecklistControllo e "
                . "JOIN e.sezione_checklist sez "
                . "JOIN sez.checklist ch "
                . "WHERE e.specifica = 1 AND ch.codice IN ('CHECK_PNT_STABILITA')";

        $em = $this->getEntityManager();
        $query = $em->createQuery($dql);
        $result = $query->getResult();

        return $result;
    }

}
