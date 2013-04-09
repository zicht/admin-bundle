<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\MenuBundle\Form\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\Form\FormEvents;
use \Symfony\Component\Form\FormEvent;
use \Symfony\Component\Form\FormBuilderInterface;

use \Zicht\Bundle\MenuBundle\Manager\MenuManager;
use \Zicht\Bundle\UrlBundle\Url\Provider;

class MenuItemPersistenceSubscriber implements EventSubscriberInterface
{
    /**
     * @{inheritDoc}
     */
    static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA   => 'postSetData',
            FormEvents::POST_BIND       => 'postBind'
        );
    }


    function __construct(MenuManager $mm, Provider $provider, FormBuilderInterface $builder)
    {
        $this->mm = $mm;
        $this->provider = $provider;
        $this->builder = $builder;
    }



    function postSetData(FormEvent $e)
    {
        if ($e->getData() === null) {
            return;
        }
        if ($item = $this->mm->getItem($this->provider->url($e->getData()))) {
            $item->setAddToMenu(true);
            $e->getForm()->get($this->builder->getName())->setData($item);
        }
    }


    function postBind(FormEvent $e)
    {
        if ($e->getForm()->getRoot()->isValid()) {
            $menuItem = $e->getForm()->get($this->builder->getName())->getData();
            if ($menuItem->isAddToMenu()) {
                if (!$menuItem->getTitle()) {
                    $menuItem->setTitle((string) $e->getData());
                }
                $menuItem->setPath($this->provider->url($e->getData()));
                $this->mm->addItem($menuItem);
            } elseif ($menuItem->getId()) {
                $this->mm->removeItem($menuItem);
            }
        }
    }
}