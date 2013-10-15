FIFA-14-Autobuyer
=================
## The idea
The idea of this Autobuyer is that everything that get's contributed, will be checked and pushed to the opensource autobuyer. I will host the website and make sure the submitted code is safe. Neither the contributor nor am I responsible in case your EA credentials are abused or obtained. The more contributors, the better. Everything will be completely free and I'm aiming to make it as user friendly as humanly possible. This Autobuyer cannot be used for commercial purposes. It is ok to download it and install it on your own web host but selling licenses for it will certainly be not allowed and punished according to the Attribution-NonCommercial-ShareAlike 3.0 Unported License.

Suggestions are always welcome! Donations will go towards the web hosting so it can be accessed and used by all of you.

I'm doing this because the average AB is starting to cost over 100 euro's and the effectivity is strongly starting to decrease.

## How to contribute?
By writing classes that would be useful for the Autobuyer. But that's certainly not all! Everybody makes mistakes so it would be useful if you could track down bugs and fix them or make a note for somebody else so he can fix it. You can also help by promoting and spreading the word. Know somebody who is thinking about spending his savings on an expensive Autobuyer? Show him this page. 

If you want to contribute to the code but have no idea on how to get those EA URLs. Use Fiddler or the Google Chrome Networking tool. The only way to pretend that our AB is actually the Web App is by CURL headings and a couple of other tricks. To be able to make request that require your account to be logged in, use the cookies you acquire from the connector. It will return the necessary cookies in a PHP array. A detailed guide on this one will be written soon. I'm currently very busy with the AB itself so if anyone with AB knowledge could help me write the instructions, much appreciated. 

Without any help from the community, I will not be able to finish the project which means you won't be able to use it. 

## To-do
- Web design, the whole front end!
- Starting on the EA search class (Almost done!)
- Choosing a safe and up-to date framework (Currently not as important)
- Deciding what will be Javascript and what will be done server side

## Sample usage                                             
[Initialization](https://github.com/ipsq/FIFA-14-Autobuyer#initialization)  


### Initialization
```php
require 'classes/connector.php'
$con = new Connector($loginDetails);
$connection = $con->connect();
```

The Login Details array should look like this:
```php
$loginDetails = array(
    "username" => $email,
    "password" => $password,
    "hash" => $hash,
    "platform" => xbox360 || ps3 || pc,
);
```

This will return the session and token information used to make Search and Bid calls. It will be returned in an array with the following layout:

```php
$loginResponse = array(
    "nucleusId" => $nucleusId,
    "userAccounts" => $userAccounts,
    "sessionId" => $sessionId,
    "phishingToken" => $phishingToken,
    "cookies" => $cookiePlugin,
    "platform" => $this->_loginDetails['platform']
);
```

These can be used by grabbing the array key like so: 
```php
$nucID = $loginResponse['nucleusId']; 
```


