<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form;

use \Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TinymceType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'tinymce';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('theme' => 'regular'));
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['required'] = false;
        $view->vars['attr']['class'] .= ' tinymce';
        $view->vars['attr']['data-theme'] = $options['theme'];

        parent::finishView($view, $form, $options); // TODO: Change the autogenerated stub
    }

    public function getParent()
    {
        return 'textarea';
    }
}