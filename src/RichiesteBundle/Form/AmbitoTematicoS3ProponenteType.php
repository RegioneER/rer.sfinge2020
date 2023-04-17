<?php
namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AmbitoTematicoS3ProponenteType extends RichiestaType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ambito_tematico_s3', self::entity, [
            'class' => 'SfingeBundle\Entity\AmbitoTematicoS3',
            'choice_label' => 'descrizione',
            'required' => true,
            'label' => "Selezionare un ambito tematico S3",
            'expanded' => true,
            'disabled' => $options['disabled'],
        ]);

        $builder->add('pulsanti', self::salva_indietro, [
            'url' => $options['url_indietro'],
            'disabled' => false
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => "RichiesteBundle\Entity\AmbitoTematicoS3Proponente"
        ]);
        $resolver->setRequired("disabled");
        $resolver->setRequired("url_indietro");
    }
}
