<?php

    namespace Simplon\Facebook;

    class Facebook
    {
        private $_appId;
        private $_appSecret;
        private $_appPermissions;
        private $_graphUrl = "https://graph.facebook.com";

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
            return [
                'appId'       => $this->getAppId(),
                'secret'      => $this->getAppSecret(),
                'permissions' => $this->getAppPermissions(),
            ];
        }

        // ##########################################

        /**
         * @param $accessToken
         * @return string|bool
         */
        public function getExtendedAccessToken($accessToken)
        {
            // request params
            $params = [
                'client_id'         => $this->getAppId(),
                'client_secret'     => $this->getAppSecret(),
                'grant_type'        => 'fb_exchange_token',
                'fb_exchange_token' => $accessToken,
            ];

            $response = $this->_requestGraph('/oauth/access_token', $params);

            if(isset($response['access_token']))
            {
                return $response['access_token'];
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $accessToken
         * @param bool $retry
         * @return FacebookUserGraphVo
         * @throws \Exception
         */
        public function getUserData($accessToken, $retry = TRUE)
        {
            try
            {
                // request params
                $params = [
                    'access_token' => $accessToken
                ];

                $data = $this->_requestGraph('/me', $params);

                return new FacebookUserGraphVo($data);
            }
            catch(\Exception $e)
            {
                // retry in case facebook wasnt quick enough to update accessToken remotely
                if($retry !== FALSE && stripos($e->getMessage(), 'CODE=190') !== FALSE)
                {
                    sleep(3); // lets be sure and wait 3 seconds

                    return $this->getUserData($accessToken, FALSE);
                }

                throw new \Exception(__METHOD__ . ": {$e->getMessage()}", 500);
            }
        }

        // ##########################################

        /**
         * @param $accessToken
         * @param $objectType
         * @param $objectAction
         * @param $objectActionValue
         * @return mixed
         * @throws \Exception
         */
        public function sendOpenGraphItem($accessToken, $objectType, $objectAction, $objectActionValue)
        {
            $objectType = strtolower($objectType);
            $objectAction = strtolower($objectAction);

            if(strpos($objectType, ':') === FALSE)
            {
                throw new \Exception(__METHOD__ . ": OG object type format is invalid. Sample valid format: myapp:like", 500);
            }

            try
            {
                $params = [
                    'access_token' => $accessToken,
                    'method'       => 'POST',
                    $objectAction  => $objectActionValue,
                ];

                return $this->_submitToGraph("/me/{$objectType}", $params);
            }
            catch(\Exception $e)
            {
                throw new \Exception($e->getMessage(), 500);
            }
        }

        // ##########################################

        /**
         * @param $resourcePath
         * @param array $params
         * @return array
         * @throws \Exception
         */
        protected function _requestGraph($resourcePath, array $params)
        {
            // make sure that we have what we need
            if(! $resourcePath)
            {
                throw new \Exception("Cannot request graph due to missing resourcePath.", 500);
            }

            // build URL
            $graphUrl = trim($this->_graphUrl, '/') . '/' . trim($resourcePath, '/') . '?' . http_build_query($params);

            // request FB graph
            $response = \CURL::init($graphUrl)
                ->setReturnTransfer(TRUE)
                ->execute();

            // read json
            $data = json_decode($response, TRUE);

            // get data from string if NOT-JSON response
            if(is_null($data))
            {
                $data = [];
                parse_str($response, $data);
            }

            // handle error response
            if(isset($data['error']))
            {
                $errorMetas = ['Failed graph request.'];

                if(isset($data['error']['message']))
                {
                    $errorMetas[] = "MSG={$data['error']['message']}";
                }

                if(isset($data['error']['type']))
                {
                    $errorMetas[] = "TYPE={$data['error']['type']}";
                }

                if(isset($data['error']['code']))
                {
                    $errorMetas[] = "CODE={$data['error']['code']}";
                }

                if(isset($data['error']['error_subcode']))
                {
                    $errorMetas[] = "SUBCODE={$data['error']['error_subcode']}";
                }

                throw new \Exception(join(' --> ', $errorMetas), 500);
            }

            // return data
            return $data;
        }

        // ##########################################

        /**
         * @param $resourcePath
         * @param array $params
         * @return mixed
         * @throws \Exception
         */
        protected function _submitToGraph($resourcePath, array $params)
        {
            // make sure that we have what we need
            if(! $resourcePath)
            {
                throw new \Exception("Cannot submit to graph due to missing resourcePath.", 500);
            }

            // build URL
            $graphUrl = trim($this->_graphUrl, '/') . '/' . trim($resourcePath, '/');

            // request FB graph
            $response = \CURL::init($graphUrl)
                ->setPost(TRUE)
                ->setPostFields($params)
                ->setReturnTransfer(TRUE)
                ->execute();

            return $response;
        }
    }