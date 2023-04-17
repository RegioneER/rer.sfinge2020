<?php
namespace RichiesteBundle\Form;

use SfingeBundle\Entity\Procedura;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarcaDaBolloType extends RichiestaType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!is_null($options['numero_marca_da_bollo_digitale'])) {
            $options['disabled'] = true;
        }

        if ($options['esenzione_marca_bollo']) {
            $builder->add('esente_marca_da_bollo', self::choice, [
                'label' => 'Esente da marca da bollo',
                'choices' => [
                    'Sì' => true,
                    'No' => false,
                ],
                'choices_as_values' => true,
                'required' => true,
                'expanded' => true,
                'disabled' => $options['disabled'],
            ]);

            $builder->add('riferimento_normativo_esenzione', self::textarea, [
                'label' => 'Riferimento normativo esenzione',
                'required' => false,
                'disabled' => $options['disabled'],
            ]);
        }

        if ($options['tipologia_marca_da_bollo'] == 'FISICA_E_DIGITALE') {
            $builder->add('tipologia_marca_da_bollo', self::choice, [
                'choices' => [
                    'Marca da bollo fisica' => Procedura::MARCA_DA_BOLLO_FISICA,
                    'Marca da bollo digitale' => Procedura::MARCA_DA_BOLLO_DIGITALE,
                ],
                'choices_as_values' => true,
                'required' => true,
                'expanded' => true,
                'disabled' => $options['disabled'],
            ]);
        }

        if ($options['tipologia_marca_da_bollo'] == 'FISICA_E_DIGITALE' || $options['tipologia_marca_da_bollo'] == 'FISICA') {
            $builder->add('data_marca_da_bollo', self::birthday, [
                'label' => 'Data marca da bollo',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'required' => $options['esenzione_marca_bollo'] ? false : true,
                'disabled' => $options['disabled'],
            ]);

            $builder->add('numero_marca_da_bollo', self::text, [
                'label' => 'Numero marca da bollo',
                'required' => $options['esenzione_marca_bollo'] ? false : true,
                'disabled' => $options['disabled'],
            ]);
        }

        if (!is_null($options['numero_marca_da_bollo_digitale'])) {
            $builder->add('numero_marca_da_bollo_digitale', self::text, [
                'label' => 'Numero marca da bollo digitale',
                'read_only' => true,
            ]);

            $builder->add('pulsanti', self::salva_indietro, [
                'url' => $options['url_indietro'],
                'disabled' => $options['disabled'],
            ]);
        }

        $builder->add('pulsanti', self::salva_indietro, [
            'url' => $options['url_indietro'],
            'disabled' => $options['disabled'],
            'label_salva' => 'Salva',
        ]);

        if ($options['tipologia_marca_da_bollo'] == 'FISICA_E_DIGITALE' || $options['tipologia_marca_da_bollo'] == 'DIGITALE') {
            $builder->add('pulsante_submit_e_paga_marca_da_bollo_digitale', self::submit, [
                'label' => 'Salva e vai al pagamento della marca da bollo digitale',
                'disabled' => $options['disabled'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'RichiesteBundle\Entity\Richiesta',
            'readonly' => false,
            'marca_da_bollo' => true,
            'esenzione_marca_bollo' => true,
            'tipologia_marca_da_bollo' => Procedura::MARCA_DA_BOLLO_FISICA,
            'numero_marca_da_bollo_digitale' => Procedura::MARCA_DA_BOLLO_FISICA,
            'validation_groups' => ['dati_marca_da_bollo'],
        ]);

        $resolver->setRequired('url_indietro');
    }
}
