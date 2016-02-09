#PHP Form validation, class based, intuitive.
This package provide a very simple wrapper around the awesome respect/validation package. 
It's just an extendable class for all your needs

##Features
- Ok to use (as is) within micro frameworks like Slim or Silex or no framework at all
- Define your form data and validation with a simple class method
- [Gettext translations for exception messages](https://github.com/idmkr/respect-validation-localization)

#Installation
Use Composer to install this package.
```
composer require idmkr/form-validation
```
Extend this class and start using respect/validation validators as intuitive class methods.


#Full example
Handling a classic web form is pretty straightforward.
Start by extending `ValidatableForm` into a new form class. You can then use  `Respect\Validation\Validator` ( v:: ) 
and define your form fields either with `setValidation` method or by camelizing each form data name. 
Form data which have not been defined by one of these methods will simply be ignored.

```php
use Respect\Validation\Validator as v;
use Idmkr\FormValidation\ValidatableForm;

class ContactForm extends ValidatableForm
{
    // This generic method can wrap all of your form data
    public function setValidation() 
    {
        return [
            'name' => v::alpha("'\"&,")->length(1,100),
            'telephone' => v::phone(),
            'email' => v::email(),
            'subject' => v::length(3,300)
        ];
    }

    // This dynamic method will override any validation associated with setValidation()
    public function validateMessage()
    {
        return v::length(30,1500);
    }
}
```

In your POST route/controller function, you would have :

```php
$form = new ContactForm('fr_FR');

$success = $form->validate($_POST);

if(!$success)
    echo json_encode($form->errors());
```

