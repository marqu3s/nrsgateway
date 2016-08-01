<?php
/**
 * Created by PhpStorm.
 * User: joao
 * Date: 29/07/16
 * Time: 12:11
 */

namespace marqu3s\nrsgateway;

/**
 * Class NRSGateway
 *
 * Class to allow the consumption of SMS services offered by NRS Gateway - http://www.nrsgateway.com
 *
 * @package marqu3s\nrsgateway
 */
class NRSGateway
{
    /** @var string $username The user to use in authentication. */
    public $username;

    /** @var string $password The password to use in authentication. */
    public $password;

    /** @var string $_base64AuthHeader The base64 string representation of "username:password" string. */
    public $base64AuthHeader;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->base64AuthHeader = "Authorization: Basic " . base64_encode($username . ':' . $password);
    }
}
