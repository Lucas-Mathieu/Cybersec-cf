<?php

class EmailUtil
{
    public static function sendVerificationEmail($to, $name, $code)
    {
        $subject = "Vérifiez votre compte";
        $message = "Bonjour $name,\n\nVoici votre code de vérification : $code\n\nMerci de vérifier votre compte.\n\nCordialement,\nCF Project Hub";
        $headers = "From: no-reply@votre-nom-de-domaine\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Encode subject for UTF-8
        $encodedSubject = mb_encode_mimeheader($subject, "UTF-8", "B", "\r\n");

        return mail($to, $encodedSubject, $message, $headers);
    }

    public static function sendPasswordResetEmail($to, $name, $code)
    {
        $subject = "Réinitialisez votre mot de passe";
        $message = "Bonjour $name,\n\nVoici votre code de réinitialisation : $code\n\nMerci de réinitialiser votre mot de passe.\n\nCordialement,\nCF Project Hub";
        $headers = "From: no-reply@votre-nom-de-domaine\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Encode subject for UTF-8
        $encodedSubject = mb_encode_mimeheader($subject, "UTF-8", "B", "\r\n");

        return mail($to, $encodedSubject, $message, $headers);
    }
}
?>