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

$encoders = array(new JsonEncoder());
$normalizers = array(new ObjectNormalizer());

$serializer = new Serializer($normalizers, $encoders);

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

        $classifiedAdvertisements = $em->getRepository('AdminAPIBundle:ClassifiedAdvertisement')
                                       ->findBy(array('isActive' => true));

        $encoders = array(new JsonEncoder());
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer(array($normalizer), $encoders);

        // $serializeDatas = $serializer->serialize($this->formatAPIResponse($classifiedAdvertisements), 'json', array('groups' => array('list')) );

        $serializeDatas = $serializer->normalize($this->formatAPIResponse($classifiedAdvertisements), null, array('groups' => array('list')) );
        dump($serializeDatas);
        $serializeDatas = $serializer->serialize($serializeDatas, 'json');
        // dump($classifiedAdvertisements);
        // $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        // $serializeDatas = $serializer->serialize($this->formatAPIResponse($classifiedAdvertisements), 'json');

        $response = new Response($serializeDatas);
        // $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/classified_advertisement")
     * @Method({"GET"}) POST in prod
     */
    public function createClassifiedAdvertisementAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();

      $id = $this->getRequest()->get('id');
      if (is_int($id)) {
        $selector = array('id' => $id);
      } else {
        $selector = array('username' => "djeanlou");
      }

      $seller = $em->getRepository('Admin\APIBundle\Entity\User')
                                       ->findOneBy($selector);

      $classifiedAdvertisement = new ClassifiedAdvertisement();
      $classifiedAdvertisement->setTitle("hello");
      $classifiedAdvertisement->setSeller($seller);

      $seller->addClassifiedAdvertisement($classifiedAdvertisement);

      dump($seller->getClassifiedAdvertisements());

      

      $em->persist($classifiedAdvertisement);
      $em->persist($seller);
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
