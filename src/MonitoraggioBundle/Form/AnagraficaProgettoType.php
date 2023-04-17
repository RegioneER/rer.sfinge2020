<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\DataMapperInterface;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints\Valid;
use MonitoraggioBundle\Entity\TC5TipoOperazione;

class AnagraficaProgettoType extends CommonType implements DataMapperInterface {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $disabled = $options['disabled'];
        $required = $options['required'];

        $builder->add('asse', self::text, [
            'disabled' => true,
            'label' => 'Asse',
            'required' => false,
        ]);

        $builder->add('numero_atto', self::text, [
            'label' => 'Codice locale procedura di attivazione',
            'required' => false,
            'disabled' => true,
        ]);

        $builder->add('codice_procedura_attivazione', self::text, [
            'label' => 'Codice procedura attivazione collegata',
            'required' => false,
            'disabled' => true,
        ]);

        $builder->add('protocollo', self::text, [
            'label' => 'Codice locale progetto',
            'required' => false,
            'disabled' => true,
        ]);

        $builder->add('titolo', self::text, [
            'label' => 'Titolo',
            'disabled' => true,
            'required' => false,
        ]);

        $builder->add('sintesi', self::textarea, [
            'label' => 'Sintesi',
            'required' => false,
            'disabled' => true,
            'attr' => ['rows' => 10],
        ]);

        $builder->add('tipo_operazione_cup', self::entity, [
            'label' => 'Tipo operazione',
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
            'class' => TC5TipoOperazione::class,
            'choices_as_values' => true,
            'placeholder' => '-',
            'choice_label' => function (TC5TipoOperazione $item) {
                return $item->__toString() . ' - ' . $item->getDescrizioneTipologiaCup();
            },
        ]);

        $builder->add('cup', self::text, [
            'label' => 'CUP',
            'disabled' => true,
            'required' => false,
        ]);

        $builder->add('tipo_aiuto', self::entity, [
            'label' => 'Tipo aiuto',
            'disabled' => $options['ruolo_lettura'],
            'required' => !$disabled,
            'class' => 'MonitoraggioBundle\Entity\TC6TipoAiuto',
        ]);

        $builder->add('data_inizio', self::birthday, [
            'label' => 'Data inizio',
            'disabled' => $options['ruolo_lettura'],
            'required' => !$disabled,
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);

        $builder->add('data_fine_prevista', self::birthday, [
            'label' => 'Data fine prevista',
            'disabled' => $options['ruolo_lettura'],
            'required' => !$disabled,
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);

        $builder->add('data_fine_effettiva', self::birthday, [
            'label' => 'Data fine effettiva',
            'disabled' => $options['ruolo_lettura'],
            'required' => !$disabled,
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);

        $builder->add('tipo_procedura_att_orig', self::entity, [
            'label' => 'Tipo procedura attivazione originaria',
            'disabled' => $options['ruolo_lettura'],
            'required' => !$disabled,
            'class' => 'MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria',
        ]);

        $builder->add('localizzazione_geografica', 'MonitoraggioBundle\Form\Type\RichiestaLocalizzazioneGeograficaType', [
            'disabled' => $options['ruolo_lettura'],
            'required' => false,
            'label' => false,
            'constraints' => [
                new Valid(),
            ],
        ]);

        // Seconda parte del form

        $builder->add('mon_progetto_complesso', self::entity, [
            'class' => 'MonitoraggioBundle\Entity\TC7ProgettoComplesso',
            'label' => 'Progetto complesso',
            'placeholder' => '-',
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
        ]);

        $builder->add('mon_grande_progetto', self::entity, [
            'class' => 'MonitoraggioBundle\Entity\TC8GrandeProgetto',
            'label' => 'Grande progetto',
            'placeholder' => '-',
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
        ]);

        $builder->add('mon_generatore_entrate', self::choice, [
            'label' => 'Generatore entrate',
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => [
                'No' => 0,
                'Sì' => 1,
            ],
        ]);

        $builder->add('fondo_di_fondi', self::choice, [
            'label' => 'Fondo di fondi',
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => [
                'No' => 0,
                'Sì' => 1,
            ],
        ]);

        $builder->add('mon_tipo_localizzazione', self::entity, [
            'class' => 'MonitoraggioBundle\Entity\TC10TipoLocalizzazione',
            'label' => 'Tipo localizzazione',
            'placeholder' => '-',
            'required' => $required && !$disabled,
            'disabled' => $options['ruolo_lettura'],
        ]);

        $builder->add('mon_gruppo_vulnerabile', self::entity, [
            'class' => 'MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto',
            'label' => 'Gruppo vulnerabile',
            'placeholder' => '-',
            'required' => $required && !$disabled,
            'disabled' => $options['ruolo_lettura'],
        ]);

        $builder->add('mon_liv_istituzione_str_fin', self::entity, [
            'class' => 'MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione',
            'label' => 'Livello istituzione strumento finanziario',
            'placeholder' => '-',
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
        ]);

        $builder->add('mon_strumenti_attuativi', self::collection, [
            'entry_type' => 'MonitoraggioBundle\Form\Type\StrumentoAttuativoType',
            'entry_options' => [],
            'label' => false,
            'required' => !$disabled && $required,
            'disabled' => $options['ruolo_lettura'],
            'allow_add' => !$disabled,
            'allow_delete' => !$disabled,
            'delete_empty' => true,
            'by_reference' => true,
        ]);

        $builder->add('mon_prg_pubblico', self::choice, [
            'required' => true,
            'label' => 'Tipologia soggetto',
            'choices_as_values' => true,
            'choices' => [
                'Pubblico' => true,
                'Privato' => false,
            ],
        ]);

        $builder->add('submit', self::salva_indietro, [
            'url' => $options['url_indietro'],
            'disabled' => $options['ruolo_lettura'],
        ]);

        $builder->setDataMapper($this);
    }

    /**
     * @param  \RichiesteBundle\Entity\Richiesta $richiesta
     * @param mixed $form
     */
    public function mapDataToForms($richiesta, $form) {
        $procedura = $richiesta->getProcedura();
        $istruttoria = $richiesta->getIstruttoria();
        $attuazioneControllo = $richiesta->getAttuazioneControllo();
        $localizzazioneGeografica = $richiesta->getMonLocalizzazioneGeografica()->get(0);

        $form = iterator_to_array($form);

        $form['asse']->setData($procedura->getAsse());
        $form['numero_atto']->setData($procedura->getAtto()->getNumero());
        $form['codice_procedura_attivazione']->setData($procedura->getMonProcAtt());
        $form['protocollo']->setData($richiesta->getProtocollo());
        $form['titolo']->setData($richiesta->getTitolo());
        $form['sintesi']->setData($richiesta->getAbstract());
        $form['tipo_operazione_cup']->setData($richiesta->getMonTipoOperazione());
        $form['cup']->setData($istruttoria->getCodiceCup());
        $form['tipo_aiuto']->setData($richiesta->getMonTipoAiuto());
        $form['data_inizio']->setData($attuazioneControllo->getDataAvvio());
        $form['data_fine_prevista']->setData($attuazioneControllo->getDataTermine());
        $form['data_fine_effettiva']->setData($attuazioneControllo->getDataTermineEffettivo());
        $form['tipo_procedura_att_orig']->setData($richiesta->getMonTipoProceduraAttOrig());
        $form['localizzazione_geografica']->setData($localizzazioneGeografica);
        $form['mon_progetto_complesso']->setData($richiesta->getMonProgettoComplesso());
        $form['mon_generatore_entrate']->setData($richiesta->getMonGeneratoreEntrate());
        $form['mon_liv_istituzione_str_fin']->setData($richiesta->getMonLivIstituzioneStrFin());
        $form['mon_strumenti_attuativi']->setData($richiesta->getMonStrumentiAttuativi());
        $form['mon_prg_pubblico']->setData($richiesta->getMonPrgPubblico());
        $form['fondo_di_fondi']->setData($richiesta->getMonFondoDiFondi());
        $form['mon_tipo_localizzazione']->setData($richiesta->getMonTipoLocalizzazione());
        $form['mon_gruppo_vulnerabile']->setData($richiesta->getMonGruppoVulnerabile());
        $form['mon_grande_progetto']->setData($richiesta->getMonGrandeProgetto());
    }

    /**
     * @param Richiesta $richiesta
     * @param mixed $form
     */
    public function mapFormsToData($form, &$richiesta) {
        $form = iterator_to_array($form);

        $attuazioneControllo = $richiesta->getAttuazioneControllo();
        $sedi = $richiesta->getMonLocalizzazioneGeografica();

        $richiesta->setMonTipoOperazione($form['tipo_operazione_cup']->getData());
        $attuazioneControllo->setDataAvvio($form['data_inizio']->getData());
        $attuazioneControllo->setDataTermine($form['data_fine_prevista']->getData());
        $attuazioneControllo->setDataTermineEffettivo($form['data_fine_effettiva']->getData());

        if ($sedi->first()) {
            $sedi->set($sedi->key(), $form['localizzazione_geografica']->getData());
        } else {
            $sedi->add($form['localizzazione_geografica']->getData());
        }

        $richiesta->setMonTipoAiuto($form['tipo_aiuto']->getData());
        $richiesta->setMonTipoProceduraAttOrig($form['tipo_procedura_att_orig']->getData());
        $richiesta->setMonProgettoComplesso($form['mon_progetto_complesso']->getData());
        $richiesta->setMonGeneratoreEntrate($form['mon_generatore_entrate']->getData());
        $richiesta->setMonLivIstituzioneStrFin($form['mon_liv_istituzione_str_fin']->getData());
        $richiesta->setMonStrumentiAttuativi($form['mon_strumenti_attuativi']->getData());
        $richiesta->setMonFondoDiFondi($form['fondo_di_fondi']->getData());
        $richiesta->setMonTipoLocalizzazione($form['mon_tipo_localizzazione']->getData());
        $richiesta->setMonGruppoVulnerabile($form['mon_gruppo_vulnerabile']->getData());
        $richiesta->setMonGrandeProgetto($form['mon_grande_progetto']->getData());
        $richiesta->setMonPrgPubblico($form['mon_prg_pubblico']->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Richiesta::class,
        ]);
        $resolver->setRequired(['url_indietro', 'disabled', 'ruolo_lettura']);
    }
}
