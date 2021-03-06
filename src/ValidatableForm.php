<?php namespace Idmkr\FormValidation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

abstract class ValidatableForm
{
    private $validData = [];
    private $unfilteredData = [];
    private $errors = [];
    private $lang = null;

    /**
     * @param $lang
     */
    public function __construct($lang) {
        $this->setLang($lang);
    }

    private function setLang($lang="en_GB")
    {
        $this->lang = $lang;
        // English is the source language
        // Gettext will be used for other languages
        // A .mo file with the domain name is needed
        if($this->lang!="en_GB") {
            $domain = 'respect-validation';

            if(function_exists("bindtextdomain")) {
                setlocale( LC_MESSAGES, $lang.".utf8");
                bindtextdomain($domain, dirname(__FILE__).'/../lang');
                textdomain($domain);
                bind_textdomain_codeset($domain, 'UTF-8');
            }
            else {
                $this->errors["gettext"] =
                    "Fatal error : gettext not found. You should activate extension=php_gettext.dll in php.ini";
            }
        }
    }

    /**
     * @param array $data
     *
     * @return boolean
     */
    public function validate($data)
    {
        $this->unfilteredData = $data;

        if(method_exists($this,"setValidation"))
            $validationMethods = $this->setValidation();

        foreach($data as $key=>$value) {
            $dynamicValidateMethod = "validate".$this->camelize($key);

            if(method_exists($this,$dynamicValidateMethod))
                $validate = $this->$dynamicValidateMethod();
            else if(isset($validationMethods) && isset($validationMethods[$key]))
                $validate = $validationMethods[$key];
            else
                $validate = null;

            if($validate) {
                try {
                    /** @var Validator $validate */
                    $validate->setName($key)->assert($value);
                    $this->validData[$key] = $value;
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
    public function toPrettyJson($html=true)
    {
        return ($html?"<pre>":'').json_encode($this->validated(),JSON_PRETTY_PRINT).($html?"</pre>":'');
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function sanitized($key=null)
    {
        $safeData = filter_var_array($this->unfilteredData, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

        return $key ? $safeData[$key] : $safeData;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function validated($key=null)
    {
        return $key ? $this->validData[$key] : $this->validData;
    }

    /**
     * @param $excludeKeys
     *
     * @return array
     */
    public function validatedWithout(Array $excludeKeys)
    {
        $data = $this->validated();
        foreach($excludeKeys as $key){
            unset($data[$key]);
        }
        return $data;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}
