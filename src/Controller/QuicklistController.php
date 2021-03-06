<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controls a quick list for configured entities.
 */
class QuicklistController extends AbstractController
{
    /**
     * Displays a quick list control for jumping to entries registered in the quick list service
     *
     * @param Request $request
     * @return array|JsonResponse
     *
     * @Template("@ZichtAdmin/Quicklist/quicklist.html.twig")
     * @Route("quick-list")
     */
    public function quicklistAction(Request $request)
    {
        $quicklist = $this->get('zicht_admin.quicklist');
        if ($request->get('repo') && $request->get('pattern')) {
            if ($request->get('language')) {
                $language = $request->get('language');
            } else {
                $language = null;
            }
            return new JsonResponse($quicklist->getResults($request->get('repo'), $request->get('pattern'), $language));
        }

        return [
            'repos' => $quicklist->getRepositoryConfigs(),
        ];
    }
}
