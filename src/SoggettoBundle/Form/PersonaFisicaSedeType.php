<?php
namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonaFisicaSedeType extends CommonType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$disabled = $options["visualizzazione"];
		$read_only = $options["visualizzazione"];

		/*if ($read_only == true) {
			$attr = ['readonly' => 'readonly'];
		} else {
			$attr = [];
		}*/

		$builder->add('indirizzo', 'BaseBundle\Form\IndirizzoType', [
            'readonly' => $read_only,
			'validation_groups' => $options["validation_groups"],
			'label' => false,
        ]);

		$builder->add('disabilitaCombo', self::hidden, ['data' => $options["visualizzazione"]]);
		$builder->add('pulsanti', self::salva_indietro, ["url" => $options["url_indietro"], 'disabled' => $disabled]);
	}

	public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Entity\Sede',
			'visualizzazione' => false,
			"dataIndirizzo" => null,
			"pubblico" => false,
            "validation_groups" => ['Default', 'sede'],
		));

		$resolver->setRequired("url_indietro");
	}
}
