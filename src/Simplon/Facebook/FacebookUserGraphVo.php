<?php

    namespace Simplon\Facebook;

    class FacebookUserGraphVo
    {
        protected $_id;
        protected $_username;
        protected $_firstName;
        protected $_lastName;
        protected $_fullName;
        protected $_email;
        protected $_locale;
        protected $_gender;
        protected $_profileUrl;
        protected $_updatedTime;
        protected $_timezone;
        protected $_verified;

        // ##########################################

        public function __construct(array $data)
        {
            foreach ($data as $key => $val)
            {
                switch ($key)
                {
                    case 'id':
                        $this->_id = $val;
                        break;

                    case 'username':
                        $this->_username = $val;
                        break;

                    case 'first_name':
                        $this->_firstName = $val;
                        break;

                    case 'last_name':
                        $this->_lastName = $val;
                        break;

                    case 'name':
                        $this->_fullName = $val;
                        break;

                    case 'email':
                        $this->_email = $val;
                        break;

                    case 'locale':
                        $this->_locale = $val;
                        break;

                    case 'gender':
                        $this->_gender = $val;
                        break;

                    case 'link':
                        $this->_profileUrl = $val;
                        break;

                    case 'updated_time':
                        $this->_updatedTime = $val;
                        break;

                    case 'verified':
                        $this->_verified = $val;
                        break;

                    default:
                }
            }
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getId()
        {
            return $this->_id;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getUsername()
        {
            return $this->_username;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getFirstName()
        {
            return $this->_firstName;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getLastName()
        {
            return $this->_lastName;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getFullName()
        {
            return $this->_fullName;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getEmail()
        {
            return $this->_email;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getLocale()
        {
            return $this->_locale;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getGender()
        {
            return $this->_gender;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getProfileUrl()
        {
            return $this->_profileUrl;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getUpdatedTime()
        {
            return $this->_updatedTime;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getTimezone()
        {
            return $this->_timezone;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getVerified()
        {
            return $this->_verified;
        }
    }
