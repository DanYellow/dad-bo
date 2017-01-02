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

  /**
   * Returns imagePath
   * @param  ClassifiedAdvertisement $classifiedAdvertisement [description]
   * @return [type]                                           [description]
   */
  protected function retriveImagePath(ClassifiedAdvertisement $classifiedAdvertisement) {
    $image = $classifiedAdvertisement->getImage();
    
    if ($image) {
      $path = $image->getWebPath();
      $imagePath = $this->get('liip_imagine.cache.manager')
                        ->getBrowserPath($path, 'classified_advertisement_thumbnail');
      return $imagePath;
    } else {
      return null;
    }
  }

  /**
   * Retrieve ClassifiedAdvertisements
   * @param  Request $request        HTTP Request performed
   * @param  \Admin\APIBundle\Entity\User  $currentUser    Current user | Defaults = null
   * @param  boolean $dataForASeller Indicates if we want datas for one specific user
   * @return Object
   */
  public function retrieveClassifiedAdvertisements(Request $request, $currentUser=null, $dataForASeller=false)
  {
    $em = $this->getDoctrine()->getManager();
    $parameters = [];

    $nbItemsPerPage = 10;
    $currentPage = (int)$this->getRequest()->get('p') ?: 1;
    if (!is_int($currentPage)) {
      $currentPage = 1;
    }

    $dql = 'SELECT p FROM AdminAPIBundle:ClassifiedAdvertisement p';

    // User is in his admin part so he can see all of his ca even non active
    $accessFromBack = $this->getRequest()->get('mine', null);
    if ($accessFromBack) {
      $dql .= ' WHERE p.isActive IN (0, 1)';
    } else {
      $dql .= ' WHERE p.isActive=1';
    }

    if ($accessFromBack) {
      $dql .= ' AND p.seller=:seller';

      $parameters['seller'] = $currentUser;
    }

    $search = $this->getRequest()->get('q', null);
    if ($search) {
      $dql .= ' AND p.title LIKE :query';
      $parameters['query'] = '%' . $search . '%';
    }

    $category = $this->getRequest()->get('c', null);
    if ($category) {
      $dql .= ' AND p.category = :category';

      $parameters['category'] = $category;
    }

    $status = $this->getRequest()->get('s', null);
    if ($status) {
      $status = (int)$status - 1;
      $dql .= ' AND p.isActive = :status';

      $parameters['status'] = $status;
    }

    $dql .= ' ORDER BY p.createdAt DESC';
    
    $query = $em->createQuery($dql)
                   ->setFirstResult($nbItemsPerPage * ($currentPage - 1))
                   ->setMaxResults($nbItemsPerPage)
                   ->setParameters($parameters);

    $paginator = new Paginator($query, $fetchJoinCollection = true);

    $totalPages = round(count($paginator)/$nbItemsPerPage);
    $nextPage = ($currentPage + 1 > $totalPages) ? null : $currentPage + 1;
    $prevPage = ($currentPage - 1 < 1) ? null : $currentPage - 1;

    $classifiedAdvertisements = $query->getResult();

    $properClassifiedAdvertisements = array();
    foreach ($classifiedAdvertisements as $key => $classifiedAdvertisement) {
      $classifiedAdvertisementSeller = $classifiedAdvertisement->getSeller();
      $isMine = ($classifiedAdvertisementSeller === $currentUser) ? true : false;

      $classifiedAdvertisementObject = $classifiedAdvertisement->getSerializableDatas($classifiedAdvertisementSeller->getSerializableDatas());
      $classifiedAdvertisementObject['is_mine'] = $isMine;
      $classifiedAdvertisementObject['image'] = $this->retriveImagePath($classifiedAdvertisement);
      
      $properClassifiedAdvertisements[] = $classifiedAdvertisementObject;
    } 

    $response = array(
      'status_code' => Response::HTTP_OK,
      'success' => true,
      'data' => ['list' => $properClassifiedAdvertisements],
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

    if($dataForASeller && $currentUser) {
      $response['data']['seller'] = $currentUser->getSerializableDatas();
    }
    return $response;
  }

  public function isUserTokenValid($token, Request $request = null)
  {
    try {
      $user = $this->get('lexik_jwt_authentication.encoder')->decode($token);
      return $user;
    } catch (\Exception $e) {
      return null;
    }
  }
}
