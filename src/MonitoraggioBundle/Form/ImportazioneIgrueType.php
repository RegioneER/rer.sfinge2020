<?php
/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class ImportazioneIgrueType extends CommonType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('file', 'DocumentoBundle\Form\Type\DocumentoFileType', array(
            // 'tipo' => $options['tipologia_documento'],
            'lista_tipi' => array($options['tipologia_documento']),
            'label' => false,
            'constraints' => array(
                new NotNull(),
            ),
            ))
            ->add('submit', self::salva_indietro, array(
                'url' => $options['url_indietro'],
                'label_salva' => 'sfinge.monitoraggio.importa',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('url_indietro', 'tipologia_documento'));
    }
}
