<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 23/06/17
 * Time: 17:06
 */

namespace MonitoraggioBundle\Form\Ricerca;

use Symfony\Component\Form\FormBuilderInterface;

class RicercaStrutturaType extends BaseType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
            parent::buildForm($builder, $options);

            $builder->add('codice', self::text, array(
                'required' => false,
                'label' => 'Codice Struttura',
            ));

            $builder->add('descrizione', self::text, array(
                'required' => false,
                'label' => 'Descrizione Struttura',
            ));

    }

}



