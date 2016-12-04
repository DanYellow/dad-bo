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
  /**
   * 
   * @Route("/user/{user}/classified_advertisements")
   * @Route("/user/{user}/classified_advertisements/?page={page}", defaults={"page" = 1})
   * @Method({"GET"})
   * 
   * @ApiDoc(
   *   description="Get classified advertisements for a specific user",
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
    
    $response = $this->retrieveClassifiedAdvertisements($request, $user, true);


    return new JSONResponse($response);
  }

  /**
   * @Route("/user/{user}")
   * @Route("/user/me")
   * @Method({"GET"})
   *
   * @ApiDoc(
   *   description="Retrieve user datas",
   *   ressource=false,
   *   section="User",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=true
   *     }
   *   },
   *   parameters={
   *     {"name"="user", "dataType"="String", "required"="\d+", "description"="", "default"="me"},
   *   }
   * )
   */
  public function getUserDatas(Request $request)
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
    
    $response = array(
        'data' => array(
          'ressource' => $user->getSerializableDatas()
        ),
        'status_code'=> Response::HTTP_CREATED
      );


    return new JSONResponse($response);
  }
}
