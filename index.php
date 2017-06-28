<?php


$link = mysqli_connect('localhost', 'root', '');  
if (!$link)  
{  
  $error = 'Unable to connect to the database server.';  
  include 'error.html.php';  
  exit();  
}  
  
if (!mysqli_set_charset($link, 'utf8'))  
{  
  $output = 'Unable to set database connection encoding.';  
  include 'output.html.php';  
  exit();  
}  
  
if (!mysqli_select_db($link, 'joke'))  
{  
  $error = 'Unable to locate the joke database.';  
  include 'error.html.php';  
  exit();  
}  
  
$result = mysqli_query($link, 'SELECT joketext FROM joke');  
if (!$result)  
{  
  $error = 'Error fetching index: ' . mysqli_error($link);  
  include 'error.html.php';  
  exit();  
}  
  
while ($row = mysqli_fetch_array($result))  
{  
  $index[] = $row['joketext'];  
}  
require 'config.php';
require 'functions.php';  
run();
