<?php

namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\DataMapperInterface;
use  Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints\Date;
use MonitoraggioBundle\Form\Type\ProgrammaProceduraType;

class ProcedureAttivazioneType extends CommonType implements DataMapperInterface {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $data = $builder->getData();

        $builder->add('codice_procedura_attivazione', self::text, [
            'label' => 'Codice procedura di attivazione',
            'required' => false,
        ]);

        $builder->add('asse', self::entity, [
            'class' => 'SfingeBundle\Entity\Asse',
            'disabled' => true,
            'label' => 'Asse',
            'required' => false,
        ]);

        $builder->add('numero_atto', self::text, [
            'label' => 'Codice locale procedura attivazione',
            'disabled' => true,
            'required' => false,
        ]);

        $builder->add('codice_rna', self::text, [
            'label' => 'Codice RNA',
            'required' => false,
            'constraints' => [
                new Length([
                    'max' => 10,
                    'maxMessage' => 'Massimo {{ limit }} caratteri',
                ]),
            ],
        ]);

        $builder->add('tipo', self::text, [
            'disabled' => true,
            'label' => 'Tipo procedura',
            'required' => false,
        ]);

        $builder->add('aiuti', self::choice, [
            'label' => 'Concessione aiuti',
            'required' => !$options['disabled'],
            'choices_as_values' => true,
            'choices' => [
                '0' => 'No',
                '1' => 'Sì',
            ],
        ]);

        $builder->add('titolo', self::textarea, [
            'disabled' => true,
            'required' => false,
            'label' => 'Titolo procedura',
        ]);

        $builder->add('responsabile', self::text, [
            'disabled' => true,
            'required' => false,
        ]);

        $builder->add('data_delibera', self::birthday, [
            'required' => !$options['disabled'],
            'label' => 'Data delibera',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new NotNull(),
                new Date(),
            ],
        ]);

        $builder->add('data_avvio_lavori_preparatori', self::birthday, [
            'required' => false,
            'label' => 'Data avvio lavori preparatori',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new Date(),
            ],
        ]);

        $builder->add('data_approvazione', self::birthday, [
            'required' => false,
            'label' => 'Data atto di approvazione',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new Date(),
            ],
        ]);

        $builder->add('data_avvio_procedura', self::birthday, [
            'required' => false,
            'label' => 'Data avvio procedura',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'constraints' => [
                new NotNull(),
                new Date(),
            ],
        ]);

        $builder->add('data_fine_procedura', self::birthday, [
            'required' => false,
            'label' => 'Data fine procedura',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);

        $builder->add('mon_tipo_beneficiario', self::choice,[
            'label' => 'Tipologia soggetto partecipante',
            'choices_as_values' => true,
            'choices' => [
                'Misto' => Procedura::MON_TIPO_PRG_MISTO,
                'Pubblico' => Procedura::MON_TIPO_PRG_PUBBLICO,
                'Privato' => Procedura::MON_TIPO_PRG_PRIVATO,
            ],
            'required' => true,
        ]);

        $programmi = $data->getMonProcedureProgrammi();
        $builder->add('programma', self::collection, [
            'entry_type' => ProgrammaProceduraType::class,
            'entry_options' => [],
            'label' => false,
            'required' => !$options['disabled'],
            'allow_add' => !$options['disabled'],
            'allow_delete' => false,
            'delete_empty' => false,
            'by_reference' => true,
        ]);

        $builder->add('submit', self::salva_indietro, [
            'url' => $options['url_indietro'],
        ]);

        $builder->setDataMapper($this);
    }

    /**
     * @param \SfingeBundle\Entity\Procedura $data
     */
    public function mapDataToForms($data, $forms) {
        $forms = iterator_to_array($forms);
        $forms['asse']->setData($data->getAsse());
        $forms['numero_atto']->setData($data->getAtto()->getNumero());
        $forms['codice_rna']->setData($data->getMonCodAiutoRna());
        $forms['tipo']->setData($data->getTipo());
        $forms['aiuti']->setData($data->getMonFlagAiuti());
        $forms['titolo']->setData($data->getTitolo());
        $forms['responsabile']->setData($data->getAmministrazioneEmittente()->getDescrizione());
        $forms['data_avvio_lavori_preparatori']->setData($data->getDataAvvioLavoriPreparatori());
        $forms['data_delibera']->setData($data->getDataDelibera());
        $forms['data_approvazione']->setData($data->getDataApprovazione());
        $forms['codice_procedura_attivazione']->setData($data->getMonCodiceProceduraAttivazione());
        $forms['mon_tipo_beneficiario']->setData($data->getMonTipoBeneficiario());

        $dataAvvioProcedura = \is_null($data->getMonDataAvvioProcedura()) ? $data->getDataApprovazione() : $data->getMonDataAvvioProcedura();
        $forms['data_avvio_procedura']->setData($dataAvvioProcedura);
        $forms['data_fine_procedura']->setData($data->getMonDataFineProcedura());
        $forms['programma']->setData($data->getMonProcedureProgrammi());
    }

    /**
     * @param \SfingeBundle\Entity\Procedura $data
     */
    public function mapFormsToData($forms, &$data) {
        $forms = iterator_to_array($forms);
        $data->setMonCodAiutoRna($forms['codice_rna']->getData());
        $data->setMonFlagAiuti($forms['aiuti']->getData());
        $data->setDataApprovazione($forms['data_approvazione']->getData());
        $data->setMonDataAvvioProcedura($forms['data_avvio_procedura']->getData());
        $data->setMonDataFineProcedura($forms['data_fine_procedura']->getData());
        $data->setMonProcedureProgrammi($forms['programma']->getData());
        $data->setDataAvvioLavoriPreparatori($forms['data_avvio_lavori_preparatori']->getData());
        $data->setDataDelibera($forms['data_delibera']->getData());
        $data->setMonCodiceProceduraAttivazione($forms['codice_procedura_attivazione']->getData());
        $data->setMonTipoBeneficiario($forms['mon_tipo_beneficiario']->getData());
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Procedura::class,
            'disabled' => false,
            'validation_groups' => ["monitoraggio", "Default"],
        ]);
        $resolver->setRequired('url_indietro');
    }
}
