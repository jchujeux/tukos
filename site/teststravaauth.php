<?php
$phpDir = dirname(__DIR__) . '/';
require $phpDir . 'TukosLib/TukosFramework.php';
require $phpDir . 'TukosLib/cmdenv.php';

use Strava\API\OAuth;
use Strava\API\Exception;
use TukosLib\TukosFramework as Tfk;

try {
    $options = [
        'clientId'     => 77448,
        'clientSecret' => 'b86453ce6aaee6808eb5b1e6fb58d3a5f7e0dbdb',
        'redirectUri'  => 'http://localhost/tukos/teststravaauth.php?tutu=toto&tata=titi'
    ];
    Tfk::initialize('commandLine', 'tukosApp', $phpDir);
    $oauth = new OAuth($options);
    
    if (!isset($_GET['code'])) {
        print '<a href="'.$oauth->getAuthorizationUrl([
            // Uncomment required scopes.
            'scope' => [
                'read',
                // 'read_all',
                // 'profile:read_all',
                // 'profile:write',
                'activity:read',
                // 'activity:read_all',
                // 'activity:write',
            ]
        ]).'">Connect</a>';
    } else {
        $token = $oauth->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        print $token->getToken();
    }
} catch(Exception $e) {
    print $e->getMessage();
}