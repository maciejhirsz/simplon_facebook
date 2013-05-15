<?php

    namespace Simplon\Facebook;

    class Facebook
    {
        private $_appId;
        private $_appSecret;
        private $_appPermissions;
        private $_graphUrl = "https://graph.facebook.com";

        private $_classErrorCode = 10000;
        private $_fetchErrorCode = 20000;

        // ##########################################

        /**
         * @param $appId
         *
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
         *
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
         *
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
         *
         * @return bool
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

            if (isset($response['access_token']))
            {
                return $response['access_token'];
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $accessToken
         * @param bool $retry
         *
         * @return FacebookUserGraphVo
         * @throws FacebookException
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
            catch (FacebookException $e)
            {
                // retry in case facebook wasnt quick enough to update accessToken remotely
                if ($retry !== FALSE && $e->getCode() !== 190)
                {
                    sleep(3); // lets be sure and wait 3 seconds

                    return $this->getUserData($accessToken, FALSE);
                }

                throw new FacebookException($e->getMessage(), 'FetchError', $this->_fetchErrorCode);
            }
        }

        // ##########################################

        /**
         * @param $accessToken
         * @param $actionType
         * @param $objectType
         * @param $objectValue
         *
         * @return bool
         * @throws FacebookException
         */
        public function sendOpenGraphItem($accessToken, $actionType, $objectType, $objectValue)
        {
            $actionType = strtolower($actionType);
            $objectType = strtolower($objectType);

            if (strpos($actionType, ':') === FALSE)
            {
                throw new FacebookException(__METHOD__ . ": OG action-type format is invalid. Sample valid format: myapp:like", 'ClassError', $this->_classErrorCode);
            }

            $params = [
                'access_token' => $accessToken,
                'method'       => 'POST',
                $objectType    => $objectValue,
            ];

            $data = $this->_submitToGraph("/me/{$actionType}", $params);

            // return graph id
            if (isset($data['id']))
            {
                return $data['id'];
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $accessToken
         * @param $graphItemId
         *
         * @return array|mixed
         * @throws FacebookException
         */
        public function removeOpenGraphItem($accessToken, $graphItemId)
        {
            if (empty($graphItemId))
            {
                throw new FacebookException(__METHOD__ . ": Missing graphItemId", 'ClassError', $this->_classErrorCode);
            }

            $params = [
                'access_token' => $accessToken,
                'method'       => 'DELETE',
            ];

            return $this->_submitToGraph("/{$graphItemId}", $params);
        }

        // ##########################################

        /**
         * @param $response
         *
         * @return array|mixed
         */
        protected function _parseGraphResponse($response)
        {
            // try json
            $data = json_decode($response, TRUE);

            // get data from string if NOT-JSON response
            if (is_null($data))
            {
                $data = [];
                parse_str($response, $data);
            }

            return $data;
        }

        // ##########################################

        /**
         * @param $resourcePath
         * @param array $params
         *
         * @return array|mixed
         * @throws FacebookException
         */
        protected function _requestGraph($resourcePath, array $params)
        {
            // make sure that we have what we need
            if (!$resourcePath)
            {
                throw new FacebookException("Cannot request graph due to missing resourcePath.", 'ClassError', $this->_classErrorCode);
            }

            // build URL
            $graphUrl = trim($this->_graphUrl, '/') . '/' . trim($resourcePath, '/') . '?' . http_build_query($params);

            // request FB graph
            $response = \CURL::init($graphUrl)
                ->setReturnTransfer(TRUE)
                ->execute();

            // parse response
            $data = $this->_parseGraphResponse($response);

            // handle error response
            if (isset($data['error']))
            {
                $this->_handleErrorResponse($data);
            }

            // return data
            return $data;
        }

        // ##########################################

        /**
         * @param $resourcePath
         * @param array $params
         *
         * @return array|mixed
         * @throws FacebookException
         */
        protected function _submitToGraph($resourcePath, array $params)
        {
            // make sure that we have what we need
            if (!$resourcePath)
            {
                throw new FacebookException("Cannot submit to graph due to missing resourcePath.", 'ClassError', $this->_classErrorCode);
            }

            // build URL
            $graphUrl = trim($this->_graphUrl, '/') . '/' . trim($resourcePath, '/');

            // request FB graph
            $response = \CURL::init($graphUrl)
                ->setPost(TRUE)
                ->setPostFields($params)
                ->setReturnTransfer(TRUE)
                ->execute();

            // parse response
            $data = $this->_parseGraphResponse($response);

            // handle error response
            if (isset($data['error']))
            {
                $this->_handleErrorResponse($data);
            }

            return $data;
        }

        // ######################################

        /**
         * @param $response
         *
         * @throws FacebookException
         */
        protected function _handleErrorResponse($response)
        {
            $errorMessage = NULL;
            $errorType = NULL;
            $errorCode = NULL;
            $errorSubcode = NULL;

            if (isset($response['error']['message']))
            {
                $errorMessage = $response['error']['message'];
            }

            if (isset($response['error']['type']))
            {
                $errorType = $response['error']['type'];
            }

            if (isset($response['error']['code']))
            {
                $errorCode = $response['error']['code'];
            }

            if (isset($response['error']['error_subcode']))
            {
                $errorSubcode = $response['error']['error_subcode'];
            }

            throw new FacebookException($errorMessage, $errorType, $errorCode, $errorSubcode);
        }
    }