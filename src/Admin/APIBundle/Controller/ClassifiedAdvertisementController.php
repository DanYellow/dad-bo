<?php

namespace Admin\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Admin\APIBundle\Entity\ClassifiedAdvertisement as ClassifiedAdvertisement;

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

    /**
     * @Route("/classified_advertisements")
     * @Method({"GET"})
     */
    public function getClassifiedAdvertisementsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $classifiedAdvertisements = $em->getRepository('Admin\APIBundle\Entity\ClassifiedAdvertisement')
                                       ->findBy(array('isActive' => true));

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $serializeDatas = $serializer->serialize($this->formatAPIResponse($classifiedAdvertisements), 'json');

        dump($request);
        return new Response($serializeDatas);
    }

    /**
     * @Route("/classified_advertisement")
     * @Method({"GET"}) POST après
     */
    public function createClassifiedAdvertisementAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();

      $classifiedAdvertisement = new ClassifiedAdvertisement();

      $id = $this->getRequest()->get('id');
      if (is_int($id)) {
        $selector = array('id' => $id);
      } else {
        $selector = array('username' => "djeanlou");
      } 

      $seller = $em->getRepository('Admin\APIBundle\Entity\User')
                                       ->findOneBy($selector);

      $em = $this->getDoctrine()->getManager();

      $classifiedAdvertisement->setTitle("hello");
      $classifiedAdvertisement->setSeller($seller);

      $em->persist($classifiedAdvertisement);
      $em->flush();

      return new Response("hello");
    }

    /**
     * @Route("/classified_advertisement")
     * @Method({"GET"}) DELETE after
     */
    public function deleteClassifiedAdvertisementAction(Request $request)
    {

    }
}
