<?php

namespace Admin\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Admin\APIBundle\Controller\Helpers as Helpers;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Admin\APIBundle\Entity\User;


class AuthentificationController extends Controller
{
    
    /**
     * ### Example response ###
     * {
     *   "data":{"token":"__my_token__"},
     *   "status_code":200,
     *   "errors":null
     * }
     *
     * curl auth
     * curl -X POST http://localhost:8000/api/get_token -d username=test1 -d password=password! | pbcopy
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
     *     {"name"="password", "dataType"="String", "required"=true, "description"="user password"},
     *   },
     * )
     */
    public function getToken(Request $request)
    {
      $username = $this->getRequest()->get('username');
      $password = $this->getRequest()->get('password');

      $user = $this->getDoctrine()
                   ->getRepository('AdminAPIBundle:User')
                   ->findOneBy(['username' => $username]);

      $response = array();

      // User not exists
      if(!$user) {
        $response = array(
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('User not found', 'error', 1004)
          ),
          'status_code' => Response::HTTP_NOT_FOUND,
          'errors' => null
        );
      }

      // Test password
      if(!$this->get('security.password_encoder')->isPasswordValid($user, $password)) {
        $response = array(
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Password incorrect', 'error', 1004)
          ),
          'status_code' => Response::HTTP_UNAUTHORIZED,
          'errors' => null
        );
      } else {
        $token = $this->get('lexik_jwt_authentication.encoder')
                      ->encode(['username' => $user->getUsername()]);

        $response = array(
          'data' => array('token' => $token),
          'status_code' => Response::HTTP_ACCEPTED,
          'errors' => null
        );
      }

      return new JsonResponse($response, $response['status_code']);
    }

    /**
     * ### Example response ###
     * {
     *   "data":{"token":"__my_token__"},
     *   "status_code":200,
     *   "errors":null
     * }
     *
     * curl auth
     * curl -X POST http://localhost:8000/api/get_token -d username=djeanlou -d password=123456789C | pbcopy
     *  
     * @Route(path="/sign_up", name="sign_up")
     * @Method({"POST"})
     * 
     * @ApiDoc(
     *   description="Allows user to sign up to the service",
     *   ressource=false,
     *   section="Authentification/Subscription",
     *   requirements={
     *     {"name"="username", "dataType"="String", "required"=true, "description"="user username"},
     *     {"name"="password", "dataType"="String", "required"=true, "description"="user password"},
     *     {"name"="password_confirmation", "dataType"="String", "required"=true, "description"="user password confirmation"},
     *     {"name"="email", "dataType"="String", "required"=true, "description"="user mail"},
     *   },
     * )
     */
    public function signUp(Request $request)
    {
      $response = array();

      try {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $passwordConfirmation = $request->request->get('password_confirmation');
        $email = $request->request->get('email');

        if ($password !== $passwordConfirmation) {
          $response = array(
            'data' => array(
              'flash_message' => Helpers::createFlashMessage('Passwords doesn\'t match', 'error', 1006)
            ),
            'status_code'=> Response::HTTP_NOT_FOUND,
            'errors' => [
              array('name' => $e->getMessage())
            ]
          );
          return new JSONResponse($response, $response['status_code']);
        }
        

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setEmail($email);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $currentUser = $user->getSerializableDatas();

        $response = array(
          'data' => array(
            'ressource' => $currentUser,
            'flash_message' => Helpers::createFlashMessage('Ressource created', 'success', 1000)
          ),
          'status_code'=> Response::HTTP_CREATED
        );

      } catch (\Exception $e) {
        $response = array(
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Ressource not found', 'error', 1004)
          ),
          'status_code'=> Response::HTTP_NOT_FOUND,
          'errors' => [
            array('name' => $e->getMessage())
          ]
        );
      }

      return new JSONResponse($response, $response['status_code']);
    }
}
