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
 
 
