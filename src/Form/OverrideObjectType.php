<?php
/**
 * @copyright Zicht Online <https://www.zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A type utilizing the override functionality.
 */
class OverrideObjectType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['object']);
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $view->vars['object'] = $options['object'];
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'zicht_override_object';
    }
}
