<?php
namespace Phadhaar\Service;

use Psr\Http\Message\StreamInterface;

/**
 *
 * @author gourav sarkar
 *        
 */
class AdhaarAuthResponse
{

    protected $streamResponse;
    protected $simpleXml;
    /**
     */
    public function __construct(StreamInterface $response)
    {
        $this->streamResponse=$response->getContents();
        //var_dump($this->streamResponse);
        $this->simpleXml=new \SimpleXMLElement( $this->streamResponse);
    }
    
    public function getRawResponse()
    {
        return $this->streamResponse;
    }
    
    public function getXmlResponse()
    {
        return $this->simpleXml;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isAuthenticated()
    {
        return strcasecmp($this->simpleXml['ret'], 'y')==0 ? true: false;
    }

}

