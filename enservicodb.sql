-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Окт 11 2024 г., 12:07
-- Версия сервера: 8.0.30
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `enservicodb`
--

-- --------------------------------------------------------

--
-- Структура таблицы `lic_id`
--

CREATE TABLE `lic_id` (
  `id` int NOT NULL,
  `num_lic` varchar(50) DEFAULT NULL COMMENT 'Лицензия',
  `text_lic` varchar(255) DEFAULT NULL COMMENT 'Состояние лицензии',
  `the_end_user` varchar(255) DEFAULT NULL COMMENT 'Конечник',
  `object` varchar(255) DEFAULT NULL COMMENT 'Объект установки - ПС или Диспетчерская',
  `actual` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `lic_param`
--

CREATE TABLE `lic_param` (
  `id` int NOT NULL COMMENT 'ид строки',
  `lic_id` int NOT NULL COMMENT 'ид лицензии',
  `volume_parameters` varchar(50) DEFAULT NULL COMMENT 'Телепараметры',
  `users` varchar(50) DEFAULT NULL COMMENT 'Количество станций',
  `platform` varchar(100) DEFAULT NULL COMMENT 'Платформа',
  `date_creation` date DEFAULT NULL COMMENT 'Дата создания лицензии',
  `date_sub_upgrade` date DEFAULT NULL COMMENT 'Дата разрешенного обновления до',
  `date_latest_update` datetime DEFAULT NULL COMMENT 'Дата обновления',
  `actual` int DEFAULT '0',
  `rec_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `max_soft_version` varchar(10) DEFAULT NULL COMMENT 'Максимальная версия ПО',
  `client` varchar(10) DEFAULT NULL COMMENT 'Клиент',
  `client10` varchar(10) DEFAULT NULL COMMENT 'Клиент10',
  `web_client10` varchar(10) DEFAULT NULL COMMENT 'Веб-клиент10',
  `protocol_support` varchar(10) DEFAULT NULL COMMENT 'Поддержка протокола МЭК',
  `opc_support` varchar(10) DEFAULT NULL COMMENT 'Поддержка OPC DA2.0',
  `web_client_support` varchar(10) DEFAULT NULL COMMENT 'Поддержка Веб-клиента',
  `exchange_protocol_dnp3` varchar(10) DEFAULT NULL COMMENT 'Протокол обмена DNP3',
  `security_level` varchar(50) DEFAULT NULL COMMENT 'Уровень безопасности'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Структура таблицы `lic_record`
--

CREATE TABLE `lic_record` (
  `id` int NOT NULL,
  `record_name` varchar(50) DEFAULT NULL COMMENT 'Номер договора',
  `record_link` varchar(255) DEFAULT NULL COMMENT 'Путь к задаче',
  `name_count` varchar(255) DEFAULT NULL COMMENT 'Контрагент',
  `date_insert` datetime DEFAULT NULL COMMENT 'Дата добавления'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `lic_view_actual_lic_id`
-- (См. Ниже фактическое представление)
--
CREATE TABLE `lic_view_actual_lic_id` (
`actual` int
,`id` int
,`num_lic` varchar(50)
,`object` varchar(255)
,`text_lic` varchar(255)
,`the_end_user` varchar(255)
);

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `lic_view_actual_param`
-- (См. Ниже фактическое представление)
--
CREATE TABLE `lic_view_actual_param` (
`actual` int
,`client` varchar(10)
,`client10` varchar(10)
,`date_creation` date
,`date_latest_update` datetime
,`date_sub_upgrade` date
,`exchange_protocol_dnp3` varchar(10)
,`id` int
,`lic_id` int
,`max_soft_version` varchar(10)
,`opc_support` varchar(10)
,`platform` varchar(100)
,`protocol_support` varchar(10)
,`rec_date` timestamp
,`security_level` varchar(50)
,`users` varchar(50)
,`volume_parameters` varchar(50)
,`web_client10` varchar(10)
,`web_client_support` varchar(10)
);

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `lic_view_full_tabler`
-- (См. Ниже фактическое представление)
--
CREATE TABLE `lic_view_full_tabler` (
`client` varchar(10)
,`client10` varchar(10)
,`date_creation` date
,`date_insert` datetime
,`date_latest_update` datetime
,`date_sub_upgrade` date
,`exchange_protocol_dnp3` varchar(10)
,`id` int
,`max_soft_version` varchar(10)
,`name_count` varchar(255)
,`opc_support` varchar(10)
,`platform` varchar(100)
,`protocol_support` varchar(10)
,`record_id` int
,`record_link` varchar(255)
,`record_name` varchar(50)
,`security_level` varchar(50)
,`users` varchar(50)
,`volume_parameters` varchar(50)
,`web_client10` varchar(10)
,`web_client_support` varchar(10)
,`Конечник` varchar(255)
,`Номер_лицензии` varchar(50)
,`Объект` varchar(255)
,`Состояние_лицензии` varchar(255)
);

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `lic_view_main_tabler`
-- (См. Ниже фактическое представление)
--
CREATE TABLE `lic_view_main_tabler` (
`date_creation` date
,`date_latest_update` datetime
,`date_sub_upgrade` date
,`id` int
,`platform` varchar(100)
,`records` bigint
,`users` varchar(50)
,`volume_parameters` varchar(50)
,`Конечник` varchar(255)
,`Контрагент` varchar(255)
,`Номер лицензии` varchar(50)
,`Объект` varchar(255)
,`Состояние` varchar(255)
);

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `lic_view_num_records`
-- (См. Ниже фактическое представление)
--
CREATE TABLE `lic_view_num_records` (
`id` int
,`name_count` varchar(255)
,`records` bigint
);

-- --------------------------------------------------------

--
-- Структура для представления `lic_view_actual_lic_id`
--
DROP TABLE IF EXISTS `lic_view_actual_lic_id`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `lic_view_actual_lic_id`  AS SELECT max(`l`.`id`) AS `id`, `l`.`num_lic` AS `num_lic`, max(`l`.`text_lic`) AS `text_lic`, max(`l`.`the_end_user`) AS `the_end_user`, max(`l`.`object`) AS `object`, max(`l`.`actual`) AS `actual` FROM `lic_id` AS `l` WHERE (`l`.`actual` = 0) GROUP BY `l`.`num_lic``num_lic`  ;

-- --------------------------------------------------------

--
-- Структура для представления `lic_view_actual_param`
--
DROP TABLE IF EXISTS `lic_view_actual_param`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `lic_view_actual_param`  AS SELECT max(`l`.`id`) AS `id`, `l`.`lic_id` AS `lic_id`, max(`l`.`volume_parameters`) AS `volume_parameters`, max(`l`.`users`) AS `users`, max(`l`.`platform`) AS `platform`, max(`l`.`date_creation`) AS `date_creation`, max(`l`.`date_sub_upgrade`) AS `date_sub_upgrade`, max(`l`.`date_latest_update`) AS `date_latest_update`, max(`l`.`actual`) AS `actual`, max(`l`.`rec_date`) AS `rec_date`, max(`l`.`max_soft_version`) AS `max_soft_version`, max(`l`.`client`) AS `client`, max(`l`.`client10`) AS `client10`, max(`l`.`web_client10`) AS `web_client10`, max(`l`.`protocol_support`) AS `protocol_support`, max(`l`.`opc_support`) AS `opc_support`, max(`l`.`web_client_support`) AS `web_client_support`, max(`l`.`exchange_protocol_dnp3`) AS `exchange_protocol_dnp3`, max(`l`.`security_level`) AS `security_level` FROM `lic_param` AS `l` WHERE (`l`.`actual` = 0) GROUP BY `l`.`lic_id``lic_id`  ;

-- --------------------------------------------------------

--
-- Структура для представления `lic_view_full_tabler`
--
DROP TABLE IF EXISTS `lic_view_full_tabler`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `lic_view_full_tabler`  AS SELECT `lic_id`.`id` AS `id`, `lic_id`.`num_lic` AS `Номер_лицензии`, `lic_id`.`text_lic` AS `Состояние_лицензии`, `lic_id`.`the_end_user` AS `Конечник`, `lic_id`.`object` AS `Объект`, `p`.`volume_parameters` AS `volume_parameters`, `p`.`users` AS `users`, `p`.`platform` AS `platform`, `p`.`date_creation` AS `date_creation`, `p`.`date_sub_upgrade` AS `date_sub_upgrade`, `p`.`date_latest_update` AS `date_latest_update`, `p`.`max_soft_version` AS `max_soft_version`, `p`.`client` AS `client`, `p`.`client10` AS `client10`, `p`.`web_client10` AS `web_client10`, `p`.`protocol_support` AS `protocol_support`, `p`.`opc_support` AS `opc_support`, `p`.`web_client_support` AS `web_client_support`, `p`.`exchange_protocol_dnp3` AS `exchange_protocol_dnp3`, `p`.`security_level` AS `security_level`, `r`.`record_name` AS `record_name`, `r`.`record_link` AS `record_link`, `r`.`name_count` AS `name_count`, `r`.`date_insert` AS `date_insert`, `r`.`id` AS `record_id` FROM ((`lic_id` left join `lic_view_actual_param` `p` on((`lic_id`.`id` = `p`.`lic_id`))) left join `lic_record` `r` on(((`lic_id`.`id` = `r`.`id`) and (`r`.`date_insert` = (select max(`lr`.`date_insert`) from `lic_record` `lr` where (`lr`.`id` = `lic_id`.`id`)))))) ORDER BY (case when (`lic_id`.`text_lic` = 'Лицензия аннулирована. Использование лицензии запрещено.') then 3 when (`lic_id`.`text_lic` = '<p>Лицензия не найдена.</p>') then 4 else 1 end) ASC, `p`.`date_creation` AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Структура для представления `lic_view_main_tabler`
--
DROP TABLE IF EXISTS `lic_view_main_tabler`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `lic_view_main_tabler`  AS SELECT `v`.`id` AS `id`, `v`.`num_lic` AS `Номер лицензии`, (case when (`v`.`text_lic` = 'Лицензия аннулирована. Использование лицензии запрещено.') then 'Аннулирована' when (`v`.`text_lic` = '<p>Лицензия не найдена.</p>') then 'Не найдена' else `v`.`text_lic` end) AS `Состояние`, `v`.`the_end_user` AS `Конечник`, `v`.`object` AS `Объект`, `p`.`volume_parameters` AS `volume_parameters`, `p`.`users` AS `users`, `p`.`platform` AS `platform`, `p`.`date_creation` AS `date_creation`, `p`.`date_sub_upgrade` AS `date_sub_upgrade`, `p`.`date_latest_update` AS `date_latest_update`, `r`.`name_count` AS `Контрагент`, `r`.`records` AS `records` FROM ((`lic_view_actual_lic_id` `v` left join `lic_view_actual_param` `p` on((`v`.`id` = `p`.`lic_id`))) left join `lic_view_num_records` `r` on((`v`.`id` = `r`.`id`))) ORDER BY (case when (`v`.`text_lic` = 'Лицензия аннулирована. Использование лицензии запрещено.') then 3 when (`v`.`text_lic` = '<p>Лицензия не найдена.</p>') then 4 else 1 end) ASC, `p`.`date_creation` AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Структура для представления `lic_view_num_records`
--
DROP TABLE IF EXISTS `lic_view_num_records`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `lic_view_num_records`  AS SELECT `lic_record`.`id` AS `id`, `lic_record`.`name_count` AS `name_count`, count(`lic_record`.`id`) AS `records` FROM `lic_record` GROUP BY `lic_record`.`id``id`  ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `lic_id`
--
ALTER TABLE `lic_id`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `lic_param`
--
ALTER TABLE `lic_param`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_lic_param_enservicodb.lic_id` (`lic_id`);

--
-- Индексы таблицы `lic_record`
--
ALTER TABLE `lic_record`
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `lic_id`
--
ALTER TABLE `lic_id`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9070;

--
-- AUTO_INCREMENT для таблицы `lic_param`
--
ALTER TABLE `lic_param`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ид строки', AUTO_INCREMENT=7577;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `lic_param`
--
ALTER TABLE `lic_param`
  ADD CONSTRAINT `FK_lic_param_enservicodb.lic_id` FOREIGN KEY (`lic_id`) REFERENCES `lic_id` (`id`);

--
-- Ограничения внешнего ключа таблицы `lic_record`
--
ALTER TABLE `lic_record`
  ADD CONSTRAINT `FK_lic_record_lic_id` FOREIGN KEY (`id`) REFERENCES `lic_id` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
