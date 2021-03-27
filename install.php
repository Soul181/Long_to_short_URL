<?php
include 'vars.php';
date_default_timezone_set('Europe/Moscow'); // Устанавливаем часовой пояс
mysqli_query("CREATE USER '$username'@'$servername' IDENTIFIED BY '$password'");  // создание нового пользователя
mysqli_query("GRANT ALL PRIVILEGES ON * . * TO '$username'@'$servername'");   // неограниченый доступ у пользователя
mysqli_query("FLUSH PRIVILEGES"); // обновить, чтобы изменения вступили в силу
mysqli_query("CREATE DATABASE $database"); // создаем новую БД имя

$conn = new mysqli($servername, $username, $password); // Создаем новый класс mysqli
if ($conn) // если успешно, то 
	{
    
	mysqli_select_db($conn, "$database"); // выбираем эту БД
	mysqli_query($conn, "CREATE TABLE `$table_name`(`longurl` text NOT NULL, `shorturl` text NOT NULL, `time` int NOT NULL)"); // создаем таблицу
	mysqli_query($conn, "ALTER TABLE `$table_name` ADD PRIMARY KEY (`shorturl`(6))"); // присвоение столбцу короткий статуc ГЛАВНОГО 
	
	
	
	mysqli_query($conn, "CREATE DEFINER=`$username`@`$ip_domen` EVENT `$event_db_name` $ev_switcher SCHEDULE EVERY $ev_interval STARTS '$begin_time_ev' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `$table_name` WHERE NOW() - $timeout_del > `time`");
	}
?>
