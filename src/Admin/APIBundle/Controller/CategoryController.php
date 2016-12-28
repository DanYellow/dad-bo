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
          'flash_message' => Helpers::createFlashMessage('Ressource created', 'success', 1001)
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

  /**
   * 
   * @Route("/category/{id}")
   * @Method({"POST"})
   * 
   * @ApiDoc(
   *   description="Update an existing category only accessible for ROLE ≥ ADMIN",
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
   *     {"name"="name", "dataType"="integer", "description"="Category new name"},
   *   }
   * )
   */
  public function updateCategory(Request $request)
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

    $id = (int)$this->getRequest()->get('id');
    $catName = $this->getRequest()->get('name');

    try {
      $categoryEntity = $em->getRepository('AdminAPIBundle:Category')->findOneById($id);

      $categoryEntity->setName($catName);
      $categoryEntity->setSlugName($this->get('cocur_slugify')->slugify($catName));

      $em->persist($categoryEntity);
      $em->flush();

      $response = array(
        'data' => array(
          'ressource' => $category->getSerializableDatas(),
          'flash_message' => Helpers::createFlashMessage('Ressource created', 'success', 1001)
        ),
        'status_code'=> Response::HTTP_OK,
        'success' => true,
      );
    } catch (\Exception $e) {
      $response = array(
        'success' => false,
        'data' => array(
          'flash_message' => Helpers::createFlashMessage('resource not found', 'error', 1004)
        ),
        'status_code' => Response::HTTP_NOT_FOUND,
        'errors' => null,
      );
    }

    return new JSONResponse($response, $response['status_code']);
  }

  /**
   * 
   * @Route("/categories")
   * @Method({"GET"})
   * 
   * @ApiDoc(
   *   description="Returns a list of every category",
   *   ressource=false,
   *   section="Category",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=true
   *     }
   *   }
   * )
   */
  public function getCategories(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $categories = $em->getRepository('AdminAPIBundle:Category')
                     ->findAll();

    $properCategories = array();
    foreach ($categories as $category) {
      $properCategories[] = $category->getSerializableDatas();
    }

    $response = array(
                  'status_code' => Response::HTTP_OK,
                  'success' => true,
                  'data' => ['list' => $properCategories],
                  
                );

    return new JSONResponse($response, $response['status_code']);
  }
}
