# Authenticating with GitHub

Welcome to the first of a series of tutorials on using the GitHub API. In this tutorial, you will learn how to use GitHub's API to authenticate users to your own website, as demonstrated through a simple PHP application. 

Benefits of using GitHub to authenticate include:

- less development time
- reduced maintenance

Using simple PHP, we can focus in on the integration process without complicating the issue by introducing unfamiliar frameworks. Using PHP also allows us to avoid exposing sensitive data through client-side code. 

Throughout this tutorial, we have kept the code simple, so you will need to create your own error handling.

## Topics

- [How Does GitHub Authentication Work?](#how-does-github-authentication-work)  
    - [What is an Access Token?](#what-is-an-access-token)  
- [Registering the Application with GitHub](#registering-the-application-with-github)  
- [Building the Application](#building-the-application)  
    - [Accessing the GitHub API (access_gh.php)](#accessing-the-github-api)  
        - [Authenticating Users](#authenticating-users)
        - [Getting an Access Token](#getting-an-access-token)
        - [Fetching User Data](#fetching-user-data)
    - [Building the Index Page (index.php)](#building-the-index-page)  
    - [Processing the Login (login.php)](#processing-the-login)  
    - [Processing the Callback (callback.php)](#processing-the-callback)  
    - [Building the Main Page (main.php)](#building-the-main-page)  
    - [Processing the Logout (logout.php)](#processing-the-logout)  
- [Deploying the Application](#deploying-the-application)  
- [Running the Application](#running-the-application)
- [Next Steps](#next-steps)

## How Does GitHub Authentication Work?

Like many other sites, GitHub uses a security framework called OAuth to allow users to grant third-party applications access to their GitHub data without giving them the password. To accomplish this, GitHub’s authorization server issues access tokens to third-party clients. When a user logs on to the system and provides credentials that authenticate against the authentication database, the logon service generates the access token, which is then used by the third party to access protected resources hosted by GitHub.

The basic process is as follows:

1. We initiate and redirect the user to GitHub.
2. The user authenticates.
3. GitHub redirects the user to us, providing an authorization code.
4. We exchange the code for an access token.
5. We access the API using the user’s access token.

### What is an Access Token?

An access token is a piece of data that accompanies a request to a server and is verified for authenticity before the server responds to the request. When making a request, we provide a key, or _secret_, along with the token to allow GitHub to decode and verify it. Without the correct secret, the token is useless.

## Registering the Application with GitHub

Before we can use GitHub authentication for our web application, we must first register our app with GitHub. You can register an app under your personal account or under any organization for which you are an administrator.

Before we begin, we will need to know:

-	Our application’s main URL
-	Our callback URL (the URL of the page to which GitHub should redirect the user after authentication is complete)

Once you have gathered this info, you are ready to register.

1. Log in to your GitHub account.
2. In the upper-right corner, click your profile photo, then click **Settings**.

    ![Screenshot: Select settings](images/reg_2_sel_settings.png)
    
3. In the left sidebar, click **Developer settings**.

    ![Screenshot: Select dev settings](images/reg_3_sel_dev_settings.png)

4. Click **Register a new application**.

    ![Screenshot: Register new app](images/reg_4_sel_reg_new_app.png)

5. Type in the details for your application, and click **Register application**.

    ![Screenshot: Enter app detail](images/reg_5_reg_app.png)

    Field                        | Description
    -----------------------------|-------------------------------
    `Application name`           | **Required.** Name of your application.
    `Homepage URL`               | **Required.** Full URL to your app's website. For security purposes, you should use the _https_ protocol.
    `Application description`    | Description of your app that will be shared with users.
    `Authorization callback URL` | **Required.** Callback URL for your app. This is where GitHub will redirect users after they successfully log in. It must be on the same domain as your main URL and must be a valid URL; GitHub won't accept _localhost_.

When you have finished, GitHub will assign your application a **Client ID** and **Client Secret**. 

![Screenshot: Edit app details](images/reg_6_app_reg_final.png)

> **Note:**  
> Notice that you can edit options for your app, track user metrics, and revoke user tokens from this page. This will come in handy during testing.

## Building the Application

Now that GitHub has assigned our application a **Client ID** and **Client Secret**, we can build our app. The app will include the following assets:

- **Main logic script** (include file - access_gh.php)  
    Acts as the brains of the operation: makes the initial call to the GitHub API, uses the returned authorization code to get an access token, and fetches user data.
- **Index page** (index.php)  
    Serves as the initial page of the app. Contains log in button.
- **Login script** (login.php)  
    Handles the login process.
-	**Callback script** (callback.php)  
    Handles callback logic and redirects the user appropriately.
-	**Main page** (main.php)  
    Serves as the main page of the app once the user logs in. Contains log out button.
-	**Logout script** (logout.php)  
    Handles the logout process.
-	**Stylesheet** (style.css)  
    Unnecessary, but included in the repo to make the app look a little prettier.

### Accessing the GitHub API

In this tutorial, the source code required to interact with the GitHub API is located in an include file (access_gh.php).

#### Authenticating Users
When we are ready to authenticate users, we'll need to send a GET request to GitHub to request an authorization code for our application.

`https://github.com/login/oauth/authorize?client_id=CLIENT_ID&redirect_url=REDIRECT_URL&scope=SCOPE`

Append the following parameters to the URL.

Name           | Description
---------------|-------------
`client_id`    | **Required.** Client ID that GitHub assigned to your application when you registered.
`redirect_uri` | URL in your application where GitHub will redirect users after they successfully log in. If not provided, GitHub will redirect users to the `Authorization callback URL` you provided when you registered your app.
`scope`        | Space-delimited list of permissions that your application is requesting. If not provided, the scope will default to an empty list for users who have not previously authorized any scopes for your app. For users who have authorized scopes, GitHub will return a set of all of the scopes the user has previously authorized for the app.
`state`        | Random string used to protect against cross-site request forgery attacks.

In the code snippet below, the `client_id` is pulled from the application’s GitHub registration page, and the `redirect_uri` is identical to the `Authorization callback URL` entered when registering the application with GitHub (see [Registering the Application with GitHub](#registering-the-application-with-github)).

```php
//Redirects to GitHub authentication page, restricting scope to read-only user data.
function goToAuthURL() {
    $client_id= "02786875d196f38bfdf1";
    $redirect_uri= "https://rachel.sems-tech.com/callback.php";
        
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $url = 'https://github.com/login/oauth/authorize?client_id='. $client_id. '&redirect_uri='. $redirect_uri.'&scope=read:user';
        header("location: $url");
    }
}
```

##### Choosing Scopes
Our application may request read or write access to specific types of users’ GitHub data.

-	Read access allows our app to look at data.
-	Write access allows our app to change data.

When users authenticate, GitHub lets them know what type of data our application wants to access and what type of access to that data our app is requesting.

>**Note**:  
> Normally, users will grant scopes identical to what we request, but remember that they can always choose to refuse or modify the type of access allowed; make sure to handle errors accordingly.

Scope Name         | Description
-------------------|-------------
(no scope)         | Read-only access to public information (public user profile info, public repo info, gists).
`Repo`             | Read/write access to code, commit statuses, invitations, collaborators, team membership, and deployment status for repos and organizations.
`repo:status`      | Read/write access to repo commit statuses. Apps do not gain access to code.
`repo_deployment`  | Access to deployment statuses for repos. Apps do not gain access to code.
`public_repo`      | Read/write access to code, commit statuses, collaborators, and deployment statuses for public repos and organizations.
`repo:invite`      | Accept/decline abilities for invitations to collaborate on a repo. Apps do not gain access to code.
`admin:org`        | Manage organizations, teams, and memberships.
`write:org`        | Publicize and unpublicize organization memberships.
`read:org`         | Read-only access to organizations, teams, and memberships.
`admin:public_key` | Manage public keys.
`write:public_key` | Create, list, and view details for public keys.
`read:public_key`  | List and view details for public keys.
`admin:repo_hook`  | Read, write, ping, and delete access to hooks in repos.
`write:repo_hook`  | Read, write, and ping access to hooks in repos.
`read:repo_hook`   | Read and ping access to hooks in repos.
`admin:org_hook`   | Read, write, ping, and delete access to organization hooks.
`Gist`             | Write access to gists.
`Notifications`    | Read access to a user's notifications. `Repo` also provides this access.
`User`             | Read/write access to profile info, including email addresses and the ability to follow or unfollow other users.
`read:user`        | Read access to user's profile data.
`user:email`       | Read access to user's email addresses.
`user:follow`      | Access to follow or unfollow other users.
`delete_repo`      | Access to delete adminable repos.
`admin:gpg_key`    | Fully manage GPG keys.
`write:gpg_key`    | Create, list, and view details for GPG keys.
`read:gpg_key`     | List and view details for GPG keys.

For more info, see [Scopes for OAuth Apps](https://developer.github.com/apps/building-oauth-apps/scopes-for-oauth-apps/) in the GitHub Developer documentation.

#### Getting an Access Token

Once redirected to GitHub, the user will be prompted to log in and authorize our application to access data. If the user accepts our request, GitHub redirects to the `redirect_uri` or `Authorization callback URL` with an authorization code, which we can exchange for an access token.

In the code snippets below, the `client_id` and `client_secret`are pulled from our app’s GitHub registration page, and the `redirect_uri` is identical to the `Authorization callback URL` entered when registering the application with GitHub (see [Registering the Application with GitHub](#registering-the-application-with-github)).

##### Parsing the Authorization Code
First, we need to parse the authorization code returned from GitHub.

```php
function fetchData() {

    $client_id= "02786875d196f38bfdf1";
    $redirect_uri= "https://rachel.sems-tech.com/callback.php";
    $client_secret = CLIENT_SECRET;
    
    //If authorization code was returned, extract authorization code.
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
```

##### Exchanging for an Access Token

Now, we need to build the querystring for the post request to exchange the authorization code for an access token.

`https://github.com/login/oauth/access_token?client_id=CLIENT_ID&client_secret=CLIENT_SECRET&code=AUTH_CODE&state=STATE`

Append the following parameters to the request:

Name             | Description
-----------------|-----------------
`client_id`      | **Required.** Client ID you received from GitHub when you registered your application.
`client_secret`  | **Required.** Client secret you received from GitHub when you registered your app.
`code`           | **Required.** Code you received as a response to your initial GitHub API call.
`redirect_uri`   | URL in your application where GitHub will redirect users after they successfully log in. If not provided, GitHub will redirect users to the `Authorization callback URL` you provided when you registered your app.
`state`          | Random string you provided in your initial GitHub API call.

> **Note:**  
> Never share your client secret with anyone; it's called a “secret” for a reason.

```php
            //Generate URL-encoded querystring from all variables.
            $post = http_build_query(array(
                        'client_id' => $client_id,
                        'redirect_uri' => $redirect_uri,
                        'client_secret' => $client_secret,
                        'code' => $code,
                    ));

            //Get and isolate access token returned from GitHub.
            $access_data = file_get_contents("https://github.com/login/oauth/access_token?". $post);
            $exploded1 = explode("access_token=", $access_data);
            $exploded2 = explode('&scope=read:user', $exploded1[1]);
            $access_token = $exploded2[0];
```

#### Fetching User Data
Any time we want to fetch user data from GitHub (within the approved scope), we send the access token along with our request.

```php
           // Build header
           $opts = [ 'http' => [
                             'method' => 'GET',
                             'header' => [ 'User-Agent: PHP']
                             ]
                    ];
               
            // Using access token, fetch user data.
            $url = "https://api.github.com/user?access_token=". $access_token;
            $context = stream_context_create($opts);
            $data = file_get_contents($url, false, $context);
 ```

##### Extracting User Data
The GitHub API sends and receives all data as JSON, so we will need to decode the JSON string and extract the data.

```php
           //Decode and convert JSON string to PHP variable, and extract pieces of data.
            $user_data = json_decode($data, true);
            $username = $user_data['login'];
            $email = $user_data['email'];
                     
               //Load user data into session variables.
            $_SESSION['user'] = $username;
            $_SESSION['email'] = $email;               
        }
    }
```

For more information about the data returned from the GitHub API, see our upcoming tutorial:
**Extracting Data from the GitHub API**.

### Building the Index Page

The user will arrive on the application's index page, which includes the **Sign In with GitHub** button.

```php
<?php
session_start();

//If session is not empty, redirect to callback page.
if (isset($_SESSION['user'])) {
     header("location: callback.php");
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Technical Task: Sign In with GitHub</title>
    <link href="style.css" rel="stylesheet">
  </head>
  <body>
    <div class="content">
      <p>Hello, world.</p>
      <p><a href="login.php"><input type='submit' name='submit' value='Sign In with GitHub' /></a></p>
    </div>
  </body>
</html>
```

### Processing the Login

Once the user clicks **Sign In with GitHub**, we will redirect the user to the GitHub API.

```php
<?php

require "access_gh.php";

//Redirect user to GitHub authentication page
goToAuthUrl();

//If redirect fails, then:
echo "Operation failed.";
```

### Processing the Callback

When the GitHub API responds to us, we will fetch the user data and redirect to the main application page. 

```php
<?php
 
session_start();
 
require "access_gh.php";

//Use code to get access token, then fetch user data from GitHub.
fetchData();

//If session is empty, redirect to index page, so user can log in.
if (!isset($_SESSION['user'])) {
     header("location: index.php");
}
//Else redirect to main page.
else {
     header("location: main.php");
}
```

### Building the Main Page

If the login is successful, we will display the main page with the **Sign Out** button.

```php
<?php

session_start();

//If session is empty, redirect to index page, so user can log in.
if (!isset($_SESSION['user'])){
    header("location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Technical Task: Sign Out with GitHub</title>
    <link href="style.css" rel="stylesheet">
  </head>
  <body>
    <div class="content">
      <p>Hello, <?php echo $_SESSION['user'] ?>.</p>
      <p>Your public email address is:<br/>
      <?php echo $_SESSION['email'] ?>.</p>
      <p><a href="logout.php"><input type='submit' name='submit' value='Sign Out' /></a></p>
    </div>   
  </body>
</html
```

### Processing the Logout

Once the user clicks **Sign Out**, we will destroy the session and redirect the user to the index page.

```php
<?php

session_start();

//Clear and destroy session.
unset($_SESSION['user']);
session_destroy();

//Redirect to index page, so user can log in.
header("location: index.php");
```

## Deploying the Application

To begin using PHP, we need to set up a proper development environment. Detailed instructions on how to set up this development environment on various platforms are available in the [online manual](http://www.php.net/manual/en/installation.php).

After you’ve successfully installed and tested PHP, copy the source code files to your computer.

1. On the command line, enter:  
    `git clone https://github.com/rachelkhoriander/tech-task.git`

2. Copy the PHP and CSS files in the `source` folder into the web folder of your development environment.


## Running the Application

Now that everything is properly configured, let’s run our application to review what users will see.

1.	Type the URL to the appication's index page (index.php), and click **Sign In with GitHub**.  

    ![Screenshot: Index page](images/run_1_index.png)
 
2.	Log in using GitHub’s API.  

    ![Screenshot: GitHub login](images/run_2_ghlogin.png)

3.	Authorize access to your GitHub user data.  

    ![Screenshot: GitHub authorization](images/run_3_ghauth.png)
 
    > Note:  
    > Notice the access level corresponds to the requested scope.

4.	Arrive at the application’s main page (main.php).  

    ![Screenshot: Main page](images/run_4_main.png)
 
## Next Steps
To learn more about GitHub's API, visit the [GitHub Developer Guide](https://developer.github.com/apps/getting-started-with-building-apps/).

To learn more about extracting data from the GitHub API, stay tuned for our forthcoming tutorial: **Extracting Data from the GitHub API**.
