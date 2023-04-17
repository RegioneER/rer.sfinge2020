<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use IstruttorieBundle\Form\Entity\RicercaIstruttoria;

class RicercaIstruttoriaType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('procedura', self::entity, [
            'class' => 'SfingeBundle\Entity\Procedura',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Procedura',
        ]);

        $builder->add('finestraTemporale', self::choice, [
            'required' => false,
            'label' => 'Finestra Temporale',
            'choices_as_values' => true,
            'choices' => [
                'Prima' => '1',
                'Seconda' => '2',
                'Terza' => '3',
                'Quarta' => '4',
                'Quinta' => '5',
                'Sesta' => '6',
            ],
        ]);

        $builder->add('protocollo', self::text, ['required' => false, 'label' => 'Protocollo']);
		$builder->add('id', self::text, ['required' => false, 'label' => 'Id operazione',]);

        $builder->add('completata', self::choice, [
            'required' => false,
            'label' => 'Stato istruttoria',
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => [
                'Completata' => true,
                'Non completata' => false,
                ],
            ]);

        $builder->add('proroga_gestita', self::choice, [
            'required' => false,
            'label' => 'Stato Istruttoria Proroghe',
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => [
                'Completata' => true,
                'Non completata' => false,
                ],
            ]);

        $builder->add('denominazione', self::text, ['required' => false, 'label' => 'Denominazione soggetto']);
        $builder->add('codice_fiscale', self::text, ['required' => false, 'label' => 'Codice fiscale soggetto']);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => RicercaIstruttoria::class,
        ]);
    }
}
