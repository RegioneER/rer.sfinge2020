<?php

namespace SfingeBundle\Form;

use SfingeBundle\Entity\Procedura;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
use SfingeBundle\Entity\Bando;

class BandoType extends ProceduraType {   
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $disabled = $options["disabled"];

        $builder->add('tipo_procedura_monitoraggio', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'class' => 'SfingeBundle:TipoProceduraMonitoraggio',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
        ]);

        $builder->add('data_approvazione', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data di approvazione',
        ]);

        $builder->add('data_pubblicazione', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data di pubblicazione BUR',
        ]);
        $builder->add('data_ora_inizio_presentazione', self::datetime, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy HH:mm',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data e ora di inizio presentazione',
        ]);
        $builder->add('data_ora_fine_presentazione', self::datetime, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy HH:mm',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data e ora di fine presentazione',
        ]);
        $builder->add('data_ora_fine_creazione', self::datetime, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy HH:mm',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data e ora di fine creazione richieste',
        ]);
        $builder->add('data_ora_scadenza', self::datetime, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy HH:mm',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data e ora di scadenza del bando',
        ]);

        $builder->add('anticipo', self::checkbox, [
            'label' => "Anticipo",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('rimborso', self::checkbox, [
            'label' => "Rimborso(SAL, acconto)",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('pagamento_soluzione_unica', self::checkbox, [
            'label' => "Pagamento soluzione unica",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('tipi_operazioni', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'class' => 'SfingeBundle:TipoOperazione',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'multiple' => true,
        ]);

        $builder->add('numero_richieste', self::integer, [
            'label' => "Numero massimo di richieste per soggetto",
            'required' => true,
            'disabled' => $disabled,
        ]);

        $builder->add('numero_proponenti', self::integer, [
            'label' => "Numero massimo di proponenti per richiesta",
            'required' => true,
            'disabled' => $disabled,
        ]);

        $builder->add('stato_procedura', self::entity, [
            'class' => 'SfingeBundle:StatoProcedura',
            'choice_label' => 'descrizione',
            'required' => true,
            'disabled' => $disabled,
            'placeholder' => '-',
            'multiple' => false,
        ]);

        $builder->add('modalita_finanziamento_attiva', self::checkbox, [
            'label' => "Modalità finanziamento attiva",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('rating', self::checkbox, [
            'label' => "Rating",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('femminile', self::checkbox, [
            'label' => "Femminile",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('giovanile', self::checkbox, [
            'label' => "Giovanile",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('incremento_occupazionale', self::checkbox, [
            'label'=> "Incremento occupazionale",
            'required' => false,
            'disabled' => $disabled
        ]);

        $builder->add('dati_incremento_occupazionale', self::checkbox, [
            'label'=> "Richiesto inserimento del numero di dipendenti attuali e nuovi (riferito all’incremento occupazionale)",
            'required' => false,
            'disabled' => $disabled
        ]);

        $builder->add('fornitori', self::checkbox, [
            'label' => "Fornitori",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('requisiti_rating', self::checkbox, [
            'label' => "Requisiti rating",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('stelle', self::checkbox, [
            'label' => "Stelle",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('mon_proc_att', self::entity, [
            'label' => 'Procedura attivazione IGRUE',
            'required' => false,
            'disabled' => $disabled,
            'class' => TC1ProceduraAttivazione::class,
        ]);

        $builder->add('mon_tipo_procedura_attivazione', self::entity, [
            'label' => 'Tipo procedura attivazione',
            'class' => TC2TipoProceduraAttivazione::class,
            'placeholder' => '-',
        ]);

        $builder->add('mon_cod_aiuto_rna', self::text, [
            'label' => 'Codice aiuto RNA',
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('mon_flag_aiuti', self::choice, [
            'label' => 'Concessione aiuti',
            'required' => false,
            'choices_as_values' => true,
            'choices' => [
                'No' => 0,
                'Si' => 1,
            ],
            'disabled' => $disabled,
        ]);
        /** @var Bando $bando */
        $bando = $builder->getData();
        $builder->add('mon_data_avvio_procedura', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Data di avvio procedura',
            'data' => $bando->getMonDataAvvioProcedura() ?: $bando->getDataPubblicazione(),
        ]);

        $builder->add('mon_data_fine_procedura', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Data di fine procedura',
        ]);

        $builder->add('marca_da_bollo', self::checkbox, [
            'label' => "Marca da bollo",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('tipologia_marca_da_bollo', self::choice, [
            'choices' => [
                'Marca da bollo fisica' => Procedura::MARCA_DA_BOLLO_FISICA,
                'Marca da bollo digitale' => Procedura::MARCA_DA_BOLLO_DIGITALE,
                'L’utente potrà scegliere quale delle due tipologie utilizzare' => Procedura::MARCA_DA_BOLLO_FISICA_E_DIGITALE,
            ],
            'choices_as_values' => true,
            'placeholder' => '-',
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('esenzione_marca_bollo', self::checkbox, [
            'label' => "Esenzione marca da bollo",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('sezione_video', self::checkbox, [
            'label' => "Sezione video di presentazione",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('rendicontazione_attiva', self::checkbox, [
            'label' => "Flag per attivare la rendicontazione",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('proroga_attiva', self::checkbox, [
            'label' => "Flag per attivare le proroghe",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('richiesta_firma_digitale', self::checkbox, [
            'label' => "La richiesta di contributo è firmata digitalmente",
            'required' => true,
            'disabled' => $disabled,
        ]);

        $builder->add('richiesta_firma_digitale_step_successivi', self::checkbox, [
            'label' => "Le comunicazioni sono firmate digitalmente",
            'required' => true,
            'disabled' => $disabled,
        ]);

        $builder->add('mostra_contatore_richieste_presentate', self::checkbox, [
            'label' => "Mostra contatore richieste presentate",
            'required' => false,
            'disabled' => $disabled,
        ]);

        $builder->add('pulsanti', self::salva_indietro, [
            "url" => $options["url_indietro"],
            'disabled' => $disabled,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Bando::class,
            'assi' => [],
        ]);
        $resolver->setRequired("url_indietro");
    }
}
