<?php
namespace cuonic\PHPAuth2;

class Config
{
    private $lang = 'en';
    private $lang_list = array(
            'en',
            'fr',
            'es'
        );
    private $base_url = 'http://example.com/phpauth2.0/';
    private $salt_1 = 'us_1dUDN4N-53/dkf7Sd?vbc_due1d?df!feg';
    private $salt_2 = 'Yu23ds09*d?u8SDv6sd?usi$_YSdsa24fd+83';
    private $salt_3 = '63fds.dfhsAdyISs_?&jdUsydbv92bf54ggvc';
    private $cookie_domain;
    private $cookie_path = '/';
    private $cookie_auth = 'auth_session';
    private $sitekey = 'dk;l189654è(tyhj§!dfgdfàzgq_f4fá.';
    private $admin_level = 99;
    private $table_activations = 'activations';
    private $table_attempts = 'attempts';
    private $table_log = 'log';
    private $table_resets = 'resets';
    private $table_sessions = 'sessions';
    private $table_users = 'users';

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
