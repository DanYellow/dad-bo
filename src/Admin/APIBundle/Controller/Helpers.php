<?php

namespace Admin\APIBundle\Controller;

class Helpers
{
  public static function createFlashMessage($message, $type, $APICode) {
    return array(
      'name' => $message,
      'type' => $type,
      'api_code' => $APICode
    );
  }
}
