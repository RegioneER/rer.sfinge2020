<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SfingeBundle\Entity\Asse;
use SfingeBundle\Entity\Procedura;
use MonitoraggioBundle\Form\Entity\RicercaProgetto;

class RicercaProgettoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('asse', self::entity, [
            'class' => Asse::class,
            'placeholder' => '-',
            'required' => false,
            'label' => 'Asse',
        ]);

        $builder->add('procedura', self::entity, [
            'class' => Procedura::class,
            'placeholder' => '-',
            'required' => false,
            'label' => 'Titolo Procedura attivazione',
        ]);

        $builder->add('codice_locale_progetto', self::text, [
            'label' => 'Codice locale progetto',
            'required' => false,
        ]);

        $builder->add('beneficiario', self::text, [
            'label' => 'Beneficiario',
            'required' => false,
        ]);

        $builder->add('codice_cup', self::text, [
            'label' => 'CUP',
            'required' => false,
        ]);

        $builder->add('codice_fiscale_beneficiario', self::text, [
            'label' => 'CF beneficiario',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => RicercaProgetto::class,
        ]);
    }
}
