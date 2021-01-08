<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TinymceType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'tinymce';
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['theme' => 'regular']);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['required'] = false;
        $view->vars['attr']['class'] = $this->appendClassVar($view->vars['attr']);
        $view->vars['attr']['data-theme'] = $options['theme'];
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextareaType::class;
    }

    private function appendClassVar(array $attr): string
    {
        if (array_key_exists('class', $attr)) {
            return $attr['class'] .= ' tinymce';
        }
        return 'tinymce';
    }
}
