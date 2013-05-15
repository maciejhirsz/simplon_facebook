<?php

    namespace Simplon\Facebook;

    class FacebookException extends \Exception
    {
        protected $type;
        protected $subcode;

        // ######################################

        public function __construct($message, $type, $code = 0, $subcode = 0)
        {
            $this->message = $message;
            $this->type = $type;
            $this->code = $code;
            $this->subcode = $subcode;
        }

        // ######################################

        public function getType()
        {
            return $this->type;
        }

        // ######################################

        public function getSubcode()
        {
            return $this->subcode;
        }
    }