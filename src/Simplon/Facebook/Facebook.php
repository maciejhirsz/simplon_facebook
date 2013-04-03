<?php

    namespace Simplon\Facebook;

    class Facebook
    {
        private $_appId;
        private $_appSecret;
        private $_appPermissions;

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
         * @param bool $retry
         * @return array
         * @throws \Exception
         */
        public function getUserData($accessToken, $retry = TRUE)
        {
            try
            {
                return $this->_requestGraph('me', $accessToken);
            }
            catch(\Exception $e)
            {
                // retry in case facebook wasnt quick enough to update accessToken remotely
                if($retry !== FALSE && strpos($e->getMessage(), 'CODE=190') !== FALSE)
                {
                    // wait 2 seconds and try again
                    sleep(2);

                    return $this->getUserData($accessToken, FALSE);
                }

                throw new \Exception($e->getMessage(), 500);
            }
        }

        // ##########################################

        /**
         * @param $resource
         * @param $accessToken
         * @return array
         * @throws \Exception
         */
        protected function _requestGraph($resource, $accessToken)
        {
            // make sure that we have what we need
            if(! $resource || ! $accessToken)
            {
                throw new \Exception(__CLASS__ . ': Cannot request graph. Missing either $resource or $accessToken.', 500);
            }

            // request FB graph
            $responseJson = \CURL::init("https://graph.facebook.com/{$resource}/?access_token={$accessToken}")
                ->setReturnTransfer(TRUE)
                ->execute();

            // get array
            $data = json_decode($responseJson, TRUE);

            // handle error response
            if(isset($data['error']))
            {
                $errorMetas = ['Failed graph request.'];

                if(isset($data['message']))
                {
                    $errorMetas[] = "MSG={$data['message']}";
                }

                if(isset($data['type']))
                {
                    $errorMetas[] = "TYPE={$data['type']}";
                }

                if(isset($data['code']))
                {
                    $errorMetas[] = "CODE={$data['code']}";
                }

                if(isset($data['error_subcode']))
                {
                    $errorMetas[] = "SUBCODE={$data['error_subcode']}";
                }

                throw new \Exception(__CLASS__ . ': ' . join(' --> ', $errorMetas), 500);
            }

            // return data
            return $data;
        }
    }
