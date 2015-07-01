<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Controller;

use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controls a quick list for configured entities.
 */
class QuicklistController extends Controller
{
    /**
     * Displays a quick list control for jumping to entries registered in the quick list service
     *
     * @param Request $request
     * @return array
     *
     * @Template
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

        return array(
            'repos' => $quicklist->getRepositoryConfigs()
        );
    }
}