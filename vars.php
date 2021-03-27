<?php
$emptyvalue = ""; 			// Переменная хранит пустое значение 
$servername = "localhost" ;  // Имя Хост Доменное имя
$username = "root";         // Имя созданного вами пользователя
$password = "root";         // Пароль пользователя 
$database = "db_url_mysql";     // Имя базы данных
$strword = 6; // Количество символов в рандом слове - короткий URL
$allstring = "0123456789abcdefghijklmnopqrstuvwxyz"; // Исходная сторка символов, разрешенные символы для короткого URL 
$trueword = 0; // Используется в цикле проверки слова по символам, перебираю каждый символ на корректность
$file = 'log.log'; // Название файла в переменную, файл хранит логи сайта
$table_name = "long_to_short"; // Имя таблицы в БД    long_to_short
$event_db_name = "del_15_days"; // название события БД
$ip_domen = "localhost"; // ip домена???
$timeout_del = 1296000; // срок действия ссылки 1296000 это 15 суток в секундах 


$ev_interval = "1 DAY"; // интервал включения события
$begin_time_ev = "2021-03-25 22:25:00"; // дата и время начала этого события
$ev_switcher = "ON"; // тумблер события ON включено OFF выключено


// добавить переменную в формате timestamp на 15 дней это 1296000

//$sql = "DROP EVENT `my_1`CREATE EVENT `my_1` ON SCHEDULE EVERY 1 MINUTE STARTS \'2021-03-25 22:25:00\' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `long_to_short` WHERE TIMESTAMP - 60 > time";

?>
