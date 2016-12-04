<?php

namespace Admin\APIBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Admin\APIBundle\Entity\Category as Category;

use Admin\APIBundle\Controller\BaseAPI as BaseAPI;


class CategoryController extends BaseAPI
{
  /**
   * 
   * @Route("/category")
   * @Method({"POST"})
   * 
   * @ApiDoc(
   *   description="Create a new category only accessible for ROLE ≥ ADMIN",
   *   ressource=false,
   *   section="Category",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=true
   *     }
   *   },
   *   requirements={
   *     {"name"="name", "dataType"="integer", "description"="Category name"},
   *   }
   * )
   */
  public function createCategory(Request $request)
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

    if (!in_array('ROLE_ADMIN', $user->getRoles())){
      return new JSONResponse(array(
        'data' => null,
        'status_code' => Response::HTTP_UNAUTHORIZED,
        'success' => false,
        'errors' => array(
          'name' => ''
        )
      ), Response::HTTP_UNAUTHORIZED);
    }
    
    try {
      $catName = $this->getRequest()->get('name');

      $category = new Category();
      $category->setName($catName);
      $category->setSlugName($this->get('cocur_slugify')->slugify($catName));
      $em->persist($category);
      $em->flush();

      $response = array(
        'data' => array(
          'ressource' => $category->getSerializableDatas(),
          'flash_message' => Helpers::createFlashMessage('Ressource updated', 'success', 1001)
        ),
        'status_code'=> Response::HTTP_CREATED,
        'success' => true,
      );

    } catch (\Exception $e) {
      $response = array(
        'data' => array(
          'flash_message' => Helpers::createFlashMessage('An error occured', 'error', 1999)
        ),
        'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
        'success' => false,
        'errors' => [
          array('name' => $e->getMessage())
        ]
      );
    }

    return new JSONResponse($response, $response['status_code']);
  }
}
