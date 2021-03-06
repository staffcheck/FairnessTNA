<?php
/**********************************************************************************
 * This file is part of "FairnessTNA", a Payroll and Time Management program.
 * FairnessTNA is copyright 2013-2017 Aydan Coskun (aydan.ayfer.coskun@gmail.com)
 * others. For full attribution and copyrights details see the COPYRIGHT file.
 *
 * FairnessTNA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation, either version 3 of the License, or (at you option )
 * any later version.
 *
 * FairnessTNA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *********************************************************************************/


/**
 * @package Core
 */
class TTLDAP
{
    public $data = null;
    private $password_attribute = 'userPassword';

    //private $password_attribute = NULL;

    public function __construct()
    {
        // @codingStandardsIgnoreStart --  Unused global variable $LDAP_CONNECT_OPTIONS.
        global $LDAP_CONNECT_OPTIONS;
        //used in ADODB
        if ($this->checkLDAPExtension() == true) {
            $LDAP_CONNECT_OPTIONS = array(
                array('OPTION_NAME' => LDAP_OPT_DEREF, 'OPTION_VALUE' => 2),
                array('OPTION_NAME' => LDAP_OPT_SIZELIMIT, 'OPTION_VALUE' => 100),
                array('OPTION_NAME' => LDAP_OPT_TIMELIMIT, 'OPTION_VALUE' => 30),
                array('OPTION_NAME' => LDAP_OPT_PROTOCOL_VERSION, 'OPTION_VALUE' => 3),
                array('OPTION_NAME' => LDAP_OPT_ERROR_NUMBER, 'OPTION_VALUE' => 13),
                array('OPTION_NAME' => LDAP_OPT_REFERRALS, 'OPTION_VALUE' => false),
                array('OPTION_NAME' => LDAP_OPT_RESTART, 'OPTION_VALUE' => false)
            );
        }
        // @codingStandardsIgnoreEnd
        return true;
    }

    public function checkLDAPExtension()
    {
        if (function_exists('ldap_connect')) {
            return true;
        }

        return false;
    }

    public function setHost($value)
    {
        $value = trim($value);

        $this->data['host'] = $value;
        return true;
    }

    public function setPort($value)
    {
        $this->data['port'] = $value;
        return true;
    }

    public function setBindUserName($value)
    {
        $value = trim($value);

        $this->data['bind_user_name'] = $value;
        return true;
    }

    public function setBindPassword($value)
    {
        $this->data['bind_password'] = trim($value);

        return true;
    }

    public function setBaseDN($value)
    {
        $value = trim($value);

        $this->data['base_dn'] = $value;
        return true;
    }

    public function setBindAttribute($value)
    {
        $value = trim($value);

        $this->data['bind_attribute'] = $value;
        return true;
    }

    public function setUserFilter($value)
    {
        $value = trim($value);

        $this->data['user_filter'] = $value;
        return true;
    }

    public function setLoginAttribute($value)
    {
        $value = trim($value);

        $this->data['login_attribute'] = $value;
        return true;
    }

    public function getUserNameSuffix()
    {
        if (isset($this->data['user_name_suffix'])) {
            return $this->data['user_name_suffix'];
        }

        return false;
    }

    public function setUserNameSuffix($value)
    {
        $value = trim($value);

        $this->data['user_name_suffix'] = $value;
        return true;
    }

    public function authenticate($user_name, $password)
    {
        $user_name = trim($user_name);
        $password = trim($password);

        $authentication_start_time = microtime(true);
        $retval = false;
        if ($this->checkLDAPExtension() == true) {
            $connection_result = false;
            $ldap_data = false;

            $ldap = NewADOConnection('ldap');
            $ldap->port = $this->getPort(); //If port is 636, use SSL instead.

            //In order to use LDAP over SSL with a invalid certificate, /etc/ldap/ldap.conf or C:\OpenLDAP\sysconf\ldap.conf must exist with the following line:
            //   TLS_REQCERT ALLOW
            // PHP-FPM/Apache must then be restarted. This will prevent certification validation from happening.

            Debug::Text('LDAP: Host: ' . $this->getHost() . ' Port: ' . $this->getPort() . ' Bind User Name: ' . $this->getBindUserName() . ' Bind Password: ' . $this->getBindPassword() . ' Bind DN: ' . $this->getBindDN($user_name) . ' Base DN: ' . $this->getBaseDN() . ' Bind Authentication Mode: ' . (int)$this->isBindAuthentication() . ' Password: ****', __FILE__, __LINE__, __METHOD__, 10);
            try {
                //Poor mans timeout if we aren't using PHP v5.3 (which supports a built-in LDAP timeout setting)
                $fp = @fsockopen(str_ireplace('ldaps://', '', $this->getHost()), $this->getPort(), $errno, $errstr, 3);
                if ($fp == false) {
                    Debug::Text('LDAP socket connection failed/timedout: ' . $errstr . ' (' . $errno . ')', __FILE__, __LINE__, __METHOD__, 10);
                } else {
                    fclose($fp);
                    if ($this->isBindAuthentication() == true) {
                        try {
                            Debug::Text('aLDAP Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
                            //Attempt to connect with the raw user_name first, if that fails, try with a full BindDN
                            $connection_result = $ldap->Connect($this->getHost(), $user_name, $password, $this->getBaseDN());
                        } catch (exception $e) {
                            Debug::Text('bLDAP Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
                            $connection_result = $ldap->Connect($this->getHost(), $this->getBindDN($user_name), $password, $this->getBaseDN());
                        }
                    } else {
                        if (strtolower($this->getBindUserName()) == 'anonymous') {
                            Debug::Text('LDAP Anonymous Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
                            $connection_result = $ldap->Connect($this->getHost(), '', '', $this->getBaseDN());
                        } else {
                            Debug::Text('LDAP BindUser Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
                            $connection_result = $ldap->Connect($this->getHost(), $this->getBindUserName(), $this->getBindPassword(), $this->getBaseDN());
                        }
                    }
                    Debug::Text('LDAP Connection Result: ' . (int)$connection_result, __FILE__, __LINE__, __METHOD__, 10);
                }
            } catch (exception $e) {
                Debug::Text('LDAP Connection Failed!: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
            }

            $filter = $this->getFilterQuery($user_name);
            Debug::Text('LDAP Filter User: ' . $filter, __FILE__, __LINE__, __METHOD__, 10);

            if ($connection_result == true and $filter != '') {
                $ldap->SetFetchMode(ADODB_FETCH_ASSOC);

                try {
                    $ldap_data = $ldap->GetRow($filter);

                    if (is_array($ldap_data) and count($ldap_data) > 0) {
                        if ($this->getLoginAttribute() != '' and isset($ldap_data[$this->getLoginAttribute()])) {

                            //Usernames are passed here in lowercase already, so it should be case insensitive match.
                            if (strtolower($ldap_data[$this->getLoginAttribute()]) == strtolower($user_name)) {
                                //Try to compare plain text password if it exists
                                if (isset($ldap_data[$this->password_attribute])) {
                                    if ($ldap_data[$this->password_attribute] == $password) {
                                        Debug::Text('LDAP authentication success! (z)', __FILE__, __LINE__, __METHOD__, 10);
                                        $retval = true;
                                    } else {
                                        Debug::Text('LDAP password comparison failed... LDAP Password attribute: ' . $ldap_data[$this->password_attribute], __FILE__, __LINE__, __METHOD__, 10);
                                    }
                                } else {
                                    //If no password attribute exists, and we're using bind authentication, still return true as we've matched the password and user filter
                                    Debug::Text('LDAP no password attribute exists...', __FILE__, __LINE__, __METHOD__, 10);
                                    if ($this->isBindAuthentication() == true) {
                                        Debug::Text('LDAP matched filter, but no password attribute exists, returning TRUE...', __FILE__, __LINE__, __METHOD__, 10);
                                        $retval = true;
                                    } else {
                                        //Bind authentication was not used, however we found the user with no password attribute, so attempt
                                        //to rebind with the discovered bindAttribute to test the password for cases where it may include a fully qualified domain.
                                        //This should avoid the need to suffix the user_name with '@mydomain.com'
                                        if (isset($ldap_data[$this->getBindAttribute()]) and $ldap_data[$this->getBindAttribute()] != '') {
                                            try {
                                                Debug::Text('aLDAP post-search Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
                                                //Attempt to connect with the raw post-search filter data first, if that fails, try with a full BindDN
                                                $retval = $ldap->Connect($this->getHost(), $ldap_data[$this->getBindAttribute()], $password, $this->getBaseDN());
                                            } catch (exception $e) {
                                                Debug::Text('bLDAP post-search Bind Authentication Mode...', __FILE__, __LINE__, __METHOD__, 10);
                                                $retval = $ldap->Connect($this->getHost(), $this->getBindDN($ldap_data[$this->getBindAttribute()]), $password, $this->getBaseDN());
                                            }
                                            Debug::Text('LDAP post-search bind connection result: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
                                        } else {
                                            Debug::Text('BindAttribute not found in users LDAP record...', __FILE__, __LINE__, __METHOD__, 10);
                                        }
                                    }
                                }
                            } else {
                                Debug::Text('LDAP Login Attribute does not match user name...', __FILE__, __LINE__, __METHOD__, 10);
                            }
                        } else {
                            Debug::Text('LDAP Login Attribute not found or not set...', __FILE__, __LINE__, __METHOD__, 10);
                            if ($this->isBindAuthentication() == true) {
                                Debug::Text('LDAP matched filter, but no login attribute exists, returning TRUE...', __FILE__, __LINE__, __METHOD__, 10);
                                $retval = true;
                            }
                        }
                    } else {
                        Debug::Text('LDAP Filter data not found...', __FILE__, __LINE__, __METHOD__, 10);
                    }

                    if ($retval == false) {
                        Debug::Arr($ldap_data, 'LDAP Data: ', __FILE__, __LINE__, __METHOD__, 10);
                    }
                } catch (exception $e) {
                    Debug::Text('LDAP Filter Failed!: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
                }
            } elseif ($this->isBindAuthentication() === true and $connection_result === true) {
                Debug::Text('LDAP bind authentication success! (a)', __FILE__, __LINE__, __METHOD__, 10);
                $retval = true;
            }

            $ldap->Close();
        }

        Debug::Text('LDAP authentication result: ' . (int)$retval . ' Total Time: ' . (microtime(true) - $authentication_start_time) . 's', __FILE__, __LINE__, __METHOD__, 10);
        return $retval;
    }

    public function getPort()
    {
        if (isset($this->data['port'])) {
            return $this->data['port'];
        }

        return 389; //Default port.
    }

    public function getHost()
    {
        if (isset($this->data['host'])) {
            return $this->data['host'];
        }

        return false;
    }

    public function getBindUserName()
    {
        if (isset($this->data['bind_user_name'])) {
            return $this->data['bind_user_name'];
        }

        return false;
    }

    public function getBindPassword()
    {
        if (isset($this->data['bind_password'])) {
            return $this->data['bind_password'];
        }

        return false;
    }

    //This is not fully implemented, not sure if its even needed, as it appears
    //most active directory installs will work without needing to specify the domain.

    public function getBindDN($user_name)
    {
        //return $this->getBindAttribute().'='. $user_name .', '.$this->getBaseDN();
        $retval = '';
        if ($this->getBindAttribute() != '') {
            $retval .= $this->getBindAttribute() . '=';
        }

        $retval .= $user_name . ', ' . $this->getBaseDN();

        return $retval;
    }

    public function getBindAttribute()
    {
        if (isset($this->data['bind_attribute'])) {
            return $this->data['bind_attribute'];
        }

        return false;
    }

    public function getBaseDN()
    {
        if (isset($this->data['base_dn'])) {
            return $this->data['base_dn'];
        }

        return false;
    }

    //Bind authentication is when a specific bind User/Password is *not* specified,
    //so we try to initially bind as the username trying to login instead.
    //However when bind username/password is specified, we can still attempt to bind as the username trying to login after a filter query is run.

    public function isBindAuthentication()
    {
        if ($this->getBindUserName() == '') { //Don't check password, as anonymous binding doesn't have one specified.
            return true;
        }

        return false;
    }

    public function getFilterQuery($user_name)
    {
        $filter_query = '';
        $filter_count = 0;

        if ($this->getLoginAttribute() != '') {
            $filter_query = '(' . $this->getLoginAttribute() . '=' . $user_name . ')';
            $filter_count++;
        }

        if ($this->getUserFilter() != '') {
            $filter_query .= '(' . $this->getUserFilter() . ')';
            $filter_count++;
        }

        if ($filter_count > 1) {
            $filter_query = '(&' . $filter_query . ')';
        }

        return $filter_query;
    }

    public function getLoginAttribute()
    {
        if (isset($this->data['login_attribute'])) {
            return $this->data['login_attribute'];
        }

        return false;
    }

    public function getUserFilter()
    {
        if (isset($this->data['user_filter'])) {
            return $this->data['user_filter'];
        }

        return false;
    }
}
