<?php

namespace Admin\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Admin\APIBundle\Entity\ClassifiedAdvertisement as ClassifiedAdvertisement;
use Admin\APIBundle\Controller\Helpers as Helpers;
use Admin\APIBundle\Controller\BaseAPI as BaseAPI;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Doctrine\ORM\Tools\Pagination\Paginator;



class ClassifiedAdvertisementController extends BaseAPI
{
  /**
   * 
   * @Route("/classified_advertisements", defaults={"p": 1, "q": null})
   * @Route("/classified_advertisements?p={page}&q={query}&c={category}", defaults={"p": 1, "q": "null", "c": "Jeux vidéo"})
   * @Method({"GET"})
   * 
   * @ApiDoc(
   *   description="Get list of classified advertisements",
   *   section="Classified avertisements",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=false
   *     }
   *   },
   *   filters={
   *     {"name"="p", "dataType"="integer", "requirement"="\d+", "description"="Page number", "default"=1},
   *     {"name"="q", "dataType"="string", "description"="User query", "default"="null"},
   *     {"name"="c", "dataType"="string", "description"="User category choice", "default"="Jeux vidéo"},
   *   }
   * )
   */
  public function getClassifiedAdvertisements(Request $request)
  {
    $token = $request->headers->get('X-TOKEN');
    $userFromToken = $this->isUserTokenValid($token);
    
    $currentUser = null;
    if ($userFromToken) {
      $em = $this->getDoctrine()->getManager();
      $currentUser = $em->getRepository('AdminAPIBundle:User')
                 ->findOneBy(array('username' => $userFromToken['username']));
    }

    $response = $this->retrieveClassifiedAdvertisements($request, $currentUser);
    return new JSONResponse($response);
  }

  /**
   * @Route("/classified_advertisement/{id}")
   * @Method({"GET"})
   *
   * @ApiDoc(
   *   description="Get a specific classified advertisement",
   *   ressource=false,
   *   section="Classified avertisements",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=true
   *     }
   *   },
   *   requirements={
   *     {"name"="id", "dataType"="Integer", "required"=true, "description"="id the classified advertisement"},
   *   },
   * )
   */
  public function getClassifiedAdvertisement(Request $request)
  {
    $id = (int)$this->getRequest()->get('id');

    $em = $this->getDoctrine()->getManager();
    $classifiedAdvertisement = $em->getRepository('AdminAPIBundle:ClassifiedAdvertisement')
                                  ->findOneBy(array('id' => $id));

    if (!$classifiedAdvertisement) {
      $response = array(
        'data' => array(
          'flash_message' => Helpers::createFlashMessage('Ressource not found', 'error', 1004)
        ),
        'status_code' => Response::HTTP_NOT_FOUND,
        'errors' => null
      );
    } else {
      $token = $request->headers->get('X-TOKEN');
      $userFromToken = $this->isUserTokenValid($token);
      
      $currentUser = null;
      if ($userFromToken) {
        $currentUser = $em->getRepository('AdminAPIBundle:User')
                          ->findOneBy(array('username' => $userFromToken['username']));
      }

      $isMine = ($classifiedAdvertisement->getSeller() === $currentUser) ? true : false;
      $classifiedAdvertisement = $classifiedAdvertisement->getSerializableDatas();
      $classifiedAdvertisement['is_mine'] = $isMine;

      $response = array(
        'success' => true,
        'status_code' => Response::HTTP_OK,
        'data' => array(
          'ressource' => $classifiedAdvertisement,
        ),
        'errors' => null
      );
    }

    return new JSONResponse($response, $response['status_code']);
  }

  /**
   * @Route("/classified_advertisement/{id}")
   * @Method({"POST"})
   *
   * @ApiDoc(
   *   description="Update a classified advertisement",
   *   ressource=false,
   *   section="Classified avertisements",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=true
   *     }
   *   },
   *   requirements={
   *     {"name"="id", "dataType"="Integer", "requirement"="\d+", "description"="id of the classified advertisement"},
   *   },
   *   parameters={
   *     {"name"="title", "dataType"="String", "required"=true, "description"="Title of the classified advertisement"},
   *     {"name"="description", "dataType"="String", "required"=false, "description"="Description of the item sold"},
   *     {"name"="price", "dataType"="float", "required"=false, "description"="Price of the item sold"},
   *   },
   * )
   */
  public function updateClassifiedAdvertisement(Request $request)
  {
    $response = array();

    $token = $request->headers->get('X-TOKEN');
    $userFromToken = $this->isUserTokenValid($token);
    if (!$userFromToken) {
     return new JSONResponse(
                  Helpers::manageInvalidUserToken()['container'],
                  Helpers::manageInvalidUserToken()['error_code']
                );
    }

    $em = $this->getDoctrine()->getManager();
    $seller = $em->getRepository('AdminAPIBundle:User')
               ->findOneBy(array('username' => $userFromToken['username']));

    $id = (int)$this->getRequest()->get('id');

    $classifiedAdvertisement = $em->getRepository('AdminAPIBundle:ClassifiedAdvertisement')
                                  ->findOneBy(array(
                                      'seller' => $seller,
                                      'id' => $id,
                                    ));

    if ($classifiedAdvertisement) {
      try {
        $category    = $this->getRequest()->get('category');
        
        $classifiedAdvertisement->setTitle($this->getRequest()->get('title'));
        $classifiedAdvertisement->setDescription($this->getRequest()->get('description'));
        $classifiedAdvertisement->setPrice($this->getRequest()->get('price'));
        $classifiedAdvertisement->setLastUpdate(new \DateTime());

        $categoryEntity = $em->getRepository('AdminAPIBundle:Category')->findOneBy(array('name' => $category));
        if ($categoryEntity) {
          $classifiedAdvertisement->setCategory($categoryEntity);
        }

        $em->persist($classifiedAdvertisement);
        $em->flush();

        $response = array(
          'success' => true,
          'data' => array(
            'ressource' => $classifiedAdvertisement->getSerializableDatas($seller->getSerializableDatas()),
            'flash_message' => Helpers::createFlashMessage('Ressource updated', 'success', 1001)
          ),
          'status_code'=> Response::HTTP_CREATED
        );
      } catch (\Exception $e) {
        $response = array(
          'success' => false,
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Missing required parameters', 'error', 1004)
          ),
          'status_code'=> Response::HTTP_UNPROCESSABLE_ENTITY,
          'errors' => [
            array('name' => $e->getMessage())
          ]
        );
      }
    } else {
      $response = array(
        'success' => false,
        'data' => array(
          'flash_message' => Helpers::createFlashMessage('Ressource not found', 'error', 1004)
        ),
        'status_code'=> Response::HTTP_NOT_FOUND
      );
    }

    return new JSONResponse($response, $response['status_code']);
  }

  /**
   * @Route("/classified_advertisement/{id}")
   * @Method({"DELETE"})
   *
   * @ApiDoc(
   *   description="Delete a classified advertisement",
   *   ressource=false,
   *   section="Classified avertisements",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=true
   *     }
   *   },
   *   requirements={
   *     {"name"="id", "dataType"="Integer", "required"=true, "description"="id the classified advertisement"},
   *   },
   * )
   */
  public function deleteClassifiedAdvertisement(Request $request)
  {
    $response = array();
    
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
               ->findOneBy(array('username' => $userFromToken['username']));

    $id = (int)$this->getRequest()->get('id');
    
    $classifiedAdvertisement = $em->getRepository('AdminAPIBundle:ClassifiedAdvertisement')
                                  ->findOneBy(array(
                                      'seller' => $user,
                                      'id' => $id,
                                    ));

    if ($classifiedAdvertisement) {
      $em->remove($classifiedAdvertisement);
      $em->flush();

      $response = array(
        'success' => true,
        'data' => array(
          'flash_message' => Helpers::createFlashMessage('Element removed', 'success', 1000)
        ),
        'status_code'=> Response::HTTP_CREATED,
        'errors' => null
      );
    } else {
      $response = array(
        'success' => false,
        'data' => array(
          'flash_message' => Helpers::createFlashMessage('Ressource not found', 'error', 1004)
        ),
        'status_code' => Response::HTTP_NOT_FOUND,
        'errors' => null
      );
    }

    return new JSONResponse($response);
  }


  /**
   * @Route("/classified_advertisement")
   * @Method({"POST"})
   *
   * @ApiDoc(
   *   description="Create a classified advertisement",
   *   ressource=false,
   *   section="Classified avertisements",
   *   headers={
   *     { "name"="X-TOKEN", "description"="User token", "required"=true }
   *   },
   *   requirements={
   *     {"name"="title", "dataType"="String", "required"=true, "description"="Title of the classified advertisement"},
   *   },
   *   parameters={
   *     {"name"="description", "dataType"="String", "required"=false, "description"="Description of the item sold"},
   *     {"name"="price", "dataType"="float", "required"=false, "description"="Price of the item sold"},
   *     {"name"="category", "dataType"="string", "required"=false, "description"="Category of element"},
   *   }
   * )
   */
  public function createClassifiedAdvertisement(Request $request)
  {
    // ^(?:[1-9]\d*|0)?(?:\.\d+)?$
    
    $token = $request->headers->get('X-TOKEN');
    $userFromToken = $this->isUserTokenValid($token);
    if (!$userFromToken) {
      return new JSONResponse(
                  Helpers::manageInvalidUserToken()['container'], 
                  Helpers::manageInvalidUserToken()['error_code']
                );
    }

    $em = $this->getDoctrine()->getManager();
    
    $seller = $em->getRepository('AdminAPIBundle:User')
               ->findOneBy(array('username' => $userFromToken['username']));

    if (!$seller) {
      $response = array(
        'success' => false,
        'data' => null,
        'status_code' => Response::HTTP_NOT_FOUND,
        'errors' => array(
          'name' => ''
        )
      );
    } else {
      $title       = $this->getRequest()->get('title');
      $description = $this->getRequest()->get('description');
      $price       = $this->getRequest()->get('price');
      $category    = $this->getRequest()->get('category');

      $classifiedAdvertisement = new ClassifiedAdvertisement();
      $classifiedAdvertisement->setTitle($title);
      $classifiedAdvertisement->setSeller($seller);
      $classifiedAdvertisement->setDescription($description);
      $classifiedAdvertisement->setPrice($price);

      $categoryEntity = $em->getRepository('AdminAPIBundle:Category')->findOneBy(array('name' => $category));
      if ($categoryEntity) {
        $classifiedAdvertisement->setCategory($categoryEntity);
      }

      $seller->addClassifiedAdvertisement($classifiedAdvertisement);

      $em->persist($classifiedAdvertisement);
      $em->persist($seller);
      $em->flush();

      $currentUser = $seller->getSerializableDatas();

      $response = array(
        'success' => true,
        'data' => array(
          'ressource' => $classifiedAdvertisement->getSerializableDatas($currentUser),
          'flash_message' => Helpers::createFlashMessage('Ressource created', 'success', 1000)
        ),
        'status_code'=> Response::HTTP_CREATED
      );
    }

    return new JSONResponse($response);
  }
}
