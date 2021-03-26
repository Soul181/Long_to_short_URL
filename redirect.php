<?php
include 'vars.php';
date_default_timezone_set('Europe/Moscow'); // Устанавливаем часовой пояс

$conn = @mysqli_connect($servername, $username, $password, $database); // Соединяемся с базой
if (!$conn) // если нет соединения с БД, то
	{
	$log = date('Y-m-d H:i:s') . ' Ошибка подключения к базе данных MySQL'; // Дата/время сообщение ошибка подключения
	$file = 'log.log'; // название файла в переменную
	file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
	exit();	
	}
mysqli_set_charset($conn, "utf8"); // установка кодировки

/////////////////////////////////////////////////////////////////////////////////////////////////

// получение части URL
$pass = $_SERVER['REQUEST_URI'];        				 // получение части url адреса под названием pass   вида "/pass"
$out = substr($pass,-strlen($pass)+1,strlen($pass)-1);   // вывод строки без слеша

/////////////////////////////////////////////////////////////////////////////////////////////////

// проверка на разрешенные символы и их количество (короткий из шести символов)
if (preg_match("/^[a-z0-9]{6}$/",$out)==1) // если есть полное совпадение с регулярным выражением, то 
	{
			$sql = "SELECT * FROM `$table_name` WHERE `shortURL`='$out'"; //формируем команду найти совпадение в БД
			$res = mysqli_query($conn, $sql); // отправляем команду в БД
			$row = mysqli_fetch_array($res); // получаем ответ из БД, есть совпадение, или нет
				if ($row == TRUE) //если совпадение есть, то
					{
						$log = date('Y-m-d H:i:s') . ' Введен короткий URL в адресную строку: ' . $out . " Осуществлён переход на сайт: " . $row[0];
						$file = 'log.log';
						file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
						mysqli_close($conn); // Закрываем соединеие с БД
						header ('Location: '.$row[0]);
											//0-longURL
											//1-shortURL
											//2-time			
						exit();
					} else  { // 404, совпадений в БД не обнаружено, переход на страницу error
								$log = date('Y-m-d H:i:s') . ' Совпадений в БД не найдено. ' . $out;
								$file = 'log.log';
								file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
								mysqli_close($conn); // Закрываем соединеие с БД
								header ('Location: error.html');	
								exit();
							}
	} else  { // 404, совпадений в БД не обнаружено, переход на страницу error
				//$log = date('Y-m-d H:i:s') . ' Некорректный короткий URL. ' . $out;
				//$file = 'log.log';
				//file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
				mysqli_close($conn); // Закрываем соединеие с БД
				header ('Location: error.html');	
				exit();
			}
?> 