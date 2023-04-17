<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssegnamentoIstruttoriaRichiestaType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('istruttore', self::entity, array(
            'class' => 'SfingeBundle\Entity\Utente',
            'choices' => $options["istruttori"],
            'choice_label' => "nomeCognomeIstruttoreRichieste",
            'placeholder' => '-',
            'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull()),
            'required' => true,
            'label' => 'Assegna a',
        ));

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired("istruttori");

        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta',
        ));

        $resolver->setRequired("url_indietro");
    }

}
