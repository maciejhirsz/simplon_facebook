<?php

  namespace Simplon\Facebook;

  class Facebook
  {
    /** @var Facebook */
    private static $_instance;

    /** @var \Facebook */
    private $_facebookSdk;

    private $_appId;
    private $_appSecret;
    private $_appPermissions;

    // ##########################################

    /**
     * @static
     * @return Facebook
     */
    public static function getInstance()
    {
      if(! Facebook::$_instance)
      {
        Facebook::$_instance = new Facebook();
      }

      return Facebook::$_instance;
    }

    // ##########################################

    /**
     * @param $appId
     * @return Facebook
     */
    public function setAppId($appId)
    {
      $this->_appId = $appId;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getAppId()
    {
      return $this->_appId;
    }

    // ##########################################

    /**
     * @param $appSecret
     * @return Facebook
     */
    public function setAppSecret($appSecret)
    {
      $this->_appSecret = $appSecret;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
      return $this->_appSecret;
    }

    // ##########################################

    /**
     * @param array $appPermissions
     * @return Facebook
     */
    public function setAppPermissions(array $appPermissions)
    {
      $this->_appPermissions = $appPermissions;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getAppPermissions()
    {
      return $this->_appPermissions;
    }

    // ##########################################

    /**
     * @return array
     */
    public function getFacebookConfig()
    {
      return array(
        'appId'       => $this->getAppId(),
        'secret'      => $this->getAppSecret(),
        'permissions' => $this->getAppPermissions(),
      );
    }

    // ##########################################

    /**
     * @return \Facebook
     */
    public function getFacebookSdk()
    {
      if(! $this->_facebookSdk)
      {
        $facebookConfig = $this->getFacebookConfig();
        $this->_facebookSdk = new \Facebook($facebookConfig);
      }

      return $this->_facebookSdk;
    }

    // ##########################################

    /**
     * @return string
     * @throws \Exception
     */
    public function getUserId()
    {
      try
      {
        return $this
          ->getFacebookSdk()
          ->getUser();
      }
      catch(\Exception $e)
      {
        throw new \Exception('Simplon/Facebook: Cannot access facebookUserId: ' . $e);
      }
    }

    // ##########################################

    /**
     * @param $facebookUserId
     * @return mixed
     * @throws \Exception
     */
    public function getRemoteUserData($facebookUserId)
    {
      try
      {
        return $this
          ->getFacebookSdk()
          ->api('/' . $facebookUserId);
      }
      catch(\Exception $e)
      {
        throw new \Exception('Simplon/Facebook: Remote request failed:' . $e);
      }
    }

    // ##########################################

    /**
     * @param $signedRequest
     * @param bool $extended
     */
    public function authViaSignedRequest($signedRequest, $extended = FALSE)
    {
      // we do not want to rely on cookies
      $_COOKIE = array();

      // set signed request
      $this
        ->getFacebookSdk()
        ->setSignedRequest($signedRequest);

      // extend access token (only once per session)
      if($extended === TRUE && ! isset($_SESSION['getExtendedAccessToken']))
      {
        $this
          ->getFacebookSdk()
          ->setExtendedAccessToken();

        $_SESSION['getExtendedAccessToken'] = TRUE;
      }
    }

    // ##########################################

    /**
     * @param $accessToken
     * @param bool $extended
     */
    public function authViaAccessToken($accessToken, $extended = FALSE)
    {
      // we do not want to rely on cookies
      $_COOKIE = array();

      // set signed request
      $this
        ->getFacebookSdk()
        ->setAccessToken($accessToken);

      // extend access token (only once per session)
      if($extended === TRUE && ! isset($_SESSION['getExtendedAccessToken']))
      {
        $this
          ->getFacebookSdk()
          ->setExtendedAccessToken();

        $_SESSION['getExtendedAccessToken'] = TRUE;
      }
    }
  }
