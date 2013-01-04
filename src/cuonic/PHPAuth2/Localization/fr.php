<?php
namespace cuonic\PHPAuth2\Localization;

class fr extends Handler
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getActivationEmail()
    {
        $email['subject'] = 'Activation de compte';

        $email['head'] = "'MIME-Version: 1.0' 'Content-type: text/html; charset=iso-8859-1'";

        $email['body'] = <<<BODY
Bonjour,
<br/>
<br/>
Vous avez cr&eacute;e un compte utilisateur sur le site PHPAuth 2.0.
<br/>
Afin de pouvoir utiliser votre compte, vous devez d'abord l'activer en utilisant le lien suivant :
<br/>
<br/>
<strong>
    <a href="{$this->data['base_url']}?page=activate&key={$this->data['key']}" target=\_blank">Activer mon compte</a>
</strong>
<br/>
<br/>
Ou sinon, visitez <a href="{$this->data['base_url']}?page=activate" target="_blank">cette page</a> et copiez / collez le code suivant : <strong>{$this->data['key']}</strong>
<br/>
<br/>
Rappel : Cette cl&eacute; unique d'activation expirera dans les 24 heures suivant la cr&eacute;ation du compte.
BODY;

        return $email;
    }

    public function getResetEmail()
    {
        $email['subject'] = 'Demande de r&eacute;initialisation de mot de passe';

        $email['head'] = "'MIME-Version: 1.0' 'Content-type: text/html; charset=iso-8859-1'";

        $email['body'] = <<<BODY
Hello,
<br/>
<br/>
Vous avez recemment demand&eacute; la r&eacute;initialisation de votre mot de passe sur le site PHPAuth 2.0.
<br/>
Pour proceder avec la r&eacute;initialisation, cliquez sur le lien suivant :
<br/>
<br/>
<strong>
    <a href="{$this->data['base_url']}?page=reset&step=2&key={$this->data['key']}" target="_blank">R&eacute;initialiser mon mot de passe</a>
</strong>
<br/>
<br/>
Ou sinon, visitez <a href="{$this->data['base_url']}?page=reset&step=2" target="_blank">cette page</a> et copiez / collez le code suivant : <strong>{$this->base['key']}</strong>
<br/>
<br/>
Rappel : Cette cl&eacute; unique de r&eacute;initialisation expirera dans les 24 heures suivant la demande de r&eacute;initialisation.
BODY;

        return $email;
    }
}
