<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use MonitoraggioBundle\Entity\TC47StatoProgetto;

class RichiestaStatoProgettoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('stato_progetto', self::entity, [
            'label' => 'Stato progetto',
            'class' => TC47StatoProgetto::class,
            'required' => !$options['disabled'],
        ]);

        $builder->add('data_riferimento', self::birthday, [
            'label' => 'Data di riferimento',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new NotBlank(),
                new Date(),
            ],
            'required' => !$options['disabled'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => RichiestaStatoAttuazioneProgetto::class,
            'empty_data' => function (FormInterface $form) {
                $richiesta = $form->getParent()->getParent()->getData();
                return new RichiestaStatoAttuazioneProgetto($richiesta);
            },
        ]);
    }
}
