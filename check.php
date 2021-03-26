<?php
require_once 'install.php'; // однократно подключаем файла
include 'vars.php';
date_default_timezone_set('Europe/Moscow'); // Устанавливаем часовой пояс

$conn = @mysqli_connect($servername, $username, $password, $database); // Соединяемся с базой
if (!$conn) // если нет соединения с БД, то
	{
		$log = date('Y-m-d H:i:s') . ' Ошибка подключения к базе данных MySQL'; // Дата/время сообщение ошибка подключения
		//$file = 'log.log'; // название файла в переменную
		file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
	$total = array('name' => "connect_db_error"); // сообщение ошибка соединения
	echo JSON_encode($total); // Переводим массив в JSON 	
	exit();	
	}
mysqli_set_charset($conn, "utf8"); // установка кодировки
////////////////////////////////////////////////////////////////////////////////////
// подготавливаем слова рандом и их возможное количество
////////////////////////////////////////////////////////////////////////////////////
//0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
//$strword = 6; // количество символов в рандом слове
//$allstring = "0123456789abcdefghijklmnopqrstuvwxyz"; // исходная сторка символов 
$alldata = pow(strlen($allstring), $strword); // общее число возможных комбинаций слов 
/////////////////////////////////////////////////////////////////////////////////////
// считаем количество записей в таблице
/////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT COUNT(*)FROM $table_name"; // формируем команду найти совпадение в БД
$res = mysqli_query($conn, $sql); // отправляем команду в БД
$row = mysqli_fetch_row($res);    // получаем ответ из БД, есть совпадение, или нет	
$countdata = $row[0]; // хранит количество строк в таблице
mysqli_free_result($res); // освобождает память, занятую результатами запроса	   	
//////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////
// если принимаем длинный и короткий
	if (isset($_POST["longURL"]) && isset($_POST["shortURL"]) && $_POST["longURL"] != $emptyvalue && $_POST["shortURL"] != $emptyvalue) 
	{ 
	$valuelong = $_POST["longURL"]; //Переменная хранит длинный элемент от клиента   
	$valueshort = $_POST["shortURL"];//Переменная хранит короткий элемент от клиента  
	//$trueword = 0; // переменная хранит 
		// проверка валидности URL, если ответ типа 200, то всё ОК 
			$g_head = @get_headers($valuelong); // получение массива заголовков ответа
			$statuscode = array(200,206,300,301,302,303,304,307); // массив безопасный ответов
			$getstatus = @substr($g_head[0],9,3); // получение статуса ответа сайта
			if (!@in_array($getstatus,$statuscode)) // если полученный ответ не совпадает со значениями безопасного массива, то 
			{
				$leftdata = $alldata - $countdata; // осталось свобных записей
				$log = date('Y-m-d H:i:s') . ' Введенный длинный URL ответил плохим HTTP-ответом: ' . $valuelong . ' Осталось свободных записей в БД: ' . $leftdata;
				file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
			$total = array('name' => "bad_longURL",
						   'longURL' => $valuelong,
						   'countdata' => $leftdata);
			echo JSON_encode($total); // Переводим массив в JSON 
			exit();
			} 
		///////////
		if (strlen($valueshort)==$strword)	// Если короткое значение длина strword символов, то
		{
			// цикл проверки слова по символам, перебираю каждый символ на корректность
			for ($i=0; $i<strlen($valueshort); $i++) // перебираем символы введенного слова
			{
				for ($j=0; $j<strlen($allstring); $j++) // сравниваем символ слова с исходной строкой символов
				{
					if ($valueshort{$i} == $allstring{$j}){ $trueword = $trueword + 1; } // количество символов, которые удовлетворяют выражению
				}
			}
			if ($trueword != strlen($valueshort)) // если количество символов, которые удовлетворяют выражению, не равно длине короткого URL, то
			{//не корректный короткий URL
			$leftdata = $alldata - $countdata; // осталось свобных записей
			$log = date('Y-m-d H:i:s') . ' Введен некорректный короткий URL: ' . $valueshort . ' Осталось свободных записей в БД: ' . $leftdata;
			file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
			$total = array('name' => "wrong_shortURL",
						   'shortURL' => $valueshort,
					       'countdata' => $leftdata); //оставлшееся количесво возможных записей
			echo JSON_encode($total); // Переводим массив в JSON 
			exit();
			}
		$sql = "SELECT * FROM `$table_name` WHERE `shortURL`='$valueshort'"; //формируем команду найти совпадение в БД
		$res = mysqli_query($conn, $sql); // отправляем команду в БД
		$row = mysqli_fetch_array($res); // получаем ответ из БД, есть совпадение, или нет
			if ($row == FALSE) //если совпадений по короткому нет , то
			{
			$sql = "SELECT * FROM `$table_name` WHERE `longURL`='$valuelong'"; //формируем команду найти совпадение в БД
			$res = mysqli_query($conn, $sql); // отправляем команду в БД
			$row = mysqli_fetch_array($res);
				if ($row == FALSE) //если совпадений по длинному нет, то
				{		
						if ($alldata - $countdata == 0) //если оставшееся количество записей для добавления равно нулю, то
						{
						$leftdata = $alldata - $countdata; // осталось свобных записей
						$log = date('Y-m-d H:i:s') . ' База данных переполнена. Свободных записей осталось: ' . $leftdata;
						file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
						$total = array('name' => "database_full", // база переполнена
						               'countdata' => $leftdata); // вывод оставлшееся количесво возможных записей
						echo JSON_encode($total); // Переводим массив в JSON 
						exit ();
						} 
				$timenow = time();
				$sql = "INSERT INTO `$table_name` (`longURL`,`shortURL`,`time`) VALUES ('$valuelong','$valueshort',$timenow)";// Запрос в БД, Добавляем запись в БД
				$res = mysqli_query($conn, $sql); // отправляем команду в БД
				$leftdata = $alldata - $countdata; // осталось свобных записей
				$leftdata = $alldata - $countdata - 1; // осталось свобных записей
				$log = date('Y-m-d H:i:s') . ' Успешно добавлен длинный: ' . $valuelong . ' Ему соотвествует короткий: ' . $valueshort . ' Свободных записей осталось: ' . $leftdata . ' Пара будет удалена: ' . date("Y-m-d H:i:s",$timenow + 1296000);
				file_put_contents($file, $log . PHP_EOL, FILE_APPEND);				
				$total = array('name' => "successfully_added",
							   'shortURL' => $valueshort,
						       'longURL' => $valuelong,
							   'countdata' => $leftdata, //оставлшееся количесво возможных записей
							   'timeout' => date("Y-m-d H:i:s",$timenow + 1296000)); // время жизни ссылки
				echo JSON_encode($total); // Переводим массив в JSON 		
				}	else	{ // else совпадение по длинному есть	
							//0-longURL
							//1-shortURL
							//2-time
							$leftdata = $alldata - $countdata; // осталось свобных записей
							$log = date('Y-m-d H:i:s') . ' Введен длинный URL, который уже есть в БД: ' . $valuelong . ' Ему соответствует короткий: ' . $row[1] . ' Пара будет удалена: ' . date("Y-m-d H:i:s",$row[2] + 1296000) . ' Свободных записей осталось: ' . $leftdata;
							file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
							$total = array('name' => "match_found",
									       'shortURL' => $row[1],
										   'longURL' => $valuelong,
										   'countdata' => $leftdata,
										   'timeout' => date("Y-m-d H:i:s",$row[2] + 1296000)); //Формируем массив для JSON ответа
							echo JSON_encode($total); // Переводим массив в JSON 
							}  
			}	else	{// совпадение по короткому есть
						$leftdata = $alldata - $countdata; // осталось свобных записей
						$log = date('Y-m-d H:i:s') . ' Введенный короткий URL уже есть в БД: ' . $valueshort . ' Свободных записей осталось: ' . $leftdata;
						file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
						$total = array('name' => "enter_match_found",
						               'shortURL' => $valueshort,
									   'countdata' => $leftdata); //Формируем массив для JSON ответа
						echo JSON_encode($total); // Переводим массив в JSON 
						}
		} else // длина символов короткого не равна $strword
			{
			$leftdata = $alldata - $countdata; // осталось свобных записей
			$log = date('Y-m-d H:i:s') . ' Введен некорректный короткий URL: ' . $valueshort . ' Осталось свободных записей в БД: ' . $leftdata;
			file_put_contents($file, $log . PHP_EOL, FILE_APPEND);				
			$total = array('name' => "wrong_shortURL",
						   'shortURL' => $valueshort,
					       'countdata' => $leftdata); //оставлшееся количесво возможных записей
			echo JSON_encode($total); // Переводим массив в JSON 
			}						
	}
//////////////////////////////////////////////////////////////////////////////////////
//если принимаем длинный
	if (isset($_POST["longURL"]) && isset($_POST["shortURL"]) && $_POST["longURL"] != $emptyvalue && $_POST["shortURL"] == $emptyvalue) // Если значение  не пустое, то
		{	
		$valuelong = $_POST["longURL"]; //Переменная хранит длинный элемент от клиента   
		// проверка валидности URL, если ответ типа 200, то всё ОК 
			$g_head = @get_headers($valuelong); // получение массива заголовков ответа
			$statuscode = array(200,206,300,301,302,303,304,307); // массив безопасный ответов
			if (!@in_array((substr($g_head[0],9,3)),$statuscode)) // если полученный ответ не мовпадает со значениями безопасного массива, то
			{
				$leftdata = $alldata - $countdata; // осталось свобных записей
				$log = date('Y-m-d H:i:s') . ' Введенный длинный URL ответил плохим HTTP-ответом: ' . $valuelong . ' Осталось свободных записей в БД: ' . $leftdata;
				file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
				$total = array('name' => "bad_longURL",
							   'longURL' => $valuelong,
							   'countdata' => $leftdata);
				echo JSON_encode($total); // Переводим массив в JSON 
				exit();
			} 
		/////////////////
		$sql = "SELECT * FROM `$table_name` WHERE `longURL`='$valuelong'"; //формируем команду найти совпадение в БД
		$res = mysqli_query($conn, $sql); // отправляем команду в БД
		$row = mysqli_fetch_array($res); // получаем ответ из БД, есть совпадение, или нет
		if ($row == FALSE) //если совпадения нет , то
			{	
					if ($alldata - $countdata == 0) //если оставшееся количество записей для добавления равно нулю, то
					{
					$leftdata = $alldata - $countdata; // осталось свобных записей
					$log = date('Y-m-d H:i:s') . ' База данных переполнена. Свободных записей осталось: ' . $leftdata;
					file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
					$total = array('name' => "database_full", // база переполнена
					               'countdata' => $leftdata); // вывод оставлшееся количесво возможных записей
					echo JSON_encode($total); // Переводим массив в JSON 
					exit ();
					}
			////////////////
				if ($alldata - $countdata > 0)
				{
				a:   // ссылка на место	
				$myshortaddress = ""; // НЕ ПЕРЕНОСИТЬ!!! переменная будет хранить рандом URL, цикл инкремент будет прибавлять по символу 
				for ($i = 0; $i < $strword; $i++)
					{ 
					$some_num = random_int(0,strlen($allstring)-1);
					$myshortaddress .= $allstring[$some_num]; //myshortaddress хранит рандомное слово из 6 символов+канкотенация
					}	
				$sql = "SELECT * FROM `$table_name` WHERE `shortURL`='$myshortaddress'"; //формируем команду найти совпадение в БД
				$res = mysqli_query($conn, $sql); // отправляем команду в БД
				$row = mysqli_fetch_array($res);
				if ($row != FALSE) goto a; //если совпадение найдено, то снова запускаем функцию рандом 
				$timenow = time();
				$sql = "INSERT INTO `$table_name` (`longURL`,`shortURL`,`time`) VALUES ('$valuelong','$myshortaddress',$timenow)";// Запрос в БД, Добавляем запись в БД
				$res = mysqli_query($conn, $sql); // отправляем команду в БД
				$leftdata = $alldata - $countdata - 1; // осталось свобных записей
				$log = date('Y-m-d H:i:s') . ' Успешно добавлен длинный: ' . $valuelong . ' Ему соотвествует короткий: ' . $myshortaddress . ' Свободных записей осталось: ' . $leftdata . ' Пара будет удалена: ' . date("Y-m-d H:i:s",$timenow + 1296000);
				file_put_contents($file, $log . PHP_EOL, FILE_APPEND);				
				$total = array('name' => "successfully_added",
							   'shortURL' => $myshortaddress,
							   'longURL' => $valuelong,
							   'countdata' => $leftdata,
							   'timeout' => date("Y-m-d H:i:s",$timenow + 1296000)); //Формируем массив для JSON ответа///////
				echo JSON_encode($total); // Переводим массив в JSON 				
				}else 	{ // база переполнена
						$leftdata = $alldata - $countdata; // осталось свобных записей
						$log = date('Y-m-d H:i:s') . ' База данных переполнена. Свободных записей осталось: ' . $leftdata;
						file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
						$total = array('name' => "database_full",
									   'countdata' => $leftdata); //Формируем массив для JSON ответа
						echo JSON_encode($total); // Переводим массив в JSON 
						}
			}else 
				{
				//0-longURL
				//1-shortURL
				//2-time
				$leftdata = $alldata - $countdata; // осталось свобных записей
				$log = date('Y-m-d H:i:s') . ' Введен длинный URL, который уже есть в БД: ' . $valuelong . ' Ему соответствует короткий: ' . $row[1] . ' Пара будет удалена: ' . date("Y-m-d H:i:s",$row[2] + 1296000) . ' Свободных записей осталось: ' . $leftdata;
				file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
				$total = array('name' => "match_found",
							   'shortURL' => $row[1],
							   'longURL' => $valuelong,
							   'countdata' => $leftdata,
							   'timeout' => date("Y-m-d H:i:s",$row[2] + 1296000)); //Формируем массив для JSON ответа
				echo JSON_encode($total); // Переводим массив в JSON 
				}  
		}
//////////////////////////////////////////////////////////////////////////////////////		
// если ничего не было введено		
	if (isset($_POST["longURL"]) && isset($_POST["shortURL"]) && $_POST["longURL"] == $emptyvalue && $_POST["shortURL"] == $emptyvalue) // Если значения пустые, то
		{
		$leftdata = $alldata - $countdata; // осталось свобных записей
		//$log = date('Y-m-d H:i:s') . ' Пожалуйста, введите URL в верхнее поле. Свободных записей осталось: ' . $leftdata;
		//file_put_contents($file, $log . PHP_EOL, FILE_APPEND); // запись в лог
		$total = array('name' => "empty_request",
					   'countdata' => $leftdata); //Формируем массив для JSON ответа
		echo JSON_encode($total); // Переводим массив в JSON 	
		}
//////////////////////////////////////////////////////////////////////////////////////
// если введен только короткий
	if (isset($_POST["longURL"]) && isset($_POST["shortURL"]) && $_POST["longURL"] == $emptyvalue && $_POST["shortURL"] != $emptyvalue) // Если значения пустые, то
		{
		$valueshort = $_POST["shortURL"];//Переменная хранит короткий элемент от клиента  	
		$leftdata = $alldata - $countdata; // осталось свобных записей
		$log = date('Y-m-d H:i:s') . ' Вы ввели только желаемый короткий URL: ' . $valueshort .' Пожалуйста, введите URL в верхнее поле. Свободных записей осталось: ' . $leftdata;
		file_put_contents($file, $log . PHP_EOL, FILE_APPEND);
		$total = array('name' => "empty_request",
					   'countdata' => $leftdata); //Формируем массив для JSON ответа
		echo JSON_encode($total); // Переводим массив в JSON 	
		}
//////////////////////////////////////////////////////////////////////////////////////
// закрытие сеанса с БД
mysqli_close($conn); // Закрываем соединеие с БД
?> 
