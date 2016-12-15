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

use Admin\APIBundle\Form\ClassifiedAdvertisementType;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Doctrine\ORM\Tools\Pagination\Paginator;



class ClassifiedAdvertisementController extends BaseAPI
{
  /**
   * 
   * @Route("/classified_advertisements/{p}", defaults={"p": 1, "q": null, "c": null})
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
   *   resource=false,
   *   section="Classified avertisements",
   *   headers={
   *     {
   *       "name"="X-TOKEN",
   *       "description"="User token",
   *       "required"=false
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
          'flash_message' => Helpers::createFlashMessage('Resource not found', 'error', 1004)
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
      $classifiedAdvertisementObject = $classifiedAdvertisement->getSerializableDatas();
      $classifiedAdvertisementObject['is_mine'] = $isMine;

      $path = $classifiedAdvertisement->getWebPath();
      $imagePath = $this->get('liip_imagine.cache.manager')->getBrowserPath($path, 'classified_advertisement_details');
      $classifiedAdvertisementObject['image'] = $imagePath;

      $response = array(
        'success' => true,
        'status_code' => Response::HTTP_OK,
        'data' => array(
          'resource' => $classifiedAdvertisementObject,
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
   *   resource=false,
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
        $data = json_decode($request->getContent(), true);
        $category    = $data['category'];



        $classifiedAdvertisement->setTitle((isset($data['title'])) ? $data['title'] : null);
        $classifiedAdvertisement->setDescription((isset($data['description'])) ? $data['description'] : null);
        $classifiedAdvertisement->setPrice((isset($data['price'])) ? $data['price'] : null);
        $classifiedAdvertisement->setLastUpdate(new \DateTime());

        $categoryEntity = $em->getRepository('AdminAPIBundle:Category')->findOneBy(array('id' => $category));

        if ($categoryEntity) {
          $classifiedAdvertisement->setCategory($categoryEntity);
        }

        $em->persist($classifiedAdvertisement);
        $em->flush();

        $response = array(
          'success' => true,
          'data' => array(
            'resource' => $classifiedAdvertisement->getSerializableDatas(true),
            'flash_message' => Helpers::createFlashMessage('resource updated', 'success', 1001)
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
          'flash_message' => Helpers::createFlashMessage('resource not found', 'error', 1004)
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
   *   resource=false,
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
          'flash_message' => Helpers::createFlashMessage('resource not found', 'error', 1004)
        ),
        'status_code' => Response::HTTP_NOT_FOUND,
        'errors' => null
      );
    }

    return new JSONResponse($response, $response['status_code']);
  }


  /**
   * 
   * @Route("/classified_advertisement", requirements={"_method" = "POST"})
   * @Method({"POST"})
   *
   * @ApiDoc(
   *   description="Create a classified advertisement",
   *   resource=false,
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
      $data = json_decode($request->getContent(), true);

      $classifiedAdvertisement = new ClassifiedAdvertisement();
      $form = $this->get('form.factory')->create(new ClassifiedAdvertisementType, $classifiedAdvertisement);
      $form->submit($data);

      // return new JSONResponse($form->all());
      // http://stackoverflow.com/questions/34906128/symfony2-uploads-a-file-using-ajax-and-jquery
      // echo json_encode($form->all(), true);
      // echo $data;
      // return new Response((string)$form->isValid());

      // return new Response($request->request->get('image') );
      // return new Response(json_encode($request->files->all()) );
      // return new JSONResponse($request->request->all() );

      if (!$form->isValid()) {

        $response = array(
          'success' => false,
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Form invalid', 'error', 1000)
          ),
          'status_code'=> Response::HTTP_BAD_REQUEST,
          'errors' => [
            array('name' => (string) $form->getErrors(true, false))
          ]
        );
      } else {
        $title       = $request->request->get('title');
        $description = $request->request->get('description');
        $price       = $request->request->get('price');
        $category    = $request->request->get('category');

        // $category    = (isset($data['category'])) ? $data['category'] : null;

        $classifiedAdvertisement->setTitle($title);
        $classifiedAdvertisement->setSeller($seller);
        $classifiedAdvertisement->setDescription($description);
        $classifiedAdvertisement->setPrice($price);
        $classifiedAdvertisement->setFile($request->files->get('image'), array(), true);
        $classifiedAdvertisement->upload();

        // echo var_dump($classifiedAdvertisement);

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
            'resource' => $classifiedAdvertisement->getSerializableDatas($currentUser),
            'flash_message' => Helpers::createFlashMessage('resource created', 'success', 1000)
          ),
          'status_code'=> Response::HTTP_CREATED
        );
      }
    }

    return new JSONResponse($response, $response['status_code']);
  }
}
