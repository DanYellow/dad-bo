<?php

namespace Admin\APIBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Admin\APIBundle\Controller\BaseAPI as BaseAPI;


class UserController extends BaseAPI
{
  function createProperUserObject(\Admin\APIBundle\Entity\User $seller) {
    return $seller = array(
      'id' => $seller->getId(),
      'pseudo' => $seller->getPseudo(),
      'location' => $seller->getLocation(),
    );
  }

  /**
   * {
   *   "status_code": 200,
   *   "data": {
   *     "list": [
   *        {
   *          "id": 36,
   *          "title": "Hello",
   *          "description": null,
   *          "price": "0.00",
   *          "createdAt": "2016-12-03 14:11:11",
   *          "lastUpdate": "2016-12-04 00:23:51",
   *          "seller": {
   *            "id": 1,
   *            "pseudo": "djeanlou",
   *            "location": null
   *          }
   *        },
   *        {
   *          "id": 37,
   *          "title": "Maillot PSG saison 2014-2015",
   *          "description": "djeanlou",
   *          "price": "45.00",
   *          "createdAt": "2016-12-03 14:13:30",
   *          "lastUpdate": null,
   *          "seller": {
   *            "id": 1,
   *            "pseudo": "djeanlou",
   *            "location": null
   *          }
   *        }
   *      ],
   *      "user": {
   *        "id": 1,
   *        "pseudo": "djeanlou",
   *        "location": null
   *      }
   *    },
   *    "pagination": {
   *      "current": 1,
   *      "first": 1,
   *      "last": 4,
   *      "prev": 1,
   *      "next": 2,
   *      "total_pages": 4,
   *      "total_items": 7
   *    }
   * }
   *
   * 
   * @Route("{user}/classified_advertisements")
   * @Route("{user}/classified_advertisements/{page}", defaults={"page" = 1})
   * @Method({"GET"})
   *
   * @ApiDoc(
   *   description="Create a classified advertisement",
   *   ressource=false,
   *   section="User",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=true
   *     }
   *   },
   *   filters={
   *     {"name"="page", "dataType"="integer", "requirement"="\d+", "description"="Page number. If requirement isn't satisfied ", "default"=1},
   *   }
   * )
   */
  public function getUserClassifiedAdvertisements(Request $request)
  {
    $token = $request->headers->get('X-TOKEN');

    $userFromToken = $this->isUserTokenValid($token);
    
    if (!$userFromToken) {
     return new JSONResponse(
                  Helpers::manageInvalidUserToken()['container'],
                  Helpers::manageInvalidUserToken()['error_code']
                );
    }

    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository('AdminAPIBundle:User')
               ->findOneBy(array('username' => $userFromToken["username"]));
    
    $response = $this->retrieveClassifiedAdvertisements($request, $user);


    return new JSONResponse($response);
  }
}
