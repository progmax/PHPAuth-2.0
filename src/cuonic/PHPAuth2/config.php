<?php

$auth_conf = array();

// ------------------------
// Auth Configuration :
// ------------------------

// Language of activation / password reset emails : Currently either "en" / "es" / "fr"
$auth_conf['lang'] = "en";
// Base url of site PHPAuth 2.0 is hosted on, including trailing slash
$auth_conf['base_url'] = "http://example.com/phpauth2.0/";

// Password salt 1 : Change this to any random string
$auth_conf['salt_1'] = 'us_1dUDN4N-53/dkf7Sd?vbc_due1d?df!feg';
// Password salt 2 : Change this to any random string
$auth_conf['salt_2'] = 'Yu23ds09*d?u8SDv6sd?usi$_YSdsa24fd+83';
// Password salt 3 : Change this to any random string
$auth_conf['salt_3'] = '63fds.dfhsAdyISs_?&jdUsydbv92bf54ggvc';

// Cookie configuration

$auth_conf['cookie_domain'] = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
$auth_conf['cookie_path'] = '/';
$auth_conf['cookie_auth'] = 'auth_session';
$auth_conf['sitekey'] = 'dk;l189654è(tyhj§!dfgdfàzgq_f4fá.';

// Administration configuration

$auth_conf['admin_level'] = 99;

// ------------------------
// Auth Email Configuration :
// ------------------------

switch ($auth_conf['lang']) {
    case "es":
        // Registration activation email subject
        $auth_conf['activation_email']['subj'] = "PHPAuth 2.0 : Activacion de cuenta";
        // Registration activation email body
        $auth_conf['activation_email']['body'] = "Hola,<br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Acabas de crear una cuenta en PHPAuth 2.0.<br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Para activar tu cuenta debes acceder al siguente enlace :<br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "<strong><a href=\"" . $auth_conf['base_url'] . "?page=activate&key={key}\" target=\_blank\">Activar mi cuenta</a></strong><br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Si no funciona el enlace puedes acceder a <a href=\"" . $auth_conf['base_url'] . "?page=activate\" target=\"_blank\">esta pagina</a> y poner el siguente codigo : <strong>{key}</strong><br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Recuerda : Esta clave unica de activacion caduca en 24 horas, a partir del envio de este correo." . "\r\n";
        // Registration activation email headers
        $auth_conf['activation_email']['head'] = 'From: PHPAuth 2.0 <no-reply@phpauth.cuonic.com>' . "\r\n";
        $auth_conf['activation_email']['head'] .= 'MIME-Version: 1.0' . "\r\n";
        $auth_conf['activation_email']['head'] .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Password reset email subject
        $auth_conf['reset_email']['subj'] = "PHPAuth 2.0 : Restablecer contrase&ntilde;a";
        // Password reset email body
        $auth_conf['reset_email']['body'] = "Hola,<br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Acabas de pedir una contrase&ntilde;a nueva en PHPAuth 2.0.<br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Para proceder a restablecer tu contrase&ntilde;a debes acceder al siguente enlace :<br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "<strong><a href=\"" . $auth_conf['base_url'] . "?page=reset&step=2&key={key}\" target=\_blank\">Restablecer mi contrase&ntilde;a</a></strong><br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Si no funciona el enlace puedes acceder a <a href=\"" . $auth_conf['base_url'] . "?page=reset&step=2\" target=\"_blank\">esta pagina</a> y poner el siguente codigo : <strong>{key}</strong><br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Recuerda : Esta clave de restablecimiento caduca en 24 horas, a partir del envio de este correo." . "\r\n";
        // Password reset email headers
        $auth_conf['reset_email']['head'] = 'From: PHPAuth 2.0 <no-reply@phpauth.cuonic.com>' . "\r\n";
        $auth_conf['reset_email']['head'] .= 'MIME-Version: 1.0' . "\r\n";
        $auth_conf['reset_email']['head'] .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        break;

    case "fr":
        // Registration activation email subject
        $auth_conf['activation_email']['subj'] = "PHPAuth 2.0 : Activation de compte";
        // Registration activation email body
        $auth_conf['activation_email']['body'] = "Bonjour,<br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Vous avez cr&eacute;e un compte utilisateur sur le site PHPAuth 2.0.<br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Afin de pouvoir utiliser votre compte, vous devez d'abord l'activer en utilisant le lien suivant :<br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "<strong><a href=\"" . $auth_conf['base_url'] . "?page=activate&key={key}\" target=\_blank\">Activer mon compte</a></strong><br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Ou sinon, visitez <a href=\"" . $auth_conf['base_url'] . "?page=activate\" target=\"_blank\">cette page</a> et copiez / collez le code suivant : <strong>{key}</strong><br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Rappel : Cette cl&eacute; unique d'activation expirera dans les 24 heures suivant la cr&eacute;ation du compte." . "\r\n";
        // Registration activation email headers
        $auth_conf['activation_email']['head'] = 'From: PHPAuth 2.0 <no-reply@phpauth.cuonic.com>' . "\r\n";
        $auth_conf['activation_email']['head'] .= 'MIME-Version: 1.0' . "\r\n";
        $auth_conf['activation_email']['head'] .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Password reset email subject
        $auth_conf['reset_email']['subj'] = "PHPAuth 2.0 : Demande de r&eacute;initialisation de mot de passe";
        // Password reset email body
        $auth_conf['reset_email']['body'] = "Hello,<br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Vous avez recemment demand&eacute; la r&eacute;initialisation de votre mot de passe sur le site PHPAuth 2.0.<br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Pour proceder avec la r&eacute;initialisation, cliquez sur le lien suivant :<br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "<strong><a href=\"" . $auth_conf['base_url'] . "?page=reset&step=2&key={key}\" target=\_blank\">R&eacute;initialiser mon mot de passe</a></strong><br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Ou sinon, visitez <a href=\"" . $auth_conf['base_url'] . "?page=reset&step=2\" target=\"_blank\">cette page</a> et copiez / collez le code suivant : <strong>{key}</strong><br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Rappel : Cette cl&eacute; unique de r&eacute;initialisation expirera dans les 24 heures suivant la demande de r&eacute;initialisation." . "\r\n";
        // Password reset email headers
        $auth_conf['reset_email']['head'] = 'From: PHPAuth 2.0 <no-reply@phpauth.cuonic.com>' . "\r\n";
        $auth_conf['reset_email']['head'] .= 'MIME-Version: 1.0' . "\r\n";
        $auth_conf['reset_email']['head'] .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        break;

    default:
        // Registration activation email subject
        $auth_conf['activation_email']['subj'] = "PHPAuth 2.0 : Account Activation required";
        // Registration activation email body
        $auth_conf['activation_email']['body'] = "Hello,<br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "You have successfully created an account at PHPAuth 2.0.<br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "To be able to use your account you need to activate it using the following link :<br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "<strong><a href=\"" . $auth_conf['base_url'] . "?page=activate&key={key}\" target=\_blank\">Activate my account</a></strong><br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Or alternatively, go to <a href=\"" . $auth_conf['base_url'] . "?page=activate\" target=\"_blank\">this page</a> and paste the following code : <strong>{key}</strong><br/><br/>" . "\r\n";
        $auth_conf['activation_email']['body'] .= "Reminder : This unique activation key will expire within 24 hours of the activation email's creation." . "\r\n";
        // Registration activation email headers
        $auth_conf['activation_email']['head'] = 'From: PHPAuth 2.0 <no-reply@phpauth.cuonic.com>' . "\r\n";
        $auth_conf['activation_email']['head'] .= 'MIME-Version: 1.0' . "\r\n";
        $auth_conf['activation_email']['head'] .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Password reset email subject
        $auth_conf['reset_email']['subj'] = "PHPAuth 2.0 : Password reset request";
        // Password reset email body
        $auth_conf['reset_email']['body'] = "Hello,<br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "You recently requested a password reset request at PHPAuth 2.0.<br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "To procede with resetting your password, click the following link :<br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "<strong><a href=\"" . $auth_conf['base_url'] . "?page=reset&step=2&key={key}\" target=\_blank\">Reset my password</a></strong><br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Or alternatively, go to <a href=\"" . $auth_conf['base_url'] . "?page=reset&step=2\" target=\"_blank\">this page</a> and paste the following code : <strong>{key}</strong><br/><br/>" . "\r\n";
        $auth_conf['reset_email']['body'] .= "Reminder : This unique reset key will expire within 24 hours of the password reset request." . "\r\n";
        // Password reset email headers
        $auth_conf['reset_email']['head'] = 'From: PHPAuth 2.0 <no-reply@phpauth.cuonic.com>' . "\r\n";
        $auth_conf['reset_email']['head'] .= 'MIME-Version: 1.0' . "\r\n";
        $auth_conf['reset_email']['head'] .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        break;
}

