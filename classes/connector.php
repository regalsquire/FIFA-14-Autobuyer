<?php namespace FIFA14;

use Guzzle\Http\Client;
use Guzzle\Plugin\Cookie\CookiePlugin;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;

class Connector
{
    
    private $_loginDetails = array();
    private $_loginResponse = array();

    private $MainPageURL = "http://www.easports.com/uk/fifa/football-club/ultimate-team";
    private $LoginURL = "https://www.easports.com/services/authenticate/login";
    private $NucleusIdURL = "http://www.easports.com/iframe/fut/?locale=en_GB&baseShowoffUrl=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team";
    private $ShardsURL = "http://www.easports.com/iframe/fut/p/ut/shards?_=";
    private $UserAccountsURL = "http://www.easports.com/iframe/fut/p/ut/game/fifa14/user/accountinfo?_=";
    private $SessionIdURL = "http://www.easports.com/iframe/fut/p/ut/auth";
    private $ValidateURL = "http://www.easports.com/iframe/fut/p/ut/game/fifa14/phishing/validate";
    private $PhishingURL = "http://www.easports.com/iframe/fut/p/ut/game/fifa14/phishing/question?_=";

    
    public function __construct($loginDetails) {
        $this->_loginDetails = $loginDetails;
    }

    public function Connect() {

        $client = new Client(null);
        $cookiePlugin = new CookiePlugin(new ArrayCookieJar());
        $client->addSubscriber($cookiePlugin);
        $client->setUserAgent("Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36");

        $login_url = $this->GetMainPage($client, $this->MainPageURL);
        $this->Login($client, $this->_loginDetails, $login_url);
        $nucleusId = $this->GetNucleusId($client, $this->_loginDetails, $this->NucleusIdURL);
        $this->GetShards($client, $nucleusId, $this->ShardsURL);
        $userAccounts = $this->GetUserAccounts($client, $nucleusId, $this->_loginDetails, $this->UserAccountsURL);
        $sessionId = $this->GetSessionId($client, $nucleusId, $userAccounts, $this->_loginDetails, $this->SessionIdURL);
        $phishing = $this->Phishing($client, $this->_loginDetails, $nucleusId, $sessionId, $this->PhishingURL);

        if (isset($phishing['debug']) && $phishing['debug'] == "Already answered question.") {
            $phishingToken = $phishing['token'];
        } else {
            $phishingToken = $this->Validate($client, $this->_loginDetails, $nucleusId, $sessionId, $this->ValidateURL);
        }

        $this->_loginResponse = array(
            "nucleusId" => $nucleusId,
            "userAccounts" => $userAccounts,
            "sessionId" => $sessionId,
            "phishingToken" => $phishingToken,
            "cookies" => $cookiePlugin
            );

        return $this->_loginResponse;

    }

    private function GetMainPage($client, $url) {

        $request = $client->get($url);

        $response = $request->send();

        return $response->getInfo('url');

    }

    private function Login($client, $loginDetails, $url) {

        $request = $client->post($url, array(), array(
            "email" => $loginDetails['username'],
            "password" => $loginDetails['password'],
            "_rememberMe" => "on",
            "rememberMe" => "on",
            "_eventId" => "submit",
            "facebookAuth" => ""
        ));

        $response = $request->send();

    }

    private function GetNucleusId($client, $loginDetails, $url) {

        $request = $client->get($url);

        $response = $request->send();
        $body = $response->getBody(true);

        $matches = array();

        preg_match("/var\ EASW_ID = '(\d*)';/", $body, $matches);
        
        return $matches[1];

    }

    private function GetShards($client, $nucleusId, $url) {

        $request = $client->get($url . time());

        $request->addHeader('Easw-Session-Data-Nucleus-Id', $nucleusId);
        $request->addHeader('X-UT-Embed-Error', 'true');
        $request->addHeader('X-UT-Route', 'https://utas.fut.ea.com');
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setHeader('Accept', 'application/json, text/javascript');
        $request->setHeader('Accept-Language', 'en-US,en;q=0.8');
        $request->setHeader('Referer', 'http://www.easports.com/iframe/fut/?baseShowoffUrl=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team&locale=en_GB');

        $response = $request->send();

    }

    private function GetUserAccounts($client, $nucleusId, $loginDetails, $url) {

        $route;
        if (strtolower($loginDetails['platform']) == "xbox360") {
            $route = 'https://utas.fut.ea.com:443';
        } else {
            $route = 'https://utas.s2.fut.ea.com:443';
        }

        $request = $client->get($url . time());

        $request->addHeader('Easw-Session-Data-Nucleus-Id', $nucleusId);
        $request->addHeader('X-UT-Embed-Error', 'true');
        $request->addHeader('X-UT-Route', $route);
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setHeader('Accept', 'application/json, text/javascript');
        $request->setHeader('Accept-Language', 'en-US,en;q=0.8');
        $request->setHeader('Referer', 'http://www.easports.com/iframe/fut/?baseShowoffUrl=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team&locale=en_GB');

        $response = $request->send();

        return $response->json();
    }

    private function GetSessionId($client, $nucleusId, $userAccounts, $loginDetails, $url) {

        $route;
        if (strtolower($loginDetails['platform']) == "xbox360") {
            $route = 'https://utas.fut.ea.com:443';
        } else {
            $route = 'https://utas.s2.fut.ea.com:443';
        }

        $persona = array();
        $lastAccessTime = array();

        foreach ($userAccounts['userAccountInfo']['personas'][0]['userClubList'] as $key) {
            $persona[] = $key;
        }

        foreach ($persona as $key) {
            $lastAccessTime[] = $key['lastAccessTime'];
        }

        $latestAccessTime = max($lastAccessTime);
        $lastUsedPersona  = $persona[array_search($latestAccessTime, $lastAccessTime)];

        $personaId = $userAccounts['userAccountInfo']['personas'][0]['personaId'];
        $personaName = $userAccounts['userAccountInfo']['personas'][0]['personaName'];
        $platform = $this->getNucleusPlatform($loginDetails['platform']);

        $data_array = array(
            'isReadOnly' => false,
            'sku' => 'FUT14WEB',
            'clientVersion' => 1,
            'nuc' => $nucleusId,
            'nucleusPersonaId' => $personaId,
            'nucleusPersonaDisplayName' => $personaName,
            'nucleusPersonaPlatform' => $platform,
            'locale' => 'en-GB',
            'method' => 'authcode',
            'priorityLevel' => 4,
            'identification' => array('authCode' => ''));
        $data_string = json_encode($data_array);

        $request = $client->post($url, array(), $data_string);

        $request->addHeader('Easw-Session-Data-Nucleus-Id', $nucleusId);
        $request->addHeader('X-UT-Embed-Error', 'true');
        $request->addHeader('X-UT-Route', $route);
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setHeader('Accept', 'application/json, text/javascript');
        $request->setHeader('Accept-Language', 'en-US,en;q=0.8');
        $request->setHeader('Referer', 'http://www.easports.com/iframe/fut/?baseShowoffUrl=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team&locale=en_GB');
        $request->setHeader('Content-Type', 'application/json');
        $request->setHeader('Content-Length', strlen($data_string));

        $response = $request->send();

        $sessionId = $response->json();

        return $sessionId['sid'];
    }

    private function Phishing($client, $loginDetails, $nucleusId, $sessionId, $url) {

        if (strtolower($loginDetails['platform']) == "xbox360") {
            $route = 'https://utas.fut.ea.com:443';
        } else {
            $route = 'https://utas.s2.fut.ea.com:443';
        }

        $request = $client->get($url . time());

        $request->addHeader('Easw-Session-Data-Nucleus-Id', $nucleusId);
        $request->addHeader('X-UT-Embed-Error', 'true');
        $request->addHeader('X-UT-Route', $route);
        $request->addHeader('X-UT-SID', $sessionId);
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setHeader('Accept', 'application/json, text/javascript');
        $request->setHeader('Accept-Language', 'en-US,en;q=0.8');
        $request->setHeader('Referer', 'http://www.easports.com/iframe/fut/?baseShowoffUrl=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Fuk%2Ffifa%2Ffootball-club%2Fultimate-team&locale=en_GB');

        $response = $request->send();
        
        return $response->json();

    }

    private function Validate($client, $loginDetails, $nucleusId, $sessionId, $url) {

        $route;
        if (strtolower($loginDetails['platform']) == "xbox360") {
            $route = 'https://utas.fut.ea.com:443';
        } else {
            $route = 'https://utas.s2.fut.ea.com:443';
        }

        $data_string = "answer=" . $loginDetails['hash'];

        $request = $client->post($url, array(), $data_string);

        $request->addHeader('X-UT-SID', $sessionId);
        $request->addHeader('X-UT-Route', $route);
        $request->addHeader('Easw-Session-Data-Nucleus-Id', $nucleusId);
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request->setHeader('Content-Length', strlen($data_string));

        $response = $request->send();

        $json = $response->json();

        return $json['token'];

    }

    private function getNucleusPlatform($platform) {
        switch ($platform) {
            case "ps3":
                return "ps3";
            case "xbox360":
                return "360";
            case "pc":
                return "pc";
            default:
                return "360";
        }
    }


}
