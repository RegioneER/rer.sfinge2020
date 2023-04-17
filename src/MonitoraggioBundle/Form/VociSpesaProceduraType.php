<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use SfingeBundle\Entity\Procedura;
use RichiesteBundle\Entity\PianoCosto;

class VociSpesaProceduraType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('piani_costo', self::collection, [
            'label' => false,
            'entry_type' => PianoCostoVoceSpesaType::class
        ]);
        $builder->add('submit', self::salva_indietro, [
            'url' => $options['indietro'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Procedura::class,
            'indietro' => null,
        ]);
    }
}
