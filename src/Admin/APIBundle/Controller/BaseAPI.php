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



use Doctrine\ORM\Tools\Pagination\Paginator;


class BaseAPI extends Controller
{
  public function retrieveClassifiedAdvertisements(Request $request, $seller="all")
  {
    $em = $this->getDoctrine()->getManager();

    $nbItemsPerPage = 2;
    $currentPage = (int)$this->getRequest()->get('page') ?: 1;
    if (!is_int($currentPage)) {
      $currentPage = 1;
    }

    $dql = 'SELECT p FROM AdminAPIBundle:ClassifiedAdvertisement p WHERE p.isActive=1 ORDER BY p.createdAt ASC';

    if ($seller !== 'all') {
      $dql = 'SELECT p FROM AdminAPIBundle:ClassifiedAdvertisement p WHERE p.isActive=1 AND p.seller= :seller ORDER BY p.createdAt ASC';
    }

    $query = $em->createQuery($dql)
                   ->setFirstResult($nbItemsPerPage * ($currentPage - 1))
                   ->setMaxResults($nbItemsPerPage);
    if($seller !== 'all') {
      $query->setParameters(array(
            'seller' => $seller
        ));
    }

    $paginator = new Paginator($query, $fetchJoinCollection = true);

    $totalPages = round(count($paginator)/$nbItemsPerPage);
    $nextPage = ($currentPage + 1 > $totalPages) ? $totalPages : $currentPage + 1;
    $prevPage = ($currentPage - 1 < 1) ? 1 : $currentPage - 1;

    $classifiedAdvertisements = $query->getResult();

    $properClassifiedAdvertisements = array();
    foreach ($classifiedAdvertisements as $key => $classifiedAdvertisement) {
      $classifiedAdvertisementSeller = $this->createProperUserObject($classifiedAdvertisement->getSeller());

      $properClassifiedAdvertisements[] = $classifiedAdvertisement->getSerializableDatas($classifiedAdvertisementSeller);
    }

    $response = array(
                  'status_code' => Response::HTTP_OK,
                  'data' => ["list" => $properClassifiedAdvertisements],
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

    if($seller !== 'all') {
      $response['data']['seller'] = $seller->getSerializableDatas();
    }
    return $response;
  }

  public function isUserTokenValid($token, Request $request = null)
  {
    // if (!$request->isXmlHttpRequest()) {
    //   # code...
    // }
    try {
      $user = $this->get('lexik_jwt_authentication.encoder')->decode($token);
      return $user;
    } catch (\Exception $e) {
      return false;
    }
  }
}
