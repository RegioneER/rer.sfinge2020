<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AttuazioneControlloBundle\Entity\IterProgetto;

class IterProgettoType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('data_inizio_prevista', self::birthday, [
            'required' => true,
            'label' => 'Data inizio prevista',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new NotBlank(),
                new Date(),
            ],
        ]);

        $builder->add('data_inizio_effettiva', self::birthday, [
            'required' => false,
            'label' => 'Data inizio effettiva',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new Date(),
            ],
        ]);

        $builder->add('data_fine_prevista', self::birthday, [
            'required' => true,
            'label' => 'Data fine prevista',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new NotBlank(),
                new Date(),
            ],
        ]);

        $builder->add('data_fine_effettiva', self::birthday, [
            'required' => false,
            'label' => 'Data fine effettiva',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new Date(),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => IterProgetto::class,
		]);
    }
}
