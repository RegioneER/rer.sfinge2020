<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use AttuazioneControlloBundle\Form\Istruttoria\VariazioneVocePianoCostoType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class VariazionePianoCostiBaseType extends \BaseBundle\Form\CommonType {

	public function getName() {
		return "variazione_voci_piano_costo";
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('voci_piano_costo', self::collection, array(
			'entry_type' => VariazioneVocePianoCostoType::class,
			'allow_add' => false,
			"label" => "Compilazione piano costi",
			'entry_options' => array(
				'annualita' => $options['annualita']
			)
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			// 'data_class' => 'AttuazioneControlloBundle\Entity\VariazioneRichiesta',
			'readonly' => false,
			'constraints' => array(new Valid()),
		));

		$resolver->setRequired("url_indietro");
		$resolver->setRequired("annualita");
	}


    public function finishView(FormView $view, FormInterface $form, array $options)
    {
		parent::finishView($view, $form, $options);
		\usort($view['voci_piano_costo']->children, function (FormView $a, FormView $b) {
            /** @var VariazioneVocePianoCosto $valueA */
			$valueA = $a->vars['value'];
            /** @var VariazioneVocePianoCosto $valueB */
			$valueB = $b->vars['value'];
            $posA = $valueA->getVocePianoCosto()->getPianoCosto()->getOrdinamento();
            $posB = $valueB->getVocePianoCosto()->getPianoCosto()->getOrdinamento();
    
            if ($posA == $posB) {
                return 0;
            }
    
            return ($posA < $posB) ? -1 : 1;
		});
    }

}
