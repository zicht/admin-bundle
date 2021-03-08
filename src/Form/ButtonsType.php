<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ButtonsType extends AbstractType
{
    /**
     * Option 'buttons' should be an array of buttons. Each button can have the options as show below.
     * Add a field with `'mapped' => false` to not map the buttons to any field of your subject.
     * @example:
     *   ->add(
     *       '_action',
     *       ButtonsType::class,
     *       [
     *           'mapped' => false,
     *           'required' => false,
     *           'buttons' => [
     *               'key' => [
     *                  // Button appearance options, all optional. Will fall back to default values when omitted.
     *                   'label' => 'action_edit', # Optional. If not specified, the 'key' will be used as label
     *                   'label_translation_domain' => 'SonataAdminBundle', # Optional. Will take translation domain of admin when left out
     *                   'style' => 'danger', # Optional. Sonata button style (e.g. 'default' [= default], 'primary', 'success', 'info', 'danger', 'warning')
     *                   'size' => 'sm', # Optional. Sonata button size. Skip for default size, otherwise pick 'xs', 'sm' or 'lg'.
     *                   'icon' => 'pencil', # Optional. Sonata FA icon style
     *                   'disabled' => 'true', # Optional. Set 'disabled' attribute on which Sonata applies a disabled style
     *                  // Button action/target options. One of either of these is required
     *                   'route' => 'edit', # Admin route to use to render the button URL, OR...
     *                   'url' => '/admin/dashboard', # ... Absolute or relative URL to use
     *               ],
     *           ],
     *       ]
     *   )
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['buttons']);
        $resolver->setAllowedTypes('buttons', 'array[]');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['buttons'] = $options['buttons'];
        $view->vars['admin'] = $view->vars['sonata_admin']['admin'];
    }

    public function getBlockPrefix(): string
    {
        return 'zicht_admin_buttons';
    }
}
