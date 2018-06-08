<?php

	session_start();

	$connect = new mysqli("localhost", "p422593_sc", "2T6x3H9z", "p422593_sc");

	define("SESSION_KEY_HASH", "hash_visit");
	define("STAT_OUTPUT_FILENAME", "stat.xls");
	define("TABLE_NAME", "userdatarucksack");
	define("TABLE_NAME_POPUP", "popupinforucksack");
	define("DAY", 60 * 60 * 24);

	$TITLE_POPUPS = [
		"ouibounce-modal-A" => "СТАНЬТЕ ОБЛАДАТЕЛЕМ РЮКЗАКА SWISSGEAR СО СКИДКОЙ!",
		"ouibounce-modal-B" => "СТАНЬТЕ ОБЛАДАТЕЛЕМ РЮКЗАКА SWISSGEAR, ТАК ЖЕ КАК ЭТО СДЕЛАЛИ 10.000 КЛИЕНТОВ ПО РФ И СНГ"
	];

	if (!$connect || $connect->connect_errno) {
		print "Connect error";
		exit;
	}

	$delay = (int) $_REQUEST["delay"];

	switch ($_REQUEST["act"]) {

		case "order":
			$hash = $_SESSION[SESSION_KEY_HASH];
			$sql = sprintf("UPDATE `" . TABLE_NAME . "` SET `afterTime` = '%d', `countOrder` = 1,`tel`='%s' WHERE `hash` = '%s'", $delay, addslashes($_REQUEST["phone"]), $hash);
			$connect->query($sql);
			$connect->close();

			/*$data = [
				"customer_api_key" => "e63220bb-6933-499c-bca7-43e87f7cc622",
				"orders" => [[
					"customer" => [
						"given_name" => $_REQUEST["name"],
						"surname" => "",
						"patronymic" => "",
						"country" => "",
						"state" => "",
						"city" => "",
						"zip" => "",
						"address" => $_REQUEST["address"],
						"email" => $_REQUEST["email"],
						"phone" => $_REQUEST["phone"]
					],
					"credentials" => [
						"created" => date("Y-m-d H:i:s"),
						"ip" => $_SERVER["REMOTE_ADDR"],
						"referer" => $_SERVER["HTTP_REFERER"]
					],
					"basket" => [
						["good_id" => $_REQUEST["tovar_id"], "cost" => 1990, "quantity" => 1]
					],
					"additional_fields" => [
						["key" => "roistat_visit", "value" => str_replace("roistat_visit:", "", $_REQUEST["additional_field1"])]
					]
				]]
			];

			$d = json_encode($data, JSON_UNESCAPED_UNICODE);

			$ch = curl_init("https://api.e-autopay.com/v02/6b02ada2-56ce-4e01-adf9-233f9913a998/orders");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
			$res = curl_exec($ch);
			curl_close($ch);

			$res = json_decode($res);
var_dump($res);
			$orderId = (int) $res["orders"][0]["order_id"];
var_dump($orderId);*/



			$handle = curl_init("https://borisovden.e-autopay.com/checkout/save_order_data.php");
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($handle, CURLOPT_TIMEOUT, 60);
			//curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($handle, CURLOPT_AUTOREFERER, false);
			curl_setopt($handle, CURLOPT_HEADER, true);
			curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($handle, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0");
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, $_REQUEST);
			curl_setopt($handle, CURLOPT_REFERER, $_SERVER["HTTP_REFERER"]);

			$response = curl_exec($handle);
			curl_close($handle);

			preg_match_all("/Location: complete\?oid=([A-Za-z0-9]+)\\r\\n/imu", $response, $res);
			$oid = $res[1][0];



			$handle = curl_init("https://borisovden.e-autopay.com/checkout/complete?oid=" . $oid);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($handle, CURLOPT_TIMEOUT, 60);
			//curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($handle, CURLOPT_AUTOREFERER, false);
			curl_setopt($handle, CURLOPT_HEADER, true);
			curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($handle, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0");
			curl_setopt($handle, CURLOPT_REFERER, $_SERVER["HTTP_REFERER"]);

			$response = curl_exec($handle);
			curl_close($handle);











			$_REQUEST["oid"] = $oid;
?>
<form action="https://borisovden.e-autopay.com/ordering/do_order.php" method="post" id="s">
<? foreach ($_REQUEST as $k => $v) { ?>
<input type="hidden" name="<?=$k;?>" value="<?=$v;?>" />
<? } ?>
</form>
<script type="text/javascript">document.getElementById("s").submit();</script>
<?
			exit;
			break;

		/**
		 * Сохранение информации о посещении или оформлении заказа
		 */
		case "put":
			// время
			$delay = (int) $_REQUEST["delay"];

			// Состояние: 0 - просто заход, 1 - оформление заказа, 2 - выход
			$state = (int) $_REQUEST["state"];

			// Текущее время у пользователя
			$now = (int) $_REQUEST["now"];

			// UTM
			$source = addslashes(trim($_REQUEST["source"]));
			$medium = addslashes(trim($_REQUEST["medium"]));
			$campaign = addslashes(trim($_REQUEST["campaign"]));
			$content = addslashes(trim($_REQUEST["content"]));
			$term = addslashes(trim($_REQUEST["term"]));
			$tel = addslashes(trim($_REQUEST["phone"]));

			switch ($state) {

				// Если это просто просмотр, то генерируем уникальную строку и добавляем данные о просмотре в БД
				case 0:
					$hash = md5($_SERVER["REMOTE_ADDR"] . time());
					$time = time();
					$weekday = date("N");
					$ip = $_SERVER["REMOTE_ADDR"];
					$geo = json_decode(file_get_contents(sprintf("http://ip-api.com/json/%s?fields=country,regionName,city&lang=ru", $ip)));

					$region = join(", ", [$geo->country, $geo->regionName, $geo->city]);

					$_SESSION[SESSION_KEY_HASH] = $hash;

					$sql = sprintf("INSERT INTO `" . TABLE_NAME . "` (`hash`, `datemsk`, `dateusr`, `weekday`, `countViews`, `source`, `region`, `medium`, `campaign`, `content`, `term`) VALUES ('%s', '%d', '%d', '%d', 1, '%s', '%s', '%s', '%s', '%s', '%s')", $hash, $time, $now, $weekday, $source, $region, $medium, $campaign, $content, $term);
					break;

				// Если это оформление заказа
				case 1:
					$hash = $_SESSION[SESSION_KEY_HASH];
					$sql = sprintf("UPDATE `" . TABLE_NAME . "` SET `afterTime` = '%d', `countOrder` = 1, `tel` = '%s' WHERE `hash` = '%s'", $delay, $tel, $hash);
					break;

				// Если юзер ушел с сайта
				case 2:
					$hash = $_SESSION[SESSION_KEY_HASH];
					$sql = sprintf("UPDATE `" . TABLE_NAME . "` SET `exitTime` = '%d' WHERE `hash` = '%s'", $delay, $hash);
					break;
			}

			$connect->query($sql);
			print json_encode(["result" => true]);
			exit;
			break;

		/**
		 * Сохранение информации о popup-ах
		 */
		case "putPopup":
			// заголовок
			$title = addslashes($_REQUEST["title"]);

			// Состояние: 0 - открыт попап, 1 - оформление заказа по попапу
			$state = (int) $_REQUEST["state"];

			$source = addslashes(trim($_REQUEST["source"]));

			if (!$state) {
				$sql = sprintf("INSERT INTO `" . TABLE_NAME_POPUP . "` (`hash`, `datemsk`, `name`, `source`) VALUES ('%s', '%d', '%s', '%s')", $_SESSION[SESSION_KEY_HASH], time(), $title, $source);
			} else {
				$sql = sprintf("UPDATE `" . TABLE_NAME_POPUP . "` SET `isOrdered` = 1 WHERE `hash` = '%s'", $_SESSION[SESSION_KEY_HASH]);
			}

			$connect->query($sql);
			print json_encode(["result" => true]);
			break;

		case "do-export":
			$period = (boolean) $_REQUEST["period"];

			$condition = "";

			if ($period) {
				$since = parseDateFromDatepicker("since");
				$until = parseDateFromDatepicker("until") + DAY;
				$condition = sprintf(" WHERE `datemsk` > '%d' AND `datemsk` <= '%d'", $since, $until);
			}

			$items = [];

			$query = $connect->query("SELECT `datemsk`,`dateusr`,`weekday`,`source`,`afterTime`,`region`,`countViews`,`countOrder`,`exitTime`,`medium`,`campaign`,`content`,`term`,`tel` FROM `" . TABLE_NAME . "`" . $condition . " ORDER BY `id` ASC");

			$weekdays = ["ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ", "ВС"];
			$header = [];

			$p = $_REQUEST["field"];

			$header = [];
			$p["date"] && ($header[] = "Дата");
			$p["weekday"] && ($header[] = "День недели");
			$p["countViews"] && ($header[] = "Переходы");
			$p["countOrder"] && ($header[] = "Заказы");
			$p["source"] && ($header[] = "Источник кампании utm_source");
			$p["medium"] && ($header[] = "Тип трафика utm_medium");
			$p["campaign"] && ($header[] = "Название кампании utm_campaign");
			$p["content"] && ($header[] = "Идентификтор объявления utm_content");
			$p["term"] && ($header[] = "Ключевое слово utm_term");
			$p["datemsk"] && ($header[] = "Время по МСК");
			$p["dateusr"] && ($header[] = "Местное время клиента");
			$p["afterTime"] && ($header[] = "Время заказа (сек.)");
			$p["exitTime"] && ($header[] = "Время выхода (сек.)");
			$p["region"] && ($header[] = "Регион");
			$p["tel"] && ($header[] = "Телефон");

			include_once "php/php-excel.php";
			$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
			$exporter->initialize();
			$exporter->addRow($header);

			while ($row = $query->fetch_array()) {
				$r = [];

				$p["date"] && ($r[] = date("d.m.Y", $row["datemsk"]));
				$p["weekday"] && ($r[] = $weekdays[$row["weekday"] - 1]);
				$p["countViews"] && ($r[] = $row["countViews"]);
				$p["countOrder"] && ($r[] = $row["countOrder"]);
				$p["source"] && ($r[] = $row["source"]);
				$p["medium"] && ($r[] = $row["medium"]);
				$p["campaign"] && ($r[] = $row["campaign"]);
				$p["content"] && ($r[] = $row["content"]);
				$p["term"] && ($r[] = $row["term"]);
				$p["datemsk"] && ($r[] = date("H:i", $row["datemsk"]));
				$p["dateusr"] && ($r[] = date("H:i", $row["dateusr"]));
				$p["afterTime"] && ($r[] = $row["afterTime"] ? $row["afterTime"] : "");
				$p["exitTime"] && ($r[] = $row["exitTime"]);
				$p["region"] && ($r[] = $row["region"]);
				$p["tel"] && ($r[] = $row["tel"]);
				$exporter->addRow($r);
			}

			$exporter->finalize();
			exit;
			break;

		case "do-report":
			$report = (int) $_REQUEST["report"];
			$period = (int) $_REQUEST["period"];

			$timeRanges = [
				[0, 5],
				[5, 10],
				[10, 20],
				[20, 30],
				[30, 40],
				[40, 50],
				[50, 60],
				[60, 90],
				[90, 120],
				[120, 180],
				[180, 300],
				[300, 600],
				[600, 900],
				[900, 1200],
				[1200, 1800],
				[1800, 3600],
				[1800, 3600],
				[3600, 7200],
				[7200, 10800],
				[10800, 86400]
			];

			$getIndexForTimeRange = function($time) use ($timeRanges) {
				foreach ($timeRanges as $index => $n) {
					if ($n[0] <= $time && $time < $n[1]) {
						return $index;
					}
				}
				return -1;
			};

			$condition = "1 = 1";

				$since = parseDateFromDatepicker("since");
				$until = parseDateFromDatepicker("until") + DAY;
				$condition = sprintf("`datemsk` > '%d' AND `datemsk` <= '%d'", $since, $until);


			include_once "php/php-excel.php";
			switch ($report) {
				case 1:
					$items = [];
					$query = $connect->query($sql = "SELECT `dateusr`,`countOrder` FROM `" . TABLE_NAME . "` WHERE " . $condition);
					$views = [];
					$orders = [];

					while ($item = $query->fetch_array()) {
						$h = (int) date("G", $item["dateusr"]);
						$views[$h]++;
						$orders[$h] += $item["countOrder"];
					}

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["Период", "Переходы", "Заказы", "Конверсия, %", "Отказы, %"]);

					for ($i = 0; $i < 24; ++$i) {
						$v = isset($views[$i]) ? $views[$i] : 0;
						$o = isset($orders[$i]) ? $orders[$i] : 0;
						$k = $v ? ($o / $v) * 100 : 0;
						$exporter->addRow([
							sprintf("%d:00-%d:00", $i, $i + 1),
							$v,
							$o,
							round($k, 2),
							round(100 - $k, 2)
						]);
					}

					$exporter->finalize();
					break;

				case 2:
					$items = [];
					$query = $connect->query("SELECT SUM(`countViews`),SUM(`countOrder`),`weekday` FROM `" . TABLE_NAME . "` WHERE " . $condition . " GROUP BY `weekday` ORDER BY `weekday`");

					$data = [];

					while ($item = $query->fetch_array()) {
						$data[$item["weekday"] - 1] = [$item["SUM(`countViews`)"], $item["SUM(`countOrder`)"]];
					}

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["День недели", "Переходы", "Заказы", "Конверсия, %", "Отказы, %"]);

					$days = ["ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ", "ВС"];

					for ($i = 0; $i < 7; ++$i) {
						$v = isset($data[$i]) ? $data[$i][0] : 0;
						$o = isset($data[$i]) ? $data[$i][1] : 0;
						$k = $v ? ($o / $v) * 100 : 0;
						$exporter->addRow([
							$days[$i],
							$v,
							$o,
							$k,
							100 - $k
						]);
					}

					$exporter->finalize();
					break;
				case 3:
					$query = $connect->query("SELECT SUM(`countOrder`) AS `count`,`afterTime` FROM `" . TABLE_NAME . "` WHERE `countOrder` > 0 AND " . $condition . " GROUP BY `afterTime`");

					$data = [];

					while ($row = $query->fetch_array()) {
						$index = $getIndexForTimeRange($row["afterTime"]);
						$data[$index] += $row["count"];
					}

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["Интервал времени оформления заказа", "Количество заказов", "% от общего числа заказов"]);

					$sum = array_sum($data);

					foreach ($timeRanges as $index => $n) {
						$v = isset($data[$index]) ? $data[$index] : 0;
						$exporter->addRow([
							sprintf("%d-%d сек", $n[0], $n[1]),
							$v,
							round(($v / $sum) * 100, 2)
						]);
					}

					$exporter->finalize();
					break;


				case 4:
					$query = $connect->query("SELECT COUNT(*) AS `count`,`exitTime` FROM `" . TABLE_NAME . "` WHERE `countOrder` = 0 AND " . $condition . " GROUP BY `exitTime`");

					$data = [];

					while ($row = $query->fetch_array()) {
						$index = $getIndexForTimeRange($row["exitTime"]);
						$data[$index] += $row["count"];
					}

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["Интервал времени ухода с сайта", "Количество уходов", "% от общего числа уходов"]);

					$sum = array_sum($data);

					foreach ($timeRanges as $index => $n) {
						$v = isset($data[$index]) ? $data[$index] : 0;
						$exporter->addRow([
							sprintf("%d-%d сек", $n[0], $n[1]),
							$v,
							round(($v / $sum) * 100, 2)
						]);
					}

					$exporter->finalize();
					break;

				case 5:
					$items = [];
					$query = $connect->query("SELECT SUM(`countViews`) AS `c`, SUM(`countOrder`) AS `o`, `campaign` FROM `" . TABLE_NAME . "` WHERE " . $condition . " AND `campaign` <> '' GROUP BY `campaign`");

					$data = [];
					while($item = $query->fetch_array()) {
						$data[] = [$item["c"], $item["o"], $item["campaign"]];
					}


					$campaigns = [];
					foreach ($data as $index => $item) {
						list($views, $orders, $campaign) = $item;

						$campaigns[$campaign][2] = $campaign;
						$campaigns[$campaign][1] += $orders;
						$campaigns[$campaign][0] += $views;
					}

					asort($campaigns);

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["Название кампании", "Переходы", "Заказы", "Конверсия, %", "Отказы, %"]);

					$campaigns = array_values($campaigns);

					foreach ($campaigns as $campaign) {
						$c = $campaign[2];
						$v = (int) $campaign[0];
						$o = (int) $campaign[1];
						$k = round($v ? ($o / $v) * 100 : 0, 2);
						$exporter->addRow([
							$c,
							$v,
							$o,
							$k,
							100 - $k
						]);
					}

					$exporter->finalize();
					break;

				case 6: // utm_content
					$items = [];
					$query = $connect->query("SELECT SUM(`countViews`),SUM(`countOrder`), `campaign`, `content` FROM `" . TABLE_NAME . "` WHERE " . $condition . " GROUP BY `campaign` ORDER BY `campaign`");

					$data = [];
					while($item = $query->fetch_array()) {
						$data[] = [$item["SUM(`countViews`)"], $item["SUM(`countOrder`)"], $item['campaign'], $item['content']];
					}
					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["Название кампании utm_campaign", "Переходы", "Заказы", "Конверсия, %", "Отказы, %"]);


					for ($i = 0, $l = sizeof($data); $i < $l; ++$i) {
						$c = $data[$i][2];
						$v = isset($data[$i]) ? $data[$i][0] : 0;
						$o = isset($data[$i]) ? $data[$i][1] : 0;
						$k = $v ? ($o / $v) * 100 : 0;
						$exporter->addRow([
							$c,
							$v,
							$o,
							$k,
							100 - $k
						]);
					}

					$exporter->addRow(); // пробел между таблицами
					$exporter->addRow(); // пробел между таблицами

					$exporter->addRow(["Идентификатор объявления utm_content", "Переходы", "Заказы", "Конверсия, %", "Отказы, %"]);

					for ($i = 0, $l = sizeof($data); $i < $l; ++$i) {
						$c = $data[$i][3];
						$v = isset($data[$i]) ? $data[$i][0] : 0;
						$o = isset($data[$i]) ? $data[$i][1] : 0;
						$k = $v ? ($o / $v) * 100 : 0;
						$exporter->addRow([
							$c,
							$v,
							$o,
							$k,
							100 - $k
						]);
					}
					$exporter->finalize();
					break;
				case 7:
					$items = [];
					$query = $connect->query("SELECT SUM(`countViews`), SUM(`countOrder`), `term` FROM `" . TABLE_NAME . "` WHERE " . $condition . " AND `term` <> 0 GROUP BY `term`");

					$data = [];
					while($item = $query->fetch_array()) {
						$data[] = [$item["SUM(`countViews`)"], $item["SUM(`countOrder`)"], explode(".",$item['term'])[2]];
					}


					$regions = [];
					foreach ($data as $index => $item) {
						list($views, $orders, $region) = $item;

						$regions[$region][2] = $region;
						$regions[$region][1] += $orders;
						$regions[$region][0] += $views;
					}

					asort($regions);

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["Возраст", "Переходы", "Заказы", "Конверсия, %", "Отказы, %"]);

					$regions = array_values($regions);

					foreach ($regions as $region) {
						$c = $region[2];
						$v = (int) $region[0];
						$o = (int) $region[1];
						$k = round($v ? ($o / $v) * 100 : 0, 2);
						$exporter->addRow([
							$c,
							$v,
							$o,
							$k,
							100 - $k
						]);
					}

					$exporter->finalize();
					break;
				case 8:
					$query = $connect->query("SELECT `isOrdered`,`source`,`name` FROM `" . TABLE_NAME_POPUP . "` WHERE " . $condition);

					$data = [];

					while ($row = $query->fetch_array()) {
						$data[$row["source"]][$row["name"]][0]++;
						$data[$row["source"]][$row["name"]][1] += $row["isOrdered"];
					}

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["Источник", "Просмотры", "Заказы", "Конверсия, %", "Отказы, %", "Название"]);

					foreach ($data as $source => $names) {
						foreach ($names as $title => $item) {
							list($views, $orders) = $item;
							$conv = ($orders / $views) * 100;
							$exporter->addRow([
								$source,
								$views,
								$orders,
								round($conv, 2),
								round(100 - $conv, 2),
								$TITLE_POPUPS[$title]
							]);
						}
					}

					$exporter->finalize();
					break;

				case 9:
					$items = [];
					$query = $connect->query("SELECT SUM(`countViews`),SUM(`countOrder`), `region` FROM `" . TABLE_NAME . "` WHERE " . $condition . " GROUP BY `region`");

					$data = [];
					while($item = $query->fetch_array()) {
						$data[] = [$item["SUM(`countViews`)"], $item["SUM(`countOrder`)"], (explode(",",$item['region'])[1] . ", ". explode(",",$item['region'])[2])];
					}

					$regions = [];
					foreach ($data as $index => $item) {
						list($views, $orders, $region) = $item;

						$regions[$region][2] = $region;
						$regions[$region][1] += $orders;
						$regions[$region][0] += $views;
					}

					$exporter = new ExportDataExcel('browser', STAT_OUTPUT_FILENAME);
					$exporter->initialize();
					$exporter->addRow(["ГЕО", "Переходы", "Заказы", "Конверсия, %", "Отказы, %"]);

					$regions = array_values($regions);

					foreach ($regions as $region) {
						$c = $region[2];
						$v = (int) $region[0];
						$o = (int) $region[1];
						$k = round($v ? ($o / $v) * 100 : 0, 2);
						$exporter->addRow([
							$c,
							$v,
							$o,
							$k,
							100 - $k
						]);
					}

					$exporter->finalize();
					break;

			}

			break;

		case "export":
			header("Content-type: text/html; charset=utf-8");
?>
<style type="text/css">
	label {
		display: block;
	}

	select {
		background: transparent;
		border: none;
		border-bottom: 1px solid black;
		height: 30px;
		padding: 0 10px;
	}

	input[type=radio], input[type=checkbox] {
		vertical-align: middle;
	}

</style>
<fieldset>
	<legend>Выгрузка данных</legend>
	<form action="?act=do-export" method="post">
		<p>Период:</p>
		<label><input type="radio" name="period" value="0" checked /> за всё время</label>
		<label><input type="radio" name="period" value="1" /> за произвольный период: <?=datepicker(time() - 10 * DAY, "since");?> до <?=datepicker(time(), "until");?></label>
		<hr />
		<p>Поля:</p>
		<label><input type="checkbox" name="field[date]" value="1" /> дата</label>
		<label><input type="checkbox" name="field[weekday]" value="1" /> день недели</label>
		<label><input type="checkbox" name="field[countViews]" value="1" /> переходы</label>
		<label><input type="checkbox" name="field[countOrder]" value="1" /> заказы</label>
		<label><input type="checkbox" name="field[source]" value="1" /> источник кампании utm_source</label>
		<label><input type="checkbox" name="field[medium]" value="1" /> тип трафика utm_medium</label>
		<label><input type="checkbox" name="field[campaign]" value="1" /> название кампании utm_campaign</label>
		<label><input type="checkbox" name="field[content]" value="1" /> идентификатор объявления utm_content</label>
		<label><input type="checkbox" name="field[term]" value="1" /> ключевое слово utm_term</label>
		<label><input type="checkbox" name="field[datemsk]" value="1" /> время по мск</label>
		<label><input type="checkbox" name="field[dateusr]" value="1" /> местное время клиента</label>
		<label><input type="checkbox" name="field[afterTime]" value="1" /> время заказа</label>
		<label><input type="checkbox" name="field[exitTime]" value="1" /> время выхода</label>
		<label><input type="checkbox" name="field[region]" value="1" /> регион</label>
		<label><input type="checkbox" name="field[tel]" value="1" /> телефон (временно)</label>
		<input type="submit" value="Выгрузить" />
	</form>
</fieldset>
<fieldset>
	<legend>Отчеты</legend>
	<form action="?act=do-report" method="post">
		<p>Хочу <input type="submit" value="получить" /> статистику <select name="report">
			<option value="1">по часам</option>
			<option value="2">по дням недели</option>
			<option value="3">по времени заказа/количеству заказов</option>
			<option value="4">по времени ухода/количеству уходов</option>
			<option value="5">по кампаниям</option>
			<option value="6">по объявлениям</option>
			<option value="7">по возрасту</option>
			<option value="9">по регионам</option>
			<option value="8">по popup'ам</option>
		</select>, по данным с <?=datepicker(time() - 10 * DAY, "since");?> до <?=datepicker(time(), "until");?></p>
	</form>
</fieldset>

<?
			break;

		case "info":
			phpinfo();
			break;

	}

	function datepicker($date = false, $prefix = "") {
		if (!$date) {
			$date = time();
		}
		list($cd, $cm, $cy) = explode(" ", date("d m Y", $date));
		$d = []; $m = []; $y = [];
		$d[(int) $cd] = " selected"; $m[(int) $cm] = " selected"; $y[$cy] = " selected";
?>
<select name="<?=$prefix;?>_day">
<? for($i = 1; $i <= 31; ++$i) { ?><option value="<?=$i;?>"<?=$d[$i];?>><?=$i;?></option><? } ?>
</select>.<select name="<?=$prefix;?>_month">
<? for($i = 1; $i <= 12; ++$i) { ?><option value="<?=$i;?>"<?=$m[$i];?>><?=$i;?></option><? } ?>
</select>.<select name="<?=$prefix;?>_year">
<? for($i = 2017; $i <= 2017; ++$i) { ?><option value="<?=$i;?>"<?=$y[$i];?>><?=$i;?></option><? } ?>
</select>
<?

	}

	function parseDateFromDatepicker($prefix) {
		$d = $_REQUEST[$prefix . "_day"];
		$m = $_REQUEST[$prefix . "_month"];
		$y = $_REQUEST[$prefix . "_year"];
		return mktime(0, 0, 0, $m, $d, $y);
	}