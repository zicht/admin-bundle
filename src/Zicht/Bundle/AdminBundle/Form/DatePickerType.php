<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Form;

use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Zicht\Bundle\AdminBundle\DataTransformer\HumanReadableDateTransformer;

/**
 * Custom  date time object for rendering of datePicker
 *
 * Make sure you include both ZichtAdminBundle:Views:Admin:includes:zicht_date_picker* files in your admin theme
 *
 * @package Zicht\Bundle\AdminBundle\Form
 */
class DatePickerType extends AbstractType
{
    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'attr' => array(
                'class' => 'zicht_date_picker'
            )
        ));
    }

    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return 'zicht_date_picker';
    }

    /**
     * @{inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new HumanReadableDateTransformer($options['model_timezone'], $options['view_timezone']));
    }


    /**
     * @{inheritDoc}
     */
    public function getParent()
    {
        return 'datetime';
    }
}