<?php

namespace AttuazioneControlloBundle\Form\ControlliStabilita;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ControlloCampioneType extends CommonType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('descrizione', self::text, array(
            'disabled' => false,
            'required' => true,
            'label' => 'Nome campione',
        ));
        
        $builder->add('tipo_controllo', self::choice, array(
            'disabled' => false,
            'required' => true,
            'choices' => [
                'Controllo di stabilita' => 'STABILITA',
                'Controllo puntuale' => 'PUNTUALE',
            ],
            'choices_as_values' => true,
            'label' => 'Tipologia controllo',
        ));

        $builder->add('tipo', self::choice, array(
            'disabled' => false,
            'required' => true,
            'choices' => [
                'Automatico' => 'AUTO',
                'Da file' => 'FILE',
            ],
            'choices_as_values' => true,
            'label' => 'Tipo pre-campionamento',
        ));

        $builder->add('data_inizio', self::birthday, array(
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => false,
            'disabled' => false,
            'label' => 'Data inizio campione',
        ));

        $builder->add('data_termine', self::birthday, array(
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => false,
            'disabled' => false,
            'label' => 'Data termine campione',
        ));

        $builder->add('file', FileType::class, [
            'label' => 'Foglio di calcolo importazione progetti',
            'estensione' => 'xls, xlsx, ods, csv',
            'required' => false,
            'constraints' => new Assert\File([
                'mimeTypes' => [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'application/vnd.oasis.opendocument.spreadsheet',
                    'text/csv',
                ],
                'mimeTypesMessage' => 'I formati supportati sono: OpenDocument spreadsheet document, Microsoft Excel (OpenXML), Microsoft Excel e CSV',
                    ]),
        ]);

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ControlloCampione'
        ));

        $resolver->setRequired("url_indietro");
    }

}
