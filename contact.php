<?php
// require ReCaptcha class
require('recaptcha-master/src/autoload.php');

// configure
// an email address that will be in the From field of the email.
$from = 'jjtellezg@gmail.com';

// an email address that will receive the email with the output of the form
$sendTo = 'tellezgjj@gmail.com';

// subject of the email
$subject = 'Hola';

// form field names and their translations.
// array variable name => Text to appear in the email
$fields = array('name' => 'Nombre', 'surname' => 'Surname', 'phone' => 'Phone', 'email' => 'Correo electronico', 'message' => 'Message');

// message that will be displayed when everything is OK :)
$okMessage = 'Formulario de contacto enviado correctamente. Gracias, nos pondremos en contacto contigo pronto!';

// If something goes wrong, we will display this message.
$errorMessage = 'Hubo un error al enviar el formulario. Por favor intente de nuevo más tarde';

// ReCaptch Secret
$recaptchaSecret = '6LeIiJsUAAAAADUMLe7MMVnc-JHHOAwwRpmD5PZs';

// let's do the sending

// if you are not debugging and don't need error reporting, turn this off by error_reporting(0);
error_reporting(E_ALL & ~E_NOTICE);

try {
    if (!empty($_POST)) {

        // validate the ReCaptcha, if something is wrong, we throw an Exception,
        // i.e. code stops executing and goes to catch() block

        if (!isset($_POST['g-recaptcha-response'])) {
            throw new \Exception('ReCaptcha no es puesto.');
        }

        // do not forget to enter your secret key from https://www.google.com/recaptcha/admin

        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret, new \ReCaptcha\RequestMethod\CurlPost());

        // we validate the ReCaptcha field together with the user's IP address

        $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if (!$response->isSuccess()) {
            throw new \Exception('ReCaptcha no fue valido.');
        }

        // everything went well, we can compose the message, as usually

        $emailText = "Tienes un nuevo mensaje de tu formulario de contacto rapido (landing page)\n=============================\n";

        foreach ($_POST as $key => $value) {
            // If the field exists in the $fields array, include it in the email
            if (isset($fields[$key])) {
                $emailText .= "$fields[$key]: $value\n";
            }
        }

        // All the neccessary headers for the email.
        $headers = array('Content-Type: text/plain; charset="UTF-8";',
            'From: ' . $from,
            'Reply-To: ' . $from,
            'Return-Path: ' . $from,
        );

        // Send email
        mail($sendTo, $subject, $emailText, implode("\n", $headers));

        $responseArray = array('type' => 'success', 'message' => $okMessage);
    }
} catch (\Exception $e) {
    $responseArray = array('type' => 'danger', 'message' => $e->getMessage());
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
} else {
    echo $responseArray['message'];
}
