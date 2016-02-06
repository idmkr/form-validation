<?php namespace Idmkr\FormValidation;

use Respect\Validation\Exceptions\NestedValidationException;
use Idmkr\FormValidation\Traits\Mailable;
use Idmkr\FormValidation\Traits\Writable;

abstract class ValidatableForm
{
    use Mailable,Writable;

    private $data;
    private $errors;
    private $lang = "en";

    public function __construct($lang) {
        $this->data = [];
        $this->errors = [];
        $this->lang = $lang;

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
                    //if($this->translatedTemplates) {
                        foreach($e->getMessages() as $msg) {
                            if($msg) {
                                if(!isset($this->errors[$key]))
                                    $this->errors[$key] = '';

                                $this->errors[$key] .= ucfirst($msg).'. ';
                            }
                        }
                    /*}
                    else
                        $this->errors[$key] = $e->getFullMessage();*/

                }
            }
        }

        return sizeof($this->errors) == 0;
    }

    private function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    private function decoratedData($html=true) {
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