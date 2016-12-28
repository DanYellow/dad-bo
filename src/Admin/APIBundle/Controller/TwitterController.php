<?php
  namespace Admin\APIBundle\Controller;

  use Abraham\TwitterOAuth\TwitterOAuth;

  use Symfony\Bundle\FrameworkBundle\Controller\Controller;
  use Symfony\Component\HttpFoundation\Request;

  use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
  use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

  use Symfony\Component\HttpFoundation\JsonResponse;

  /**
  * 
  */
  class TwitterController extends Controller
  {
    
    /**
     * 
     * @Route("/random_tweet")
     * @Method({"GET"})
     * 
     */
    public function getCategories(Request $request)
    {
      $CONSUMER_KEY = $this->getParameter('twitter_consumer_key');
      $CONSUMER_SECRET = $this->getParameter('twitter_consumer_secret');

      $ACCESS_TOKEN = $this->getParameter('twitter_access_token');
      $ACCESS_TOKEN_SECRET = $this->getParameter('twitter_access_token_secret');

      $params = ['screen_name' => 'digitwitas', 'count' => 17];

      $connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);

      $content = $connection->get("statuses/user_timeline", $params);

      return new JSONResponse($content);
    }
  }
