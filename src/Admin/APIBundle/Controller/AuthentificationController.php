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


class AuthentificationController extends Controller
{
    
    /**
     * ### Example response ###
     * {
     *   "data":{"token":"__my_token"},
     *   "status_code":200,
     *   "errors":null
     *  }
     *  
     * @Route(path="/get_token", name="token_authentication")
     * @Method({"POST"})
     * 
     * @ApiDoc(
     *   description="Get authentification token for user",
     *   ressource=false,
     *   section="Authentification/Subscription",
     *   requirements={
     *     {"name"="username", "dataType"="String", "required"=true, "description"="user username"},
     *   },
     * )
     */
    public function tokenAuthentication(Request $request)
    {
        $username = $this->getRequest()->get('username');
        $password = $this->getRequest()->get('password');

        $user = $this->getDoctrine()->getRepository('AdminAPIBundle:User')
            ->findOneBy(['username' => $username]);

        $response = array();

        // User not exists
        if(!$user) {
          $response = array(
            'data' => array(
              'flash_message' => Helpers::createFlashMessage('User not found', 'error', 1004)
            ),
            'status_code' => Response::HTTP_NOT_FOUND,
            'errors' => array()
          );

            // throw $this->createNotFoundException();
        }

        // Test password
        if(!$this->get('security.password_encoder')->isPasswordValid($user, $password)) {
          $response = array(
            'data' => array(
              'flash_message' => Helpers::createFlashMessage('Password incorrect', 'error', 1004)
            ),
            'status_code' => Response::HTTP_UNAUTHORIZED,
            'errors' => array()
          );
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
                      ->encode(['username' => $user->getUsername()]);

        $response = array(
          'data' => array('token' => $token),
          'status_code' => Response::HTTP_UNAUTHORIZED,
          'errors' => null
        );


        return new JsonResponse($response);
    }
}
