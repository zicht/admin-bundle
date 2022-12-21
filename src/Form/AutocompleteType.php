<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zicht\Bundle\AdminBundle\DataTransformer\ClassTransformer;
use Zicht\Bundle\AdminBundle\DataTransformer\MultipleTransformer;
use Zicht\Bundle\AdminBundle\DataTransformer\NoneTransformer;
use Zicht\Bundle\AdminBundle\Service\Quicklist;

/**
 * A type utilizing autocomplete functionality within the CMS.
 */
class AutocompleteType extends AbstractType
{
    public const OPTION_TRANSFORMER_AUTO = 'auto';
    public const OPTION_TRANSFORMER_CLASS = 'class';
    public const OPTION_TRANSFORMER_MULTIPLE = 'multiple';
    public const OPTION_TRANSFORMER_MULTIPLE_NONE = 'multiple_none';
    public const OPTION_TRANSFORMER_NONE = 'none';
    public const OPTION_TRANSFORMER_NOOP = 'noop'; // disable transformation completely

    /**
     * @var Quicklist
     */
    private $quicklist;

    public function __construct(Quicklist $quicklist)
    {
        $this->quicklist = $quicklist;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $transformerStrategy = $options['transformer'];
        if (self::OPTION_TRANSFORMER_AUTO === $transformerStrategy) {
            $transformerStrategy = $options['multiple'] ? self::OPTION_TRANSFORMER_MULTIPLE : self::OPTION_TRANSFORMER_CLASS;
        }

        switch ($transformerStrategy) {
            case self::OPTION_TRANSFORMER_CLASS:
                $builder->addViewTransformer(new ClassTransformer($this->quicklist, $options['repo']));
                break;

            case self::OPTION_TRANSFORMER_NONE:
                $builder->addViewTransformer(new NoneTransformer($this->quicklist, $options['repo']));
                break;

            case self::OPTION_TRANSFORMER_MULTIPLE:
                $builder->addViewTransformer(new MultipleTransformer(new ClassTransformer($this->quicklist, $options['repo'])));
                break;

            case self::OPTION_TRANSFORMER_MULTIPLE_NONE:
                $builder->addViewTransformer(new MultipleTransformer(new NoneTransformer($this->quicklist, $options['repo'])));
                break;
        }
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['repo'])
            ->setDefaults(
                [
                    'attr' => [
                        'placeholder' => 'zicht_quicklist.autocomplete_type.search_placeholder',
                        'data-allow-manual-input' => false,
                        'data-allow-manual-input-regex' => null,
                    ],
                    'multiple' => false,
                    'translation_domain' => 'admin',
                    'transformer' => self::OPTION_TRANSFORMER_AUTO,
                    'route' => 'zicht_admin_quicklist_quicklist',
                    'route_params' => [],
                    'class' => null, // BC - somehow this options is created when adding this type as a filter... :?
                ]
            );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['route'] = $options['route'];
        $view->vars['route_params'] = $options['route_params'] + [
                'repo' => $options['repo'],
            ];
        $view->vars['multiple'] = $options['multiple'];

        if ($options['multiple']) {
            $view->vars['full_name'] = $view->vars['full_name'] . '[]';
        }
    }

    public function getBlockPrefix()
    {
        return 'zicht_quicklist_autocomplete';
    }
}
