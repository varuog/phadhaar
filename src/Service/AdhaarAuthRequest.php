<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Phadhaar\Service;

use Phadhaar\Exception\AdhaarNotSupportedException;
use Phadhaar\Model\User;
use Carbon\Carbon;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Description of AdhaarAuthService
 *
 * @todo cet,xml signer p123 file apth check
 * @author gourav sarkar
 */
class AdhaarAuthRequest implements \Serializable
{

    const API_VERSION_2 = '2.0';

    const TEST_ADHAAR_ENDPOINT = 'https://auth.uidai.gov.in';

    const TEST_ASA_LICENSE_KEY = 'MMxNu7a6589B5x5RahDW-zNP7rhGbZb5HsTRwbi-VVNxkoFmkHGmYKM';

    const TEST_AUA_LICENSE_KEY = 'MBni88mRNM18dKdiVyDYCuddwXEQpl68dZAGBQ2nsOlGMzC9DkOVL5s';

    const TEST_AUA_CODE = 'public';

    const TEST_ASA_CODE = 'public';

    const CERT_PATH = 'src/Storage/uidai_auth_stage.cer';

    const XML_SIGNER_PATH = 'src/Storage/Staging_Signature_PrivateKey.p12';

    /**
     * <Auth uid="" rc="" tid="" ac="" sa="" ver="" txn="" lk="">
     * <Uses pi="" pa="" pfa="" bio="" bt="" pin="" otp=""/>
     * <Meta udc="" rdsId="" rdsVer="" dpId="" dc="" mi="" mc="" />
     * <Skey ci="">encrypted and encoded session key</Skey>
     * <Hmac>SHA-256 Hash of Pid block, encrypted and then encoded</Hmac>
     * <Data type="X|P">encrypted PID block</Data>
     * <Signature>Digital signature of AUA</Signature>
     * </Auth
     */
    protected $xmlWriter;

    protected $xmlSigner;

    protected $config;

    protected $user;

    protected $enableProtoBuf = false;

    // X/P

    /**
     *
     * @var String
     */
    protected $consent = 'Y';

    protected $tid = '';

    protected $auaLicenseKey = self::TEST_AUA_LICENSE_KEY;

    protected $auaCode = self::TEST_AUA_CODE;

    protected $asaCode = self::TEST_ASA_CODE;

    protected $apiVersion = self::API_VERSION_2;

    protected $transactionId;

    protected $certificatePath;

    protected $xmlSignerPath;

    /*
     * Skey
     */
    protected $certificateExpiryDate;

    /**
     * Hmac
     */
    protected $hmac;

    /**
     * Session Key
     */
    protected $session;

    protected $sessionKey;

    /**
     * Other meta data is bio dependent
     */
    protected $metaUdc;

    protected $pid;

    /**
     *
     * @param \GuzzleHttp\Client $httpService
     * @param Object $config
     */
    public function __construct(User $user, $config = [])
    {
        $this->xmlWriter = new \XMLWriter();
        $this->xmlSigner = new XMLSecurityDSig();
        $this->certificatePath = realpath(static::CERT_PATH);
        $this->xmlSignerPath = realpath(static::XML_SIGNER_PATH);
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    protected function generateMetaUdc()
    {
        $bytes = random_bytes(20);
        $this->metaUdc = bin2hex($bytes);
    }

    /**
     *
     * @todo Constraint need to be applied
     */
    protected function generateTransactionId()
    {
        $bytes = random_bytes(50);
        $this->transactionId = bin2hex($bytes);
    }

    protected function generateCertificateExpiry()
    {
        $certinfo = openssl_x509_parse(file_get_contents($this->certificatePath));
        $this->certificateExpiryDate = \Carbon\Carbon::parse('@' . $certinfo['validTo_time_t'])->format('Ymd');
    }

    /**
     */
    protected function generateSessionKey()
    {
        /*
         * Generate 256 bit session key
         */
        $this->session = openssl_random_pseudo_bytes(128, $cryptStrength);

        $crypted = '';
        $publicKeyRes = openssl_pkey_get_public(file_get_contents($this->certificatePath));
        $publicKeyDetails = openssl_pkey_get_details($publicKeyRes); // openssl_pkey_get_details
        $res = openssl_public_encrypt($this->session, $crypted, $publicKeyDetails['key']);
        $this->sessionKey = base64_encode($crypted);
        // var_dump($res,$publicKeyDetails);
    }

    protected function generateHmac(\Phadhaar\Model\User $user)
    {
        if (! $this->enableProtoBuf) {
            $xmlPid = serialize($user);
            // $hashXmlPid = hash($xmlPid, 'sha256');
            /*
             * encrypt
             */
            /*
             * $crypted = '';
             * $publicKeyRes = openssl_pkey_get_public(file_get_contents($this->certificatePath));
             * $publicKeyDetails = openssl_pkey_get_details($publicKeyRes); // openssl_pkey_get_details
             * $res = openssl_public_encrypt($hashXmlPid, $crypted, $publicKeyDetails['key']);
             * $encryptedXmlPid = base64_encode($crypted);
             */
            $encryptedXmlPid = hash_hmac('sha256', $xmlPid, $this->session, true);
            $this->hmac = base64_encode($encryptedXmlPid);
            return;
        }

        throw new AdhaarNotSupportedException('ProtoBuf Not supported yet');
    }

    protected function generatePidBlock(\Phadhaar\Model\User $user)
    {
        $this->user = $user;
        if (! in_array('aes-256-gcm', openssl_get_cipher_methods())) {
            throw new AdhaarNotSupportedException('aes-256-gcm support is needed');
        }

        if (! $this->enableProtoBuf) {
            $xmlPid = serialize($this->user);
            // encrypt
            $iv = $this->user->getTs();
            openssl_encrypt($xmlPid, 'aes-256-gcm', $this->session, OPENSSL_ZERO_PADDING, $iv, $tag);
            $encryptedXmlPid = $xmlPid;
            $this->pid = base64_encode($encryptedXmlPid);
            return;
        }

        throw new AdhaarNotSupportedException('ProtoBuf Not supported yet');
    }

    /**
     *
     * @return String
     */
    public function generateEndpoint()
    {
        $endpoint = sprintf('%s/%s/%s/%s/%s/%s', self::TEST_ADHAAR_ENDPOINT, self::API_VERSION_2, 'public', $this->user->getAdhaarNumber()[0], $this->user->getAdhaarNumber()[1], urlencode(self::TEST_ASA_LICENSE_KEY));
        //var_dump($endpoint);
        return $endpoint;
    }

    /**
     *
     * @param \Phadhaar\Model\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function serialize()
    {
        $this->generateTransactionId();
        $this->generateMetaUdc();
        $this->generateCertificateExpiry();
        $this->generateSessionKey();
        $this->generateHmac($this->user);
        $this->generatePidBlock($this->user);

        $this->xmlWriter->openMemory();
        // $this->xmlWriter->startDocument("1.0");
        /*
         * Auth Block uid="" rc="" tid="" ac="" sa="" ver="" txn="" lk=""
         */
        $this->xmlWriter->startElement('Auth');
        $this->xmlWriter->writeAttribute('uid', $this->user->getAdhaarNumber());
        $this->xmlWriter->writeAttribute('rc', $this->consent);
        $this->xmlWriter->writeAttribute('tid', $this->tid);
        $this->xmlWriter->writeAttribute('ac', $this->auaCode);
        $this->xmlWriter->writeAttribute('sa', $this->asaCode);
        $this->xmlWriter->writeAttribute('ver', $this->apiVersion);
        $this->xmlWriter->writeAttribute('txn', $this->transactionId);
        $this->xmlWriter->writeAttribute('lk', $this->auaLicenseKey);

        /*
         * Uses Block
         */
        $this->xmlWriter->startElement('Uses');
        $this->xmlWriter->writeAttribute('pi', $this->user->doUsesPersonalInfo() ? 'y' : 'n');
        $this->xmlWriter->writeAttribute('pa', $this->user->doUsesPersonalAddress() ? 'y' : 'n');
        $this->xmlWriter->writeAttribute('pfa', $this->user->doUsesPersonalFullAddress() ? 'y' : 'n');
        $this->xmlWriter->writeAttribute('bio', $this->user->doUsesBio() ? 'y' : 'n');
        $this->xmlWriter->writeAttribute('bt', $this->user->doUsesBioType() ? 'y' : 'n');
        $this->xmlWriter->writeAttribute('pin', $this->user->doUsesPin() ? 'y' : 'n');
        $this->xmlWriter->writeAttribute('otp', $this->user->doUsesOtp() ? 'y' : 'n');
        $this->xmlWriter->endElement();

        /*
         * Meta Block
         */
        $this->xmlWriter->startElement('Meta');
        $this->xmlWriter->writeAttribute('udc', $this->metaUdc);
        $this->xmlWriter->endElement();

        /*
         * Skey Block
         */
        $this->xmlWriter->startElement('Skey');
        $this->xmlWriter->writeAttribute('ci', $this->certificateExpiryDate);
        $this->xmlWriter->text($this->sessionKey);
        $this->xmlWriter->endElement();

        /*
         * Data Block
         */
        $this->xmlWriter->startElement('Data');
        $this->xmlWriter->writeAttribute('type', $this->enableProtoBuf ? 'P' : 'X');
        $this->xmlWriter->writeRaw($this->pid);
        $this->xmlWriter->endElement();

        /*
         * Hmac Block
         */
        $this->xmlWriter->startElement('Hmac');
        $this->xmlWriter->text($this->hmac);
        $this->xmlWriter->endElement();

        /*
         * Signature Block
         */
        // $this->xmlWriter->startElement('Signature');
        // $this->xmlWriter->text($this->hmac);
        // $this->xmlWriter->endElement();

        // Auth End
        $this->xmlWriter->endElement();
        return $this->xmlWriter->outputMemory();
    }

    public function unserialize($serialized)
    {}

    /**
     *
     * @param string $payloadXml
     */
    public function sign()
    {
        // Load the XML to be signed
        $doc = new \DOMDocument();
        // s=$this->serialize();
        // file_put_contents('test.xml', $rs);
        $doc->loadXML($this->serialize());

        // Use the c14n exclusive canonicalization
        $this->xmlSigner->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        // Sign using SHA-256
        $this->xmlSigner->addReference($doc, XMLSecurityDSig::SHA256, [
            'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
            'http://www.w3.org/2001/10/xml-exc-c14n#'
        ], [
            'force_uri' => true
        ]);

        // Create a new (private) Security key
        $xmlObjKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, [
            'type' => 'private'
        ]);

        // Append the signature to the XML
        // $objDSig->appendSignature($doc->documentElement);
        // Save the signed XML
        $key = '';
        $succ = openssl_pkcs12_read(file_get_contents($this->xmlSignerPath), $key, "public");
        // var_dump(file_exists($this->xmlSignerPath),$key,$succ); die();
        $xmlObjKey->loadKey($key["pkey"]);
        $this->xmlSigner->add509Cert($key["cert"]);
        $this->xmlSigner->sign($xmlObjKey, $doc->documentElement);
        return $doc->saveXML();
    }
}
