<?php

namespace Admin\APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class Helpers
{
  public static function createFlashMessage($message, $type, $APICode) {
    return array(
      'name' => $message,
      'type' => $type,
      'api_code' => $APICode
    );
  }

  public static function manageInvalidUserToken() {
    $response = array(
      'data' => array(
        'flash_message' => Helpers::createFlashMessage('Invalid Token', 'error', 1004)
      ),
      'status_code'=> Response::HTTP_UNAUTHORIZED,
      'errors' => [
        array('name' => '')
      ]
    );
    return $response;
  }
}
