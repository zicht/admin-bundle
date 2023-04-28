<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zicht\Bundle\AdminBundle\Form\AutocompleteType;
use Zicht\Bundle\AdminBundle\Service\Quicklist;

/**
 * Controls a quick list for configured entities.
 */
class QuicklistController extends AbstractController
{
    /**
     * Displays a quick list control for jumping to entries registered in the quick list service
     *
     * @return array|JsonResponse
     * @Template("@ZichtAdmin/Quicklist/quicklist.html.twig")
     * @Route("quick-list")
     */
    public function quicklistAction(Request $request, Quicklist $quicklist)
    {
        if ($request->get('repo') && $request->get('pattern')) {
            if ($request->get('language')) {
                $language = $request->get('language');
            } else {
                $language = null;
            }
            return new JsonResponse($quicklist->getResults($request->get('repo'), $request->get('pattern'), $language));
        }

        $repos = [];
        foreach ($quicklist->getRepositoryConfigs() as $identifier => $repo) {
            $form = $this->createForm(AutocompleteType::class, [], [
                'repo' => $identifier,
                'transformer' => 'noop',
                'attr' => ['data-quicklist-url' => $this->generateUrl('zicht_admin_quicklist_quicklist', ['repo' => $identifier])],
                'js_callback' => 'function(item){ window.location.href = item.url; }',
            ]);
            $repos[] = ['label' => $repo['title'], 'form' => $form->createView()];
        }

        return [
            'repos' => $repos,
        ];
    }
}
