<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Phadhaar\Model;

use Phadhaar\Exception\AdhaarValidationException;

/**
 * Description of User
 *
 * @author gourav sarkar
 */
class User implements \Serializable
{

    // put your code here

    /**
     * <Uses pi="" pa="" pfa="" bio="" bt="" pin="" otp=""/>
     * <Pid ts="" ver="" wadh=â€�â€�>
     * <Demo lang="">
     * <Pi ms="E|P" mv="" name="" lname="" lmv="" gender="M|F|T" dob=""
     * dobt="V|D|A" age="" phone="" email=""/>
     * <Pa ms="E" co="" house="" street="" lm="" loc=""
     * vtc="" subdist="" dist="" state="" country="" pc="" po=""/>
     * <Pfa ms="E|P" mv="" av="" lav="" lmv=""/>
     * </Demo>
     * <Bios dih="">
     * <Bio type="FMR|FIR|IIR|FID" posh="" bs="">encoded biometric</Bio>
     * </Bios>
     * <Pv otp="" pin=""/>
     *
     * </Pid>
     *
     * @var type
     */
    const PID_VERSION_1 = '1.0';

    const PID_VERSION_2 = '2.0';

    const DOB_TYPE_VERIFIED = 'V';

    const DOB_TYPE_DECLARED = 'D';

    const DOB_TYPE_APPROX = 'A';

    const GENDER_MALE = 'M';

    const GENDER_FEMALE = 'F';

    const GENDER_TRANSGENDER = 'T';

    /**
     */
    const SUPPORTED_LANGS = [
        'Assamese' => '01',
        'Bengali' => '02',
        'Gujarati' => '05',
        'Hindi' => '01',
        'Kannada' => '07',
        'Malayalam' => '11',
        'Manipuri' => '12',
        'Marathi' => '13',
        'Oriya' => '15',
        'Punjabi' => '16',
        'Tamil' => '20',
        'Telugu' => '21',
        'Urdu' => '22'
    ];

    /**
     */
    const ADDRESS_FIELD = [
        'co',
        'house',
        'street',
        'lm',
        'loc',
        'vtc',
        'subdist',
        'dist',
        'state',
        'country',
        'pc',
        'po'
    ];

    protected $usesOtp = false;

    protected $usesPin = false;

    protected $usesBioType = false;

    // no
    protected $usesBio = false;

    // no
    protected $usesPersonalInfo = false;

    protected $usesPersonalAddress = false;

    protected $usesPersonalFullAddress = false;

    /**
     * Data
     */
    protected $name;

    protected $dob;

    protected $dobt;

    protected $age;

    protected $phone;

    protected $email;

    protected $address;

    protected $gender;

    /**
     */
    protected $nameMatchValue = 100;

    // default 100
    protected $nameMatchStrategy = 'E';

    // default ''E'
    protected $addressMatchStrategy = 'E';

    // default E, E/P for full address
    protected $addressMatchValue = 100;

    // default E, E/P for full address

    /**
     */
    protected $langAddressValue;

    protected $langAddressMatchValue;

    protected $langNameValue;

    protected $langNameMatchValue;

    /**
     */
    protected $lang;

    /**
     */
    protected $otp;

    protected $pin;

    protected $adhaarNumber;

    /**
     * PID
     */
    protected $ts;

    protected $wadh;

    // for ekyc
    protected $pidVersion;

    // for ekyc

    /**
     */
    protected $xmlWriter;

    public function __construct($adhaarCardNumber)
    {
        $this->adhaarNumber=$adhaarCardNumber;
        /*
         * YYYY-MM-DDThh:mm:ss
         */
        $this->ts = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d\TH:i:s');
        $this->pidVersion = self::PID_VERSION_2;

        $this->xmlWriter = new \XMLWriter();
    }

    public function getTs()
    {
        return $this->ts;
    }

    /**
     *
     * @param String $lang
     * @throws AdhaarValidationException
     */
    public function setLanguage($lang)
    {
        if (! in_array($lang, static::SUPPORTED_LANGS)) {

            throw new AdhaarValidationException('Unsupported Languages');
        }
        $this->lang = $lang;
    }

    /**
     *
     * @param String $strategy
     *            E,P
     * @param int $value
     *            1,100
     */
    public function setMatchingStrategy($strategy, $value)
    {
        $this->nameMatchStrategy = $strategy;
        $this->nameMatchValue = $value;
    }

    /**
     *
     * @param String $name
     * @param int $matchValue
     * @throws AdhaarValidationException
     */
    public function setLocaleName($name, $matchValue)
    {
        if (! $this->lang) {
            throw new AdhaarValidationException('Language Must be set');
        }
        $this->personalIdentityField['lname'] = $name;
        $this->langNameValue = $name;
        $this->langNameMatchValue = $matchValue;

        $this->usesPersonalInfo = true;
    }

    /**
     *
     * @param string $gender
     *            M,F,T
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->usesPersonalInfo = true;
    }

    /**
     *
     * @param string $gender
     *            M,F,T
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        $this->usesPersonalInfo = true;
    }

    /**
     *
     * @param string $dob
     *            YYYY, YYYY-MM-DD
     * @param string $dobt
     *            V,D,T
     */
    public function setDob($dob, $dobt = User::DOB_TYPE_VERIFIED, $onlyYear = false)
    {
        $carbondate = \Carbon\Carbon::parse($dob);
        if ($onlyYear) {
            $parsedDate = $carbondate->format('Y');
        } else {
            $parsedDate = $carbondate->format('Y-m-d');
        }

        $this->dob = $parsedDate;
        $this->dobt = $dobt;
        $this->usesPersonalInfo = true;
    }

    /**
     *
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
        $this->usesPersonalInfo = true;
    }

    /**
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->usesPersonalInfo = true;
    }

    /**
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        $this->usesPersonalInfo = true;
    }

    /**
     *
     * @param string $address
     * @param int $matchValue
     * @throws AdhaarValidationException
     */
    public function setLocaleAddress($address, $matchValue)
    {
        if (! $this->lang) {
            throw new AdhaarValidationException('Language Must be set');
        }
        if (isset($this->address) && is_array($this->address)) {
            throw new AdhaarValidationException('Partial addres does not have locale address');
        }

        $this->langAddressValue = $address;
        $this->langAddressMatchValue = $matchValue;
        $this->usesPersonalFullAddress = true;
    }

    /**
     * Array is partial address
     * String means full address
     *
     * @todo validation check
     * @param string $address
     */
    public function setAddress($address, $strategy = '', $value = '')
    {

        /**
         * split address only has exact match
         * full address has p/E and value
         */
        if (is_array($address)) {
            $this->value = $value;
            $this->addressMatchStrategy = 'E';
            $this->usesPersonalAddress = true;
        } else {
            $this->addressMatchStrategy = $strategy;
            $this->value = $value;
            $this->usesPersonalFullAddress = true;
        }

        $this->address = $address;
    }

    /**
     *
     * @param string $otp
     */
    public function setOtp($otp)
    {
        $this->otp = $otp;
        $this->usesOtp = true;
    }

    /**
     *
     * @param string $pin
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
        $this->usesPin = true;
    }

    public function setAdhaarNumber($adhaarNum)
    {
        $this->adhaarNumber = $adhaarNum;
    }

    public function getAdhaarNumber()
    {
        return $this->adhaarNumber;
    }

    public function doUsesPersonalInfo()
    {
        return $this->usesPersonalInfo;
    }

    public function doUsesPersonalAddress()
    {
        return $this->usesPersonalAddress;
    }

    public function doUsesPersonalFullAddress()
    {
        return $this->usesPersonalFullAddress;
    }

    public function doUsesBio()
    {
        return $this->usesBio;
    }

    public function doUsesBioType()
    {
        return $this->usesBioType;
    }

    public function doUsesOtp()
    {
        return $this->usesOtp;
    }

    public function doUsesPin()
    {
        return $this->usesPin;
    }

    public function serialize()
    {
        $this->xmlWriter->openMemory();
        // $this->xmlWriter->startDocument("1.0", "utf-8");
        /*
         * Pid Block
         */
        $this->xmlWriter->startElement('Pid');
        $this->xmlWriter->writeAttribute('ts', $this->ts);
        $this->xmlWriter->writeAttribute('ver', $this->pidVersion);
        if (isset($this->wadh)) {
            $this->xmlWriter->writeAttribute('wadh', $this->wadh);
        }

        /*
         * Demo Block
         */
        $this->xmlWriter->startElement('Demo');
        if ($this->lang) {
            $this->xmlWriter->writeAttribute('lang', $this->lang);
        }

        /*
         * PI block self colsed
         */
        $this->xmlWriter->startElement('Pi');
        if (isset($this->name)) {
            $this->xmlWriter->writeAttribute('name', $this->name);
            $this->xmlWriter->writeAttribute('ms', $this->nameMatchStrategy);
            $this->xmlWriter->writeAttribute('mv', $this->nameMatchValue);
        }

        if (isset($this->gender)) {
            $this->xmlWriter->writeAttribute('gender', $this->gender);
        }
        if (isset($this->dob)) {
            $this->xmlWriter->writeAttribute('dob', $this->dob);
            $this->xmlWriter->writeAttribute('dobt', $this->dobt);
        }
        if (isset($this->langNameValue)) {
            $this->xmlWriter->writeAttribute('lname', $this->langNameValue);
            $this->xmlWriter->writeAttribute('lmv', $this->langNameMatchValue);
        }
        if (isset($this->age)) {
            $this->xmlWriter->writeAttribute('age', $this->age);
        }
        if (isset($this->phone)) {
            $this->xmlWriter->writeAttribute('phone', $this->phone);
        }
        if (isset($this->email)) {
            $this->xmlWriter->writeAttribute('email', $this->email);
        }
        $this->xmlWriter->endElement();

        /*
         * Address block Pa,Pfa
         */
        if (is_array($this->address)) {
            $this->xmlWriter->startElement('Pa');
            $this->xmlWriter->writeAttribute('ms', $this->addressMatchStrategy);
            $this->xmlWriter->writeAttribute('co', $this->address['co']);
            $this->xmlWriter->writeAttribute('house', $this->address['house']);
            $this->xmlWriter->writeAttribute('street', $this->addres['street']);
            $this->xmlWriter->writeAttribute('lm', $this->address['lm']);
            $this->xmlWriter->writeAttribute('loc', $this->address['loc']);
            $this->xmlWriter->writeAttribute('vtc', $this->address['vtc']);
            $this->xmlWriter->writeAttribute('subdist', $this->address['subdist']);
            $this->xmlWriter->writeAttribute('dist', $this->address['dist']);
            $this->xmlWriter->writeAttribute('state', $this->address['state']);
            $this->xmlWriter->writeAttribute('country', $this->address['country']);
            $this->xmlWriter->writeAttribute('pc', $this->address['pc']);
            $this->xmlWriter->writeAttribute('po', $this->address['po']);
            $this->xmlWriter->endElement();
        } else if (is_string($this->address)) {
            $this->xmlWriter->startElement('Pfa');
            $this->xmlWriter->writeAttribute('ms', $this->addressMatchStrategy);
            $this->xmlWriter->writeAttribute('mv', $this->addressMatchValue);
            $this->xmlWriter->writeAttribute('av', $this->address);
            $this->xmlWriter->writeAttribute('lav', $this->langAddressValue);
            $this->xmlWriter->endElement();
        }

        /*
         * Otp/pin block
         */
        if (isset($this->otp) || isset($this->pin)) {
            $this->xmlWriter->startElement('Pv');
            $this->xmlWriter->writeAttribute('otp', $this->otp);
            $this->xmlWriter->writeAttribute('pin', $this->pin);
            $this->xmlWriter->endElement();
        }

        /*
         * Demo end
         */
        $this->xmlWriter->endElement();
        /*
         * Pid End
         */
        $this->xmlWriter->endElement();
        // $this->xmlWriter->endDocument();

        return $this->xmlWriter->outputMemory();
    }

    public function unserialize($serialized)
    {}
}
