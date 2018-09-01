<?php
namespace src\Service;

/**
 *
 * @author gourav sarkar
 *        
 */
class AdhaarAuthResponse
{

    protected $rawResponse;
    /**
     */
    public function __construct($response)
    {
        $this->rawResponse=$response;
    }
    
    public function getRawResponse()
    {
        return $this->rawResponse;
    }
    
    public function getRawResponseBody()
    {
        
    }
}

