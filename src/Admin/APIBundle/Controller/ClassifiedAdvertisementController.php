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
     * @Route("/classified_advertisements/{page}")
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

        $nbItemsPerPage = 2;
        $currentPage =  $this->getRequest()->get('page') ?: 1;

        
        $dql = "SELECT p FROM AdminAPIBundle:ClassifiedAdvertisement p WHERE p.isActive=1";
        $query = $em->createQuery($dql)
                       ->setFirstResult(0)
                       ->setMaxResults($nbItemsPerPage, $nbItemsPerPage * ($currentPage - 1));
        $paginator = new Paginator($query, $fetchJoinCollection = true);


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

        $objectResponse = array('data' => $properClassifiedAdvertisements);

        $serializeDatas = $serializer->serialize($objectResponse, 'json' );

        $response = new Response($serializeDatas);
        // $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/classified_advertisement")
     * @Method({"POST"}) POST in prod
     *
     * @example curl -XPOST -d "title=Xbox One&description=Console Xbox One presque neuve. \nvends car je ne l'aime plus.&price=250.90€&seller=djeanlou" http://127.0.0.1:8000/app_dev.php/api/classified_advertisement | pbcopy
     *
     * @ApiDoc(
     *   description="Create a classified advertisement",
     *   ressource=false,
     *   section="Classified avertisements",
     *   curl="refrefer",
     *   requirements={
     *     {"name"="title", "dataType"="String", "required"=true, "description"="Title of the classified advertisement"},
     *   },
     *   parameters={
     *     {"name"="title", "dataType"="String", "required"=true, "description"="Title of the classified advertisement"},
     *     {"name"="description", "dataType"="String", "required"=false, "description"="Description of the item sold"},
     *     {"name"="price", "dataType"="float", "required"=false, "description"="Price of the item sold"},
     *   }
     * )
     */
    public function createClassifiedAdvertisementAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();

      $title       = $this->getRequest()->get('title');
      $seller      = $this->getRequest()->get('seller');
      $description = $this->getRequest()->get('description');
      $price       = $this->getRequest()->get('price');

      $id = $this->getRequest()->get('seller');
      if (is_int($id)) {
        $selector = array('id' => $id);
      } else {
        $selector = array('username' => "djeanlou");
      }

      $seller = $em->getRepository('Admin\APIBundle\Entity\User')
                                       ->findOneBy($selector);

      $classifiedAdvertisement = new ClassifiedAdvertisement();
      $classifiedAdvertisement->setTitle($title);
      $classifiedAdvertisement->setSeller($seller);
      $classifiedAdvertisement->setDescription($seller);
      $classifiedAdvertisement->setPrice($price);

      $seller->addClassifiedAdvertisement($classifiedAdvertisement);

      $em->persist($classifiedAdvertisement);
      $em->persist($seller);
      $em->flush();

      $response = array(
        'data' => 'Ressource created'
      );

      return new JSONResponse($response);
    }

    /**
     * @Route("/classified_advertisement")
     * @Method({"GET"}) DELETE after
     */
    public function deleteClassifiedAdvertisementAction(Request $request)
    {

    }
}
