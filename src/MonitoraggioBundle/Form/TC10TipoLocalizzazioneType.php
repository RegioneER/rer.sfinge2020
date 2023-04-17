<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of TC10TipoLocalizzazioneType
 *
 * @author lfontana
 */
class TC10TipoLocalizzazioneType extends BaseFormType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('tipo_localizzazione', self::text, array(
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        $builder->add('descrizione_tipo_localizzazione', self::textarea, array(
            'disabled' => $options['disabled'],
            'required' => false,
        ));

        $builder->add('submit', self::salva_indietro, array(
            "url" => $options["url_indietro"],
            'disabled' => $options['disabled'],
            'required' => false,
        ));
    }

}
