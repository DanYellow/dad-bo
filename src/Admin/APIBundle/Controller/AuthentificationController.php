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
     * 
     * @Route(path="/get_token")
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
      $data = json_decode($request->getContent(), true);

      $username = $data['username'];
      $password = $data['password'];

      $user = $this->getDoctrine()
                   ->getRepository('AdminAPIBundle:User')
                   ->findOneBy(['username' => $username]);

      $response = array();
      // User not exists
      if(!$user) {
        $response = array(
          'success' => false,
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('User not found', 'error', 1009)
          ),
          'status_code' => Response::HTTP_NOT_FOUND,
          'errors' => null
        );

        return new JsonResponse($response, $response['status_code']);
      }

      // Test password
      if(!$this->get('security.password_encoder')->isPasswordValid($user, $password)) {
        $response = array(
          'success' => false,
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Password incorrect', 'error', 1006)
          ),
          'status_code' => Response::HTTP_UNAUTHORIZED,
          'errors' => null
        );
      } else {
        $token = $this->get('lexik_jwt_authentication.encoder')
                      ->encode(['username' => $user->getUsername()]);
        $expireDate = time() + (60 * 60);
        
        $response = array(
          'success' => true,
          'data' => array(
            'resource' => array('token' => $token, 'expire' => $expireDate, 
                                'pseudo' => $username, 'password' => 'guest'),
            'flash_message' => Helpers::createFlashMessage('Logged', 'success', 1011)
          ),
          'status_code' => Response::HTTP_ACCEPTED,
          'errors' => null
        );
      }

      return new JsonResponse($response, $response['status_code']);
    }

    /**
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
      $data = json_decode($request->getContent(), true);
      $response = array();

      try {
        $username             = $data['username'];
        $password             = $data['password'];
        $passwordConfirmation = $data['password_confirmation'];
        $email                = $data['email'];

        if ($password !== $passwordConfirmation) {
          $response = array(
            'success' => false,
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
        $user->setPlainPassword($password);
        $user->setEmail($email);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $currentUser = $user->getSerializableDatas();

        $response = array(
          'success' => true,
          'data' => array(
            'ressource' => $currentUser,
            'flash_message' => Helpers::createFlashMessage('Ressource created', 'success', 1010)
          ),
          'status_code'=> Response::HTTP_CREATED
        );

      } catch (\Exception $e) {
        $response = array(
          'success' => false,
          'data' => array(
            'flash_message' => Helpers::createFlashMessage($e->getMessage(), 'error', 1004)
          ),
          'status_code'=> Response::HTTP_NOT_FOUND,
          'errors' => [
            array('name' => $e->getMessage())
          ]
        );
      }

      return new JSONResponse($response, $response['status_code']);
    }

    /**
     *
     * @Route(path="/forgot_password", name="forgot_password")
     * @Method({"POST"})
     */
    public function forgotPassword(Request $request)
    {
      $data = json_decode($request->getContent(), true);
      $response = array();

      $email = $data['email'];
      $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

      if (is_null($user)) {
        $user = $this->get('fos_user.user_manager')->findUserByUsername($email);
      }

      if (is_null($user)) {
        $response = array(
          'success' => false,
          'data' => array(
            'flash_message' => Helpers::createFlashMessage('Unknown user', 'error', 1006)
          ),
          'status_code'=> Response::HTTP_NOT_FOUND,
          'errors' => [
            array('name' => null)
          ]
        );
        return new JSONResponse($response, $response['status_code']);
      }

      if (null === $user->getConfirmationToken()) {
        $tokenGenerator = $this->get('fos_user.util.token_generator');
        $user->setConfirmationToken($tokenGenerator->generateToken());
      }

      $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
      $user->setPasswordRequestedAt(new \DateTime());
      $this->get('fos_user.user_manager')->updateUser($user);

      $response = array(
        'success' => true,
        'data' => array(
          'flash_message' => Helpers::createFlashMessage('mail sent', 'success', 1014)
        ),
        'status_code'=> Response::HTTP_OK
      );

      return new JSONResponse($response, $response['status_code']);

    }
}
