RoutmouteDiscordBundle
======================

Manual Installation
-------------------

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


### Step 1: Create configuration file

Create configuration file `config/packages/routmoute_discord.yaml` and modify scopes if you want
```yaml
// config/packages/routmoute_discord.yaml

routmoute_discord:
    oauth:
        client_id: '%env(ROUTMOUTE_DISCORD_CLIENT_ID)%'
        client_secret: '%env(ROUTMOUTE_DISCORD_CLIENT_SECRET)%'
        scope:
            - identify
            - email
    api:
        bot_token: '%env(ROUTMOUTE_DISCORD_BOT_TOKEN)%'
```


### Step 2: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require routmoute/routmoute-discord-bundle
```


Configuration
-------------

### Step 1: Create your Discord Application

- Go to https://discord.com/developers/applications
- Create a New Application
- Copy `CLIENT ID` and `CLIENT SECRET` for next step
- Go to `OAuth2` Tab
- Add Redirect `https://yourDomain.domain/receiveDiscord`
- Go to Bot Tab and copy `TOKEN` for next step


### Step 2: Create your env variables

Add this environments vars in your `.env` file.
```
ROUTMOUTE_DISCORD_CLIENT_ID=YourClientId
ROUTMOUTE_DISCORD_CLIENT_SECRET=YourClientSecret
ROUTMOUTE_DISCORD_BOT_TOKEN=YourBotToken
```


Usage (for Symfony 5)
---------------------

## Discord OAuth

### Step 1: Create Controller in your App

Create Controller, for exemple `src/Controller/DiscordOAtuhController.php`

```php
// src/Controller/DiscordOAtuhController.php

<?php

namespace App\Controller;

use Routmoute\Bundle\RoutmouteDiscordBundle\Service\RoutmouteDiscordOAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DiscordOAuthController extends AbstractController
{
    /**
     * @Route("/connectToDiscord", name="routmoute_discord_redirect", methods="GET")
     */
    public function redirectToDiscord(RoutmouteDiscordOAuthService $oAuthService, UrlGeneratorInterface $urlGenerator): RedirectResponse
    {
        $redirectUrl = $urlGenerator->generate('routmoute_discord_receiver', [], UrlGeneratorInterface::ABSOLUTE_URL);
        return new RedirectResponse($oAuthService->getRedirectDiscordUrl($redirectUrl));
    }

    /**
     * @Route("/receiveDiscord", name="routmoute_discord_receiver", methods="GET")
     */
    public function receiveFromDiscordAuthorize(Request $request, RoutmouteDiscordOAuthService $oAuthService, UrlGeneratorInterface $urlGenerator): RedirectResponse
    {
        $redirectUrl = $urlGenerator->generate('routmoute_discord_receiver', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $userData = $oAuthService->getUserData($request, $redirectUrl);

        // TODO: Process userData and change path_to_redirect

        return $this->redirectToRoute('path_to_redirect');
    }
}
```


### Step 2: Create your redirect button

Create a button in your frontend that redirect to `routmoute_discord_redirect` path.

for example, in twig template:
```html
<a href="{{ path('routmoute_discord_redirect') }}">
    <button type="button">Link my account with discord</button>
</a>
```


## Discord API (Bot)

Example usage in Controller:
```php
<?php
namespace App\Controller;

use Routmoute\Bundle\RoutmouteDiscordBundle\Service\RoutmouteDiscordApiService;

class MyController extends AbstractController
{
    public function index(RoutmouteDiscordApiService $discordAPI)
    {
        $discordId = 'theUserDiscordId';

        $userInfos = $discordAPI->getUserFromDiscordId($discordId);

        $userInfos["id"];
        $userInfos["username"];
        $userInfos["discriminator"];
        $userInfos["avatar"];
    }
}
```
https://discord.com/developers/docs/resources/user


Parameters
----------

#### `client_id`
_Required_

The `CLIENT ID` provided by discord

#### `client_secret`
_Required_

The `CLIENT SECRET` provided by discord

#### `scope`
_Required_

The Discord API scopes - https://discord.com/developers/docs/topics/oauth2#shared-resources
- `identify` - discordId, avatar, username, discriminator
- `email` - email
- ...

#### `bot_token`
_Required_

The Bot `TOKEN` provided by discord