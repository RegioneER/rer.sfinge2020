<?php
/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use MonitoraggioBundle\Form\Type\LocalizzazioneGeograficaType;

class RichiestaLocalizzazioneGeograficaType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder
            ->add('localizzazione', LocalizzazioneGeograficaType::class, [
            'required' => $options['required'] && !$options['disabled'],
            'label' => false,
            'constraints' => [
                new NotNull(),
            ],
        ])
            ->add('indirizzo', self::text, [
            'label' => 'Indirizzo',
            'required' => $options['required'] && !$options['disabled'],
        ])
            ->add('cap', self::text, [
            'label' => 'CAP',
            'required' => $options['required'] && !$options['disabled'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => LocalizzazioneGeografica::class,
            'empty_data' => function (FormInterface $form) {
                $richiesta = $form->getParent()->getData();
                return new LocalizzazioneGeografica($richiesta);
            },
        ]);
        $resolver->setRequired([
            'disabled', 'required',
        ]);
    }
}
