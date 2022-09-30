<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Controller;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\Controller\CRUDController as BaseCRUDController;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zicht\Bundle\AdminBundle\Event\AdminEvents;
use Zicht\Bundle\AdminBundle\Event\ObjectDuplicateEvent;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/**
 * Provides some basic utility functionality for admin controllers, to be supplied as an construction parameter
 */
class CRUDController extends BaseCRUDController
{
    /** @var string[] */
    protected $overrideExcludedProperties = ['copiedFrom'];

    public static function getSubscribedServices(): array
    {
        return ['zicht_url.provider' => UrlProvider::class] + parent::getSubscribedServices();
    }

    /**
     * Override content of an original page with content of a new page and remove new page
     */
    public function overrideAction(Request $request): RedirectResponse
    {
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        $originalObject = $object->getCopiedFrom();

        // Override properties without association mapping/relations (like title, introduction etc)
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $classMetadata = $this->getDoctrine()->getManager()->getClassMetadata(get_class($object));

        $fieldMappings = $classMetadata->getFieldNames();

        foreach ($fieldMappings as $property) {
            if (in_array($property, $this->overrideExcludedProperties)) {
                continue;
            }
            $value = $propertyAccessor->getValue($object, $property);

            $propertyAccessor->setValue($originalObject, $property, $value);
        }

        // Override properties with association mapping/relations: meaning the property is also an object and there's an oneToOne, oneToMany or manyToMany relation
        // between the object and the property. To override properties in case of an oneToMany relation (e.g. one page can have many contentitems) the contentitems
        // have to be cloned and set to the "receiving" page before the "providing" page gets deleted, because of the cascade remove option.
        $associationMappings = $classMetadata->getAssociationNames();

        foreach ($associationMappings as $property) {
            if (in_array($property, $this->overrideExcludedProperties)) {
                continue;
            }
            $values = $propertyAccessor->getValue($object, $property);
            $mappingType = $classMetadata->getAssociationMappings()[$property]['type'];

            if ($mappingType === ClassMetadata::ONE_TO_MANY) {
                $clonedValues = [];
                foreach ($values as $value) {
                    $clonedValues[] = clone $value;
                }
                $propertyAccessor->setValue($originalObject, $property, $clonedValues);
            } else {
                $propertyAccessor->setValue($originalObject, $property, $values);
            }
        }

        $objectManager = $this->getDoctrine()->getManager();
        $objectManager->persist($originalObject);
        $objectManager->remove($object);
        $objectManager->flush();

        $this->addFlash('sonata_flash_success', $this->get('translator')->trans('admin.sonata_flash.override_success', [], 'admin'));

        return new RedirectResponse($this->admin->generateObjectUrl('edit', $originalObject));
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function duplicateAction(Request $request): RedirectResponse
    {
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DUPLICATE', $object)) {
            throw new AccessDeniedException();
        }

        $newObject = clone $object;

        if (method_exists($newObject, 'setTitle')) {
            $newObject->setTitle($this->get('translator')->trans('admin.duplicate.title_format', ['%title%' => $newObject->getTitle()], 'admin'));
        }

        if (method_exists($newObject, 'setCopiedFrom')) {
            $newObject->setCopiedFrom($object);
        }

        // dispatching an event in order for other bundles to listen to this event and do extra stuff for specific entities for example
        $this->get('event_dispatcher')->dispatch(new ObjectDuplicateEvent($object, $newObject), AdminEvents::OBJECT_DUPLICATE_EVENT);

        $objectManager = $this->getDoctrine()->getManager();
        $objectManager->persist($newObject);
        $objectManager->flush();

        $this->addFlash('sonata_flash_success', $this->get('translator')->trans('admin.sonata_flash.duplicate_success', [], 'admin'));

        return new RedirectResponse($this->admin->generateObjectUrl('edit', $newObject));
    }

    public function showAction(Request $request): Response
    {
        $id = $request->get($this->admin->getIdParameter());
        $obj = $this->admin->getObject($id);
        if ($this->container->has('zicht_url.provider') && $this->get('zicht_url.provider')->supports($obj)) {
            return $this->redirect($this->get('zicht_url.provider')->url($obj));
        }

        return parent::showAction($request);
    }

    public function editAction(Request $request): Response
    {
        if ($request->get('__bind_only')) {
            return $this->bindAndRender($request, 'edit');
        }

        return parent::editAction($request);
    }

    public function createAction(Request $request): Response
    {
        $refl = new \ReflectionClass($this->admin->getClass());
        if ($refl->isAbstract() && !$request->get('subclass')) {
            $delegates = [];
            $classMetadata = $this->getDoctrine()->getManager()->getClassMetadata($this->admin->getClass());
            foreach ($classMetadata->subClasses as $subClass) {
                if ($admin = $this->get('sonata.admin.pool')->getAdminByClass($subClass)) {
                    if ($admin->isGranted('CREATE')) {
                        $delegates[] = $admin;
                    }
                }
            }

            if (count($delegates)) {
                return $this->renderWithExtraParams(
                    '@ZichtAdmin/CRUD/create-subclass.html.twig',
                    ['admins' => $delegates]
                );
            }
        }

        if ($request->get('__bind_only')) {
            return $this->bindAndRender($request, 'create');
        }

        return parent::createAction($request);
    }

    /**
     * Move the item up. Used for Tree admins
     *
     * @param mixed $id
     */
    public function moveUpAction(Request $request, $id): Response
    {
        $repo = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass());
        $result = $repo->find($id);
        $repo->moveUp($result);

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        } else {
            return new Response('<script>history.go(-1);</script>');
        }
    }

    /**
     * Move the item up. Used for Tree admins
     *
     * @param mixed $id
     */
    public function moveDownAction(Request $request, $id): Response
    {
        $repo = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass());
        $result = $repo->find($id);
        $repo->moveDown($result);

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        } else {
            return new Response('<script>history.go(-1);</script>');
        }
    }

    protected function bindAndRender(Request $request, string $action): Response
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        if ($action == 'edit') {
            $id = $request->get($this->admin->getIdParameter());

            $object = $this->admin->getObject($id);

            if (!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            if (false === $this->admin->isGranted('EDIT', $object)) {
                throw new AccessDeniedException();
            }
        } else {
            $object = $this->admin->getNewInstance();

            $this->admin->setSubject($object);

            /** @var $form \Symfony\Component\Form\Form */
            $form = $this->admin->getForm();
            $form->setData($object);
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getRuntime(FormRenderer::class)->setTheme($view, $this->admin->getFormTheme());

        return $this->renderWithExtraParams(
            $this->admin->getTemplate($templateKey),
            [
                'action' => $action,
                'form' => $view,
                'object' => $object,
            ]
        );
    }
}
