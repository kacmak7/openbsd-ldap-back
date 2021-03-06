<?php
    
namespace Controller;

class Api
{
    private $_view;
    private $_ldap_dn;
    private $_ldap_password;
    private $_ldap_con;

    /**
     * @var \Model\InetOrgPersonMapper
     */
    private $_inetOrgPersonMapper;

    public function __construct()
    {
        $this->_view = \View::instance();
        $this->_ldap_dn = 'cn=admin,dc=marol,dc=com,dc=pl';
        $this->_ldap_password = 'secret';
        $this->_ldap_con = ldap_connect('127.0.0.1');
        $this->_inetOrgPersonMapper = new \Model\InetOrgPersonMapper();
    }

    public function GET_dictionary_systems(\Base $f3)
    {
        $domain = array(
            'marol.com.pl',
            'chatapolska.pl',
        );

        echo $this->_view->render('json_data.phtml', 'application/json', array('data' => $domains));
    }

    public function GET_test_message(\Base $f3)
    {
        $testMessage = 'papaya';

        $view=\View::instance();
        echo $view->render('json_data.phtml', 'application/json', array('data' => $testMessage));
    }

    public function GET_test_numbers(\Base $f3)
    {
        $data = array(1, 5, 2, 97, 26);

        $view=\View::instance();
        echo $view->render('json_data.phtml', 'application_json', array('data' => $data));
    }

    public function GET_users(\Base $f3)
    {
        echo $this->_view->render('json_data.phtml', 'application_json', array('data' => $this->_inetOrgPersonMapper->fetch('*')));
    }

    public function GET_users_array(\Base $f3)
    {
        print_r($this->_inetOrgPersonMapper->fetch('*'));
    }

    public function POST_user(\Base $f3)
    {
        $data = json_decode($f3->get('BODY'), true);

        ldap_set_option($this->_ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);

        if (ldap_bind($this->_ldap_con, $this->_ldap_dn, $this->_ldap_password)) {
            $info['objectclass'][0] = 'inetOrgPerson';
            $info['objectclass'][1] = 'person';
            $info['objectclass'][2] = 'organizationalPerson';
            $info['objectclass'][3] = 'posixAccount';
            $info['objectclass'][4] = 'shadowAccount';
            $info['objectclass'][5] = 'top';
            $info['givenName'] = $data['name'];
            $info['cn'] = $data['name'] . ' ' . $data['surname'];
            $info['sn'] = $data['surname'];
            $info['gecos'] = $info['cn'];
            $info['uid'] = $this->_inetOrgPersonMapper->generateUid($info['givenName'], $info['sn']);
            $info['uidNumber'] = $this->_inetOrgPersonMapper->nextUidNumber();
            $info['homeDirectory'] = $this->_inetOrgPersonMapper->getHomeDirectory($info['uid']);
            $info['sn'] = $data['surname'];
            $info['gidNumber'] = $info['uidNumber'];
            $info['loginShell'] = '/bin/sh';
            $info['userPassword'] = $data['password'];
        }
        //print_r($info);
        //echo '___________________';

        ldap_add($this->_ldap_con, 'uid=' . $info['uid'] . ', ou=users, dc=marol, dc=com, dc=pl', $info);
        ldap_close($this->_ldap_con);

        echo $this->_view->render('json_data.phtml', 'application_json', array('data' => $this->_inetOrgPersonMapper->fetch($info['uid'])));
    }

    public function PUT_user(\Base $f3)
    {
        $data = json_decode($f3->get('BODY'), true);

        ldap_set_option($this->_ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);

        if (ldap_bind($this->_ldap_con, $this->_ldap_dn, $this->_ldap_password)) {
            $info['objectclass'][0] = 'inetOrgPerson';
            $info['objectclass'][1] = 'person';
            $info['objectclass'][2] = 'organizationalPerson';
            $info['objectclass'][3] = 'posixAccount';
            $info['objectclass'][4] = 'shadowAccount';
            $info['objectclass'][5] = 'top';
            $info['givenName'] = $data['name'];
            $info['cn'] = $data['name'] . ' ' . $data['surname'];
            $info['sn'] = $data['surname'];
            $info['gecos'] = $info['cn'];
            $info['uid'] = $this->_inetOrgPersonMapper->generateUid($info['givenName'], $info['sn']);
            $info['uidNumber'] = $this->_inetOrgPersonMapper->nextUidNumber();
            $info['homeDirectory'] = $this->_inetOrgPersonMapper->getHomeDirectory($info['uid']);
            $info['sn'] = $data['surname'];
            $info['gidNumber'] = $info['uidNumber'];
            $info['loginShell'] = '/bin/sh';
            $info['userPassword'] = $data['password'];
        }

        ldap_modify($this->_ldap_con, 'uid=' . $info['uid'] . ', ou=users, dc=marol, dc=com, dc=pl', $info);
        ldap_close($this->_ldap_con);

        echo $this->_view->render('json_data.phtml', 'application_json', array('data' => $this->_inetOrgPersonMapper->fetch($info['uid'])));
    }

    public function GET_test_number(\Base $f3)
    {
        echo $this->_view->render('json_data.phtml', 'application_json', array('data' => $this->_inetOrgPersonMapper->nextUidNumber()));
    }
}
