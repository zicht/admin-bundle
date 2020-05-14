<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zicht\Bundle\AdminBundle\DataTransformer\HumanReadableDateTransformer;

/**
 * Custom date time object for rendering of datePicker
 *
 * Make sure you include both ZichtAdminBundle:Views:Admin:includes:zicht_date_picker* files in your admin theme
 */
class DatePickerType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'zicht_date_picker',
                ]
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'zicht_date_picker';
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'zicht_date_picker';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new HumanReadableDateTransformer($options['model_timezone'], $options['view_timezone']));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return DateTimeType::class;
    }
}
