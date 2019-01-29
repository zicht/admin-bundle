<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as BaseCRUDController;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides some basic utility functionality for admin controllers, to be supplied as an construction parameter
 */
class CRUDController extends BaseCRUDController
{
    /**
     * Duplicate pages
     *
     * @return RedirectResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function duplicateAction()
    {
        $id     = $this->getRequest()->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DUPLICATE', $object)) {
            throw new AccessDeniedException();
        }

        $newObject = clone $object;

        if (method_exists($newObject, 'setTitle')) {
            $newObject->setTitle('[COPY] - ' . $newObject->getTitle());
        }

        $objectManager = $this->getDoctrine()->getManager();
        $objectManager->persist($newObject);
        $objectManager->flush();

        $this->addFlash(
            'sonata_flash_success',
            $this->admin->trans('flash_duplicate_success')
        );

        return new RedirectResponse($this->admin->generateObjectUrl('edit', $newObject));
    }

    /**
     * Show action
     *
     * @param int|null $id
     * @return RedirectResponse|Response
     */
    public function showAction($id = null)
    {
        $id = $this->getRequest()->get($this->admin->getIdParameter());
        $obj = $this->admin->getObject($id);
        if ($this->container->has('zicht_url.provider') && $this->get('zicht_url.provider')->supports($obj)) {
            return $this->redirect($this->get('zicht_url.provider')->url($obj));
        }

        return parent::showAction($id);
    }


    /**
     * @{inheritDoc}
     */
    public function editAction($id = null)
    {
        if ($this->getRequest()->get('__bind_only')) {
            return $this->bindAndRender('edit');
        }

        return parent::editAction($id);
    }


    /**
     * @{inheritDoc}
     */
    public function createAction()
    {
        $refl = new \ReflectionClass($this->admin->getClass());
        if ($refl->isAbstract() && !$this->getRequest()->get('subclass')) {
            $delegates = array();
            $classMetadata = $this->getDoctrine()->getManager()->getClassMetadata($this->admin->getClass());
            foreach ($classMetadata->subClasses as $subClass) {
                if ($admin = $this->get('sonata.admin.pool')->getAdminByClass($subClass)) {
                    if ($admin->isGranted('CREATE')) {
                        $delegates[] = $admin;
                    }
                }
            }

            if (count($delegates)) {
                return $this->render(
                    'ZichtAdminBundle:CRUD:create-subclass.html.twig',
                    array('admins' => $delegates)
                );
            }
        }


        if ($this->getRequest()->get('__bind_only')) {
            return $this->bindAndRender('create');
        }

        return parent::createAction();
    }


    /**
     * Move the item up. Used for Tree admins
     *
     * @param mixed $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveUpAction($id)
    {
        $repo   = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass());
        $result = $repo->find($id);
        $repo->moveUp($result);

        if ($referer = $this->getRequest()->headers->get('referer')) {
            return $this->redirect($referer);
        } else {
            return new Response('<script>history.go(-1);</script>');
        }
    }


    /**
     * Move the item up. Used for Tree admins
     *
     * @param mixed $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveDownAction($id)
    {
        $repo   = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass());
        $result = $repo->find($id);
        $repo->moveDown($result);

        if ($referer = $this->getRequest()->headers->get('referer')) {
            return $this->redirect($referer);
        } else {
            return new Response('<script>history.go(-1);</script>');
        }
    }


    /**
     * Binds the request to the form and only renders the resulting form.
     *
     * @param string $action
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function bindAndRender($action)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        if ($action == 'edit') {
            $id = $this->getRequest()->get($this->admin->getIdParameter());

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

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->handleRequest($this->getRequest());
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getRuntime(FormRenderer::class)->setTheme($view, $this->admin->getFormTheme());

        return $this->render(
            $this->admin->getTemplate($templateKey),
            array(
                'action' => $action,
                'form'   => $view,
                'object' => $object,
            )
        );
    }
}
