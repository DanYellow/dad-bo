<?php

namespace Admin\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Admin\APIBundle\Entity\ClassifiedAdvertisement as ClassifiedAdvertisement;
use Admin\APIBundle\Controller\Helpers as Helpers;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Doctrine\ORM\Tools\Pagination\Paginator;


class ClassifiedAdvertisementController extends Controller
{
    
    /**
     * Formats API response to be complient to jsonapi standard
     * http://jsonapi.org/examples/#pagination
     * @param  Object/Array $data Object
     * @param Array $extraData [<description>]
     * @return Object       Standardized datas
     */
    function formatAPIResponse($data, $extraData = array()) 
    {
      $standardizedData = array('data' => $data);

      return $standardizedData;
    }

    function createProperUserObject($user) {
      return $user = array(
        'id' => $user->getId(),
        'pseudo' => $user->getPseudo(),
        'location' => $user->getLocation(),
      );
    }

    /**
     * Get information for a given user.
     *
     * ### Example response ###
     *
     *     {
     *       "data": [{
     *         "id": 33,
     *         "title": "Xbox One",
     *         "description": "djeanlou",
     *         "price": "0.00",
     *         "createdAt": "2016-12-03 00:12:23",
     *         "lastUpdate": null,
     *         "seller": {
     *           "id": 1,
     *           "pseudo": "djeanlou",
     *           "location": null
     *          }
     *       },{
     *          "id": 34,
     *          "title": "Xbox One",
     *          "description": "djeanlou",
     *          "price": "90.00",
     *          "createdAt": "2016-12-03 00:14:33",
     *          "lastUpdate": null,
     *          "seller": {
     *            "id": 1,
     *            "pseudo": "djeanlou",
     *            "location": null
     *          }
     *        }
     *      ],
     *      "pagination": {
     *        "current":3,
     *        "first":1,
     *        "last":3,
     *        "prev":2,
     *        "next":3,
     *        "total_pages":3,
     *        "total_items":5
     *       }
     *     }
     *
     *
     * @Route("/classified_advertisements/")
     * @Route("/classified_advertisements/{page}", defaults={"page" = 1})
     * @Method({"GET"})
     * curl -XGET -d '' http://127.0.0.1:8000/app_dev.php/api/classified_advertisements/1
     * @ApiDoc(
     *   description="Get list of classified advertisements",
     *   section="Classified avertisements",
     *   filters={
     *     {"name"="page", "dataType"="integer", "requirement"="\d+", "description"="Page number", "default"=1},
     *   }
     * )
     */
    public function getClassifiedAdvertisementsAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $request = $this->container->get('request');

      $nbItemsPerPage = 2;
      $currentPage = (int)$this->getRequest()->get('page') ?: 1;
      if (!is_int($currentPage) || $currentPage > 1000) {
        $currentPage = 1;
      }
      
      $dql = "SELECT p FROM AdminAPIBundle:ClassifiedAdvertisement p WHERE p.isActive=1";
      $query = $em->createQuery($dql)
                     ->setFirstResult($nbItemsPerPage * ($currentPage - 1))
                     ->setMaxResults($nbItemsPerPage);
      $paginator = new Paginator($query, $fetchJoinCollection = true);

      $totalPages = round(count($paginator)/$nbItemsPerPage);
      $nextPage = ($currentPage + 1 > $totalPages) ? $totalPages : $currentPage + 1;
      $prevPage = ($currentPage - 1 < 1) ? 1 : $currentPage - 1;

      $classifiedAdvertisements = $query->getResult();

      $encoders = array(new JsonEncoder());
      $normalizer = new ObjectNormalizer();

      $serializer = new Serializer(array($normalizer), $encoders);

      $properClassifiedAdvertisements = array();
      foreach ($classifiedAdvertisements as $key => $classifiedAdvertisement) {
        $currentUser = $this->createProperUserObject($classifiedAdvertisement->getSeller());

        $lastUpdate = null;
        if ($classifiedAdvertisement->getLastUpdate()) {
          $lastUpdate = $classifiedAdvertisement->getLastUpdate()->format('Y-m-d H:i:s');
        }
        
        $properClassifiedAdvertisements[] = array(
            'id'          => $classifiedAdvertisement->getId(),
            'title'       => $classifiedAdvertisement->getTitle(),
            'description' => $classifiedAdvertisement->getDescription(),
            'price'       => $classifiedAdvertisement->getPrice(),
            'createdAt'   => $classifiedAdvertisement->getCreatedAt()->format('Y-m-d H:i:s'),
            'lastUpdate'  => $lastUpdate,
            'seller'      => $currentUser,
        );
      }

      $objectResponse = array(
                          'status_code' => Response::HTTP_OK,
                          'data' => $properClassifiedAdvertisements,
                          'pagination' => array(
                            'current'     => $currentPage,
                            'first'       => 1,
                            'last'        => $totalPages,
                            'prev'        => $prevPage,
                            'next'        => $nextPage,
                            'total_pages' => $totalPages,
                            'total_items' => count($paginator),
                          )
                        );

      $serializeDatas = $serializer->serialize($objectResponse, 'json' );

      $response = new Response($serializeDatas);
      // $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

    /**
     * @Route("/classified_advertisement")
     * @Method({"POST"})
     *
     * @example 
     * curl -XPOST -d "title=Xbox One&description=Console Xbox One presque neuve. \nvends car je ne l'aime plus.&price=250.90€&seller=djeanlou" http://127.0.0.1:8000/app_dev.php/api/classified_advertisement | pbcopy
     * 
     * curl -XPOST -d '{"username":"xyz","password":"xyz"}' http://127.0.0.1:8000/app_dev.php/api/classified_advertisement | pbcopy
     *
     * @ApiDoc(
     *   description="Create a classified advertisement",
     *   ressource=false,
     *   section="Classified avertisements",
     *   requirements={
     *     {"name"="title", "dataType"="String", "required"=true, "description"="Title of the classified advertisement"},
     *     {"name"="description", "dataType"="String", "required"=false, "description"="Description of the item sold"},
     *     {"name"="price", "dataType"="float", "required"=false, "description"="Price of the item sold"},
     *     {"name"="seller", "dataType"="Integer / String", "required"=true, "description"="User id or user username"},
     *   }
     * )
     */
    public function createClassifiedAdvertisementAction(Request $request)
    {
      $securityContext = $this->container->get('security.authorization_checker');

      if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        dump("gerggerge");
      } else {
        dump("gerggerge ot");
      }

      $em = $this->getDoctrine()->getManager();

      $title       = $this->getRequest()->get('title');
      $seller      = $this->getRequest()->get('seller');
      $description = $this->getRequest()->get('description');
      $price       = $this->getRequest()->get('price');

      $id = $this->getRequest()->get('seller');
      if (is_int($id)) {
        $selector = array('id' => $id);
      } else {
        $selector = array('username' => $id);
      }

      $seller = $em->getRepository('AdminAPIBundle:User')
                                       ->findOneBy($selector);

      if (!$seller) {
        $response = array(
          'data' => null,
          'status_code' => Response::HTTP_NOT_FOUND,
          'errors' => array(
            'name' => ''
          )
        );
      } else {
        $classifiedAdvertisement = new ClassifiedAdvertisement();
        $classifiedAdvertisement->setTitle($title);
        $classifiedAdvertisement->setSeller($seller);
        $classifiedAdvertisement->setDescription($description);
        $classifiedAdvertisement->setPrice($price);

        $seller->addClassifiedAdvertisement($classifiedAdvertisement);

        $em->persist($classifiedAdvertisement);
        $em->persist($seller);
        $em->flush();

        $currentUser = $this->createProperUserObject($seller);

        $response = array(
          'data' => array(
            'ressource' => array(
              'id'          => $classifiedAdvertisement->getId(),
              'title'       => $classifiedAdvertisement->getTitle(),
              'description' => $classifiedAdvertisement->getDescription(),
              'price'       => $classifiedAdvertisement->getPrice(),
              'createdAt'   => $classifiedAdvertisement->getCreatedAt()->format('Y-m-d H:i:s'),
              'seller'      => $currentUser,
            ),
            'flash_message' => Helpers::createFlashMessage('Ressource created', 'success', 1000)
          ),
          'status_code'=> Response::HTTP_CREATED
        );
      }

      

      return new JSONResponse($response);
    }

    /**
     * @Route("/classified_advertisement/{id}")
     * @Method({"DELETE"})
     *
     * @ApiDoc(
     *   description="Delete a classified advertisement",
     *   ressource=false,
     *   section="Classified avertisements",
     *   requirements={
     *     {"name"="id", "dataType"="Integer", "required"=true, "description"="id the classified advertisement"},
     *   },
     * )
     */
    public function deleteClassifiedAdvertisementAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();

      $id = (int)$this->getRequest()->get('id') ?: 1;
      
      $entity = $em->getRepository('AdminAPIBundle:ClassifiedAdvertisement')
                   ->find($id);
      $response = array();

      if ($entity) {
        $em->remove($entity);
        $em->flush();

        $response = array(
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Element removed', 'success', 1000)
          ),
          'status_code'=> Response::HTTP_CREATED
        );
      } else {
        $response = array(
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Ressource not found', 'error', 1004)
          ),
          'status_code'=> Response::HTTP_NOT_FOUND
        );
      }

      return new JSONResponse($response);
    }
}
