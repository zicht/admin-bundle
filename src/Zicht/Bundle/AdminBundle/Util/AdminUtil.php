<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Util;

use Sonata\AdminBundle\Form\FormMapper;

final class AdminUtil
{
    /**
     * Allows to reorder Tabs
     *
     * Need the formMapper since the used methods to set the tabs
     * are protected in the original Sonata implementation
     *
     * @param FormMapper $formMapper
     * @param array $tabOrder
     *
     * @return void
     */
    public static function reorderTabs(FormMapper $formMapper, array $tabOrder)
    {
        $tabsOriginal = $formMapper->getAdmin()->getFormTabs();

        //filter out tabs that doesn't exist (yet)
        $tabOrder = array_filter(
            $tabOrder,
            function ($key) use ($tabsOriginal) {
                return array_key_exists($key, $tabsOriginal);
            }
        );

        $tabs = array_merge(array_flip($tabOrder), $tabsOriginal);
        $formMapper->getAdmin()->setFormTabs($tabs);
    }
}