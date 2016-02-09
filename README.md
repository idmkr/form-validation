#PHP Form validation, class based, intuitive.
This package provide a simple wrapper around the awesome respect/validation package. 
It's basically just an abstract class that you can extend, and it provides a dynamic method `validateInputName()`,along with a mailer and logger feature. 

##Features
- Ok to use (as is) within micro frameworks like Slim or Silex
- Define your form data and validation with a simple class method
- [Gettext translations for exception messages](https://github.com/idmkr/respect-validation-localization) 
- Mail notifier
- File logger

#Installation
Use Composer to install this package.
```
composer require idmkr/form-validation
```
Extend this class and start using respect/validation validators as intuitive class methods.


#Full example
Handling a classic contact form is straightforward. Start by creating a new class and extends ValidatableForm. 
You can then use  `Respect\Validation\Validator` ( v:: ) and define your form fields either with `setValidation` method or by camelizing the form data name. 
Form data which have not been defined by one of these methods will simply be ignored.

```php
use Respect\Validation\Validator as v;

use Idmkr\FormValidation\ValidatableForm;
use Idmkr\FormValidation\Traits\Mailable;
use Idmkr\FormValidation\Traits\Loggable;

class ContactForm extends ValidatableForm
{
    use Mailable,Loggable;
    
    // This generic method can wrap all of your form data
    public function setValidation() 
    {
        return [
            'name' => v::alpha("'\"&,")->length(1,100),
            'telephone' => v::phone(),
            'email' => v::email(),
            'subject' => v::length(3,300),
        ];
    }

    // This dynamic method will override any validation associated with setValidation()
    public function validateMessage()
    {
        return v::length(30,1500);
    }
}
```

In your POST route/controller function :

```php
$form = new ContactForm('fr_FR');

$success = $form->validate($_POST)
            // Send email through PHPMailer
            && $form->notify('your@mail.com',[
                'subject' => '{Contact Form} '.$form->data("firstname").' '.$form->data("lastname"),
                'from' => $form->data("email")
            ])
            // Log to .json file
            && $form->writeTo("./form-logs");

if(!$success)
    echo json_encode($form->errors());
```

