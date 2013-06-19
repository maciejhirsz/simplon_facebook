<?php

namespace Simplon\Facebook;

class FacebookPageGraphVo
{
    protected $_id;
    protected $_isPage = false;

    // ##########################################

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $val)
        {
            switch ($key)
            {
                case 'id':
                    $this->_id = $val;
                    break;

                case 'likes':
                    $this->_isPage = true;

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
     * @return bool
     */
    public function getIsPage()
    {
        return $this->_isPage;
    }

}