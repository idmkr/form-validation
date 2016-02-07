#PHP Form validation and notifier, class based, intuitive.
This package provide a simple wrapper around the awesome respect/validation package. It's basically just an abstract class that you can extend, and it provides a dynamic method `validateInputName()`, along with a mailer and logger feature. 

##Features
- Define your form data and validation with a simple class method
- Gettext translations for exception messages ( new translations welcome, only French right now )
- Mail notifier

#Installation
composer require idmkr/form-validation
Extend this class and start using respect/validation validators as intuitive class methods.

#Full example
Handling a classic contact form is straightforward. Start by creating a new class and extends ValidatableForm. You can then use  `Respect\Validation\Validator` ( v:: ) and define your form fields by transforming them into CamelCase. 
Form data which have not been defined by this method will simply be not be processed.

```php
use Respect\Validation\Validator as v;
use Idmkr\FormValidation\ValidatableForm;

class ContactForm extends ValidatableForm
{
    public function validatePrenom()
    {
        return $this->text();
    }

    public function validateNom()
    {
        return $this->text();
    }

    public function validateEmail()
    {
        return v::email();
    }

    public function validateTelephone()
    {
        return v::phone()->length(1,100);
    }

    public function validateMessage()
    {
        return v::length(30,1500);
    }

    private function text()
    {
        return v::alpha("'\"&,")->length(1,100);
    }
}
```

In your POST route/controller function :

```php
$form = new ContactForm('fr_FR');

$success = $form->validate($_POST)
            && $form->notify('team@idmkr.io',[
                'subject' => '{idmkr.io} '.ucfirst($type).' '.
                             $form->data("prenom").' '.$form->data("nom"),
                'from' => $form->data("email")
            ])
            && $form->writeTo(APP_DIR."/content/form/$type");

if(!$success)
    echo json_encode($form->errors());
```

