<?php

if (isset($_POST["message"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $message = $_POST["message"];

    // Adresse e-mail de l'expéditeur
    $fromEmail = $email;

    // Nom de l'expéditeur
    $fromName = $name;

    $subject = $message;

    // Corps du message
    $messageBody = "Ce message vous a été envoyé via la page contact de votre portfolio\n"
        . "Nom : " . $name . "\n"
        . "Email : " . $email . "\n"
        . "Message : " . $message;

    // En-têtes de l'e-mail
    $headers = "From: " . $fromName . " <" . $fromEmail . ">\r\n";
    $headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Adresse de destination
    $toEmail = "eliot.pouplier@gmail.com";

    // Envoi de l'e-mail
    $retour = mail($toEmail, $subject, $messageBody, $headers);

    if ($retour) {
        echo "Votre message a été envoyé avec succès.";
    } else {
        echo "Une erreur s'est produite lors de l'envoi de votre message. Veuillez réessayer plus tard.";
    }
}

?>