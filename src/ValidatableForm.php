<?php namespace Idmkr\FormValidation;

use Respect\Validation\Exceptions\NestedValidationException;

abstract class ValidatableForm
{

    private $data;
    private $errors;
    private $lang;

    /**
     * @param $lang
     */
    public function __construct($lang="en_GB") {
        $this->data = [];
        $this->errors = [];
        $this->lang = $lang;

        // English is the source language
        // Gettext will be used for other languages
        // A .mo file with the domain name is needed
        if($lang!="en_GB") {
            $domain = 'respect-validation';

            setlocale( LC_MESSAGES, $lang.".utf8");
            bindtextdomain($domain, dirname(__FILE__).'/../lang');
            textdomain($domain);
            bind_textdomain_codeset($domain, 'UTF-8');
        }
    }

    /**
     * @param array $data
     *
     * @return boolean
     */
    public function validate($data)
    {
        foreach($data as $key=>$value) {
            $validationMethod = "validate".$this->camelize($key);

            if(method_exists($this,$validationMethod)) {
                // keep this data
                $this->data[$key] = $value;
                try {
                    $this->$validationMethod()->setName($key)->assert($value);
                }
                catch(NestedValidationException $e) {
                    $e->setParam('translator', 'gettext');
                    foreach($e->getMessages() as $msg) {
                        if($msg) {
                            if(!isset($this->errors[$key]))
                                $this->errors[$key] = '';

                            $this->errors[$key] .= ucfirst($msg).'. ';
                        }
                    }
                }
            }
        }

        return sizeof($this->errors) == 0;
    }

    /**
     * @param        $input
     * @param string $separator
     *
     * @return string
     */
    private function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    /**
     * @param bool $html
     *
     * @return string
     */
    public function decoratedData($html=true)
    {
        return ($html?"<pre>":'').json_encode($this->data(),JSON_PRETTY_PRINT).($html?"</pre>":'');
    }

    /**
     * @return array
     */
    public function data($key=null)
    {
        return $key ? $this->data[$key] : $this->data;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}
