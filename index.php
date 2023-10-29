<?php
require_once("class.php");
session_start();

$username = 'tpvge';
$validFields = ['login', 'avatar_url', 'name', 'public_repos', 'followers', 'following'];





$user = new GitHubUser($username, $validFields);
$user->getUserData();
$userProfileCardHtml = $user->getUserProfileCardHtml();
$lastAccessDate = GitHubUser::getLastAccessDateFromCookies();
if ($lastAccessDate) {
    echo "Дата последнего обращения к GitHub: $lastAccessDate<br>";
} else {
    echo "Куки с датой последнего обращения не установлены.<br>";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <? echo $userProfileCardHtml; ?>
</body>

</html>
<?
//	Поместить метод в одну переменную и вызвать.
$getDataFunction = [GitHubUser::class, 'getAllUserData'];
$userData = $getDataFunction($username);
if ($userData !== null) {
    foreach ($userData as $field => $value) {
        echo "$field: $value <br>";
    }
} else {
    echo 'Произошла ошибка при получении данных пользователя.';
}

?>
<?
session_write_close();
?>
