# Authenticating with GitHub

Welcome to the first of a series of tutorials on using the GitHub API. In this tutorial, you will learn how to use GitHub's API to authenticate users to your own website, as demonstrated through a simple PHP application. 

Benefits of using GitHub to authenticate include:

- less development time
-	less code to implement
- reduced maintenance

Using simple PHP, we can focus in on the integration process without complicating the issue by introducing unfamiliar frameworks. Using PHP also allows us to avoid exposing sensitive data through client-side code. 

Throughout this tutorial, we have kept the code simple, so you will need to create your own error handling.

## Topics

- [How Does GitHub Authentication Work?](#how-does-github-authentication-work)
  - [What is an Access Token?](#what-is-an-access-token)
- [Registering Your Application with GitHub](#registering-your-application-with-github)

- Building Your Application
- Deploying Your Application
- Running Your Application

## How Does GitHub Authentication Work?

Like many other sites, GitHub uses a security framework called OAuth to allow users to grant third-party applications access to their GitHub data without giving them the password. To accomplish this, GitHub’s authorization server issues access tokens to third-party clients. When a user logs on to the system and provides credentials that authenticate against the authentication database, the logon service generates the access token, which is then used by the third party to access protected resources hosted by GitHub.

The basic process is as follows:

1. The third-party client initiates and redirects the user to GitHub.
2. The user authenticates.
3. GitHub redirects the user to the client, providing an authorization code.
4. The client exchanges the code for an access token.
5. The client accesses the API using the user’s access token.

### What is an Access Token?

An access token is a piece of data that accompanies a request to a server and is verified for authenticity before the server responds to the request. The third-party application provides a key, or _secret_, along with the token to allow the server to decode and verify it. Without the correct secret, the token is useless.

## Registering Your Application with GitHub

Before you can use GitHub authentication for your web application, you must first register it with GitHub. You can register your app under your personal account or under any organization to which you have administrative access.

Before you begin, you will need to know:

-	Your application’s main URL
-	Your callback URL (the URL of the page to which GitHub should redirect the user after authentication is complete)

Once you have gathered this info, you are ready to register.

1. Log in to your GitHub account.
2. In the upper-right corner, click your profile photo, then click **Settings**.

    ![Image](https://github.com/rachelkhoriander/tech-task/blob/master/tutorial/images/reg_2_sel_settings.png)
    
3. In the left sidebar, click **Developer settings**.

    ![Image](https://github.com/rachelkhoriander/tech-task/blob/master/tutorial/images/reg_3_sel_dev_settings.png)

4. In the left sidebar, click **OAuth Apps**.

    ![Image](https://github.com/rachelkhoriander/tech-task/blob/master/tutorial/images/reg_4_sel_reg_new_app.png)

5. Click **Register a new application**.

    ![Image](https://github.com/rachelkhoriander/tech-task/blob/master/tutorial/images/reg_5_reg_app.png)

6. Type in the details for your application, and click **Register application**.

    ---------------------------|-------------------------------
    Application name           | **Required.** The name of your application.
    Homepage URL               | **Required.** The full URL to your app's website. For security purposes, you should use https.
    Application description    | A description of your app that will be shared with users.
    Authorization callback URL | **Required.** The callback URL for your app. This is where GitHub will redirect users after they successfully log in. It must be on the same domain as your main URL and must be a valid URL. GitHub won't accept _localhost_.

When you have finished, your application will be assigned a Client ID and Client Secret. Also notice that you can edit any options for your app or revoke tokens from this page. In the process of testing you may need to do this a few times. https:// is your friend.
Screenshot of final page. Annotate?





My name is **Rachel** _Elizabeth_ 'Khoriander'

[Link](http://www.sems-tech.com)


