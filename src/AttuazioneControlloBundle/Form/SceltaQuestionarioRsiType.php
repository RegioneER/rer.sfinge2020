<?php
namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SceltaQuestionarioRsiType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tipologia_questionario_rsi', self::choice, [
            'label' => 'Scelta questionario RSI',
            'choices' => [
                'IMPRESE_MANIFATTURIERE' => 'Imprese manifatturiere',
                'IMPRESE_DI_SERVIZI' => 'Imprese di servizi',
            ],
            'expanded' => true,
            'placeholder' => '-',
            'constraints' => [new NotNull()],
        ]);

        $builder->add('pulsanti', self::salva_indietro, ['url' => $options['url_indietro']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('url_indietro');
        $resolver->setRequired('disabled');
    }
}
