<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\Richiesta;

class FaseProceduraleBaseType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('voci_fase_procedurale', self::collection, [
            'entry_type' => CampiFaseProceduraleType::class,
            'allow_add' => false,
            'label' => "Compilazione Fasi procedurali",
            'entry_options' => [
				'labels' => $options['labels'],
				'attiva_opzionale' => $options['attiva_opzionale'],
				'data_approvazione' => $options['data_approvazione'],
			],
        ]);
        $builder->add('pulsanti', self::salva_indietro, [
			"url" => $options["url_indietro"], 
			'disabled' => false
		]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Richiesta::class,
            'readonly' => false,
            'validation_groups' => ["fase_procedurale"],
            'data_approvazione' => true,
        ]);

        $resolver->setRequired("url_indietro");
        $resolver->setRequired("attiva_opzionale");
        $resolver->setRequired("labels");
    }
}
