<?php

namespace NotizieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotiziaType extends CommonType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('titolo')
            ->add('testo')
            ->add('dataInserimento', 'date', ['widget' => 'single_text', 'input' => 'datetime', 'format' => 'dd/MM/yyyy'])
            ->add('dataInserimento', self::birthday, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'required' => true,
                'label' => 'Data Inserimento', ])
            ->add('dataInizioVisualizzazione', self::birthday, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'required' => true,
                'label' => 'Data Inizio Visualizzazione', ])
            ->add('dataFineVisualizzazione', self::birthday, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'required' => true,
                'label' => 'Data Fine Visualizzazione', ])
            ->add('visibilita', self::choice, [
                'choices_as_values' => true,
                'choices' => \array_flip($options["visibilita"]),
                'required' => true,
                "expanded" => false,
                "multiple" => true,
                'label' => 'Visibilità',
            ])
            ->add('procedura', self::entity, [
                'class' => 'SfingeBundle\Entity\Procedura',
                'placeholder' => '-',
                'required' => false,
                'label' => 'Procedura', ])
            ->add('pulsanti', self::salva_indietro, ["url" => $options["url_indietro"]]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'NotizieBundle\Entity\Notizia',
        ]);
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("visibilita");
    }
}
