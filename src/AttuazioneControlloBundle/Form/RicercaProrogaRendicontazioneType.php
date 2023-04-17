<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Form\Entity\RicercaProrogaRendicontazione;

class RicercaProrogaRendicontazioneType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('protocollo', self::text, [
            'required' => false,
            'label' => 'Protocollo progetto',
        ]);
        $builder->add('id_operazione', self::text, [
            'required' => false,
            'label' => 'ID Operazione',
        ]);
        $builder->add('procedura', self::text, [
            'required' => false,
            'label' => 'Bando',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => RicercaProrogaRendicontazione::class,
        ]);
    }
}
