<?php
/**
 * @copyright Zicht Online <https://www.zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A type utilizing the override functionality.
 *
 * @deprecated Use the more generic {@see \Zicht\Bundle\AdminBundle\Form\ButtonsType}
 *   Before, OverrideObjectType had to be passed the object explicitly,
 *   but automatically would take the route 'override' and the translate 'admin.override.text_button':
 *     ```
 *     ->add(
 *         'copiedFrom',
 *         OverrideObjectType::class,
 *         ['object' => $this->getSubject()]
 *     )
 *     ```
 *   Now, ButtonsType should be passed the options for the button, but automatically takes
 *   the admin's subject as the target object:
 *     ```
 *     ->add(
 *         'copiedFrom',
 *         ButtonsType::class,
 *         ['buttons' => [
 *             'override' => [
 *                 'label' => 'admin.override.text_button',
 *                 'style' => 'info',
 *                 'route' => 'override',
 *             ],
 *         ]]
 *     )
 *     ```
 */
class OverrideObjectType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['object']);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $view->vars['object'] = $options['object'];
    }

    public function getBlockPrefix()
    {
        return 'zicht_override_object';
    }
}
