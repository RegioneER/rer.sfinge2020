<?php

namespace SfingeBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssistenzaTecnicaType extends ProceduraType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $disabled = $options["disabled"];

        $builder->add('tipo_assistenza_tecnica', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'class' => 'SfingeBundle:TipoAssistenzaTecnica',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);

        $builder->add('tipo_procedura_monitoraggio', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'class' => 'SfingeBundle:TipoProceduraMonitoraggio',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);

        $builder->add('tipi_operazioni', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'class' => 'SfingeBundle:TipoOperazione',
            'choice_label' => 'descrizione',
            'label' => "Tipo operazione",
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'multiple' => false,
        ]);

        $builder->add('data_convenzione', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data Convenzione/Contratto',
        ]);

        $builder->add('documento_convenzione', self::documento, [
            'label' => false,
            'tipo' => $options['TIPOLOGIA_DOCUMENTO'],
            'disabled' => $disabled,
            'opzionale' => $options['documento_opzionale'], ]);

        $builder->add('data_programma_attivita', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data programma di attività',
        ]);

        $builder->add('pulsanti', self::salva_indietro, ["url" => $options["url_indietro"], 'disabled' => $disabled]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'SfingeBundle\Entity\AssistenzaTecnica',
            'TIPOLOGIA_DOCUMENTO' => '',
            'documento_opzionale' => false,
            'assi' => [],
        ]);
        $resolver->setRequired("url_indietro");
    }
}
