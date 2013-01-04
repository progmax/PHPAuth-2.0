<?php
namespace cuonic\PHPAuth2\Localization;

class es extends Handler
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getActivationEmail()
    {
        $email['subject'] = 'Activacion de cuenta';

        $email['head'] = "'MIME-Version: 1.0' 'Content-type: text/html; charset=iso-8859-1'";

        $email['body'] = <<<BODY
Hola,<br/><br/>
Acabas de crear una cuenta en PHPAuth 2.0.<br/>
Para activar tu cuenta debes acceder al siguente enlace :<br/><br/>
<strong>
    <a href="{$this->data['base_url']}?page=activate&key={$this->data['key']}" target="_blank">Activar mi cuenta</a>
</strong>
<br/>
<br/>
Si no funciona el enlace puedes acceder a <a href="{$this->data['base_url']}?page=activate" target="_blank">esta pagina</a> y poner el siguente codigo : <strong>{$this->data['key']}</strong>
<br/>
<br/>
Recuerda : Esta clave unica de activacion caduca en 24 horas, a partir del envio de este correo.
BODY;

        return $email;
    }

    public function getResetEmail()
    {
        $email['subject'] = 'Restablecer contrase&ntilde;a';

        $email['head'] = "'MIME-Version: 1.0' 'Content-type: text/html; charset=iso-8859-1'";

        $email['body'] = <<<BODY
Hola,
<br/>
<br/>
Acabas de pedir una contrase&ntilde;a nueva en PHPAuth 2.0.
<br/>
Para proceder a restablecer tu contrase&ntilde;a debes acceder al siguente enlace :
<br/>
<br/>
<strong>
    <a href="{$this->data['base_url']}?page=reset&step=2&key={$this->data['key']}" target="\_blank">Restablecer mi contrase&ntilde;a</a>
</strong>
<br/>
<br/>
Si no funciona el enlace puedes acceder a <a href="{$this->data['base_url']}?page=reset&step=2" target="_blank">esta pagina</a> y poner el siguente codigo : <strong>{$this->data['key']}</strong>
<br/>
<br/>
Recuerda : Esta clave de restablecimiento caduca en 24 horas, a partir del envio de este correo.
BODY;

        return $email;
    }
}
