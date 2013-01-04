<?php
namespace cuonic\PHPAuth2\Localization;

class en extends Handler
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getActivationEmail()
    {
        $email['subject'] = 'Account Activation required';

        $email['head'] = "'MIME-Version: 1.0' 'Content-type: text/html; charset=iso-8859-1'";

        $email['body'] = <<<BODY
Hello,
<br/>
<br/>
You have successfully created an account at PHPAuth 2.0.
<br/>
To be able to use your account you need to activate it using the following link :
<br/>
<br/>
<strong>
    <a href="{$this->data['base_url']}?page=activate&key={$this->data['key']}" target="_blank">Activate my account</a>
</strong>
<br/>
<br/>
Or alternatively, go to <a href="{$this->data['base_url']}?page=activate" target="_blank">this page</a> and paste the
following code : <strong>{$this->data['key']}</strong>
<br/>
<br/>
Reminder : This unique activation key will expire within 24 hours of the activation email's creation.
BODY;

        return $email;
    }

    public function getResetEmail()
    {
        $email['subject'] = 'Password reset request';

        $email['head'] = "'MIME-Version: 1.0' 'Content-type: text/html; charset=iso-8859-1'";

        $email['body'] = <<<BODY
Hello,
<br/>
<br/>
You recently requested a password reset request at PHPAuth 2.0.
<br/>
To proceed with resetting your password, click the following link :
<br/>
<br/>
<strong>
    <a href="{$this->data['base_url']}?page=reset&step=2&key={$this->data['key']}" target="_blank">Reset my password</a>
</strong>
<br/>
<br/>
Or alternatively, go to <a href="{$this->data['base_url']}?page=reset&step=2" target="_blank">this page</a> and paste the following code : <strong>{$this->data['key']}</strong>
<br/>
<br/>
Reminder : This unique reset key will expire within 24 hours of the password reset request.
BODY;

        return $email;
    }
}
