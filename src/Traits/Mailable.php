<?php namespace Idmkr\FormValidation\Traits;

use PHPMailer;

trait Mailable {
    private $mailer;

    public function warn($to,$options) {
        $options = array_merge([
            "subject" => "FormValidation Mailer: You've got mail !",
            "from" => null,
            "attachment" => null
        ], $options);

        $mailer = $this->mailer($to);

        if($options["subject"])
            $mailer->Subject = $options["subject"];

        if($options["from"])
            $mailer->setFrom($options["from"]);

        if($options["attachment"])
            $mailer->addAttachment($options["attachment"]);

        $sent = $mailer->send();

        if($mailer->ErrorInfo)
            $this->errors["PHPMailer"] = $mailer->ErrorInfo;

        return $sent;
    }

    /**
     * @param $to
     *
     * @return PHPMailer
     */
    public function mailer($to) {
        $mailer = new PHPMailer();

        $mailer->addAddress($to);
        $mailer->Body = $this->decoratedData(true);
        $mailer->AltBody = $this->decoratedData();
        $mailer->isHTML(true);

        return $mailer;
    }
}