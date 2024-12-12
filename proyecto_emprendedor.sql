
CREATE database IF NOT EXISTS proyecto_emprendedor;
use proyecto_emprendedor;

CREATE TABLE `usuario_administrador` (
  `id_usuario_administrador` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contrasenia` varchar(120) NOT NULL,
  `activado` tinyint(4) NOT NULL,
  `token_contrasenia` varchar(45) DEFAULT '0',
  `contrasenia_pedido` tinyint(4) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `tipo_usuario_admin` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tipo_usuario` (
  `id_tipo_usuario` int(11) NOT NULL,
  `nombre_tipo_usuario` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tipo_notificacion` (
  `id_tipo_notificacion` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `estado_producto` (
  `id_estado_producto` int(11) NOT NULL,
  `estado` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `categoria_producto` (
  `id_categoria_producto` int(11) NOT NULL,
  `nombre_categoria` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(350) NOT NULL,
  `contrasenia` varchar(120) NOT NULL,
  `fecha` datetime NOT NULL,
  `token` varchar(45) DEFAULT NULL,
  `token_contrasenia` varchar(45) DEFAULT NULL,
  `contrasenia_pedido` tinyint(4) DEFAULT 0,
  `nuevo_email` varchar(350) DEFAULT NULL,
  `token_nuevo_email` varchar(45) DEFAULT NULL,
  `activado` tinyint(4) NOT NULL DEFAULT 0,
  `baneado` tinyint(4) NOT NULL DEFAULT 0,
  `id_tipo_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `usuario_emprendedor` (
  `id_usuario_emprendedor` int(11) NOT NULL,
  `nombre_emprendimiento` varchar(100) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `calificacion_emprendedor` int(11) DEFAULT NULL,
  `foto_perfil_nombre` varchar(45) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `publicacion_informacion` (
  `id_publicacion_informacion` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_publicacion` datetime NOT NULL,
  `map_latitud` double DEFAULT NULL,
  `map_longitud` double DEFAULT NULL,
  `id_usuario_emprendedor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `archivo_publicacion_informacion` (
  `id_archivo_publicacion` int(11) NOT NULL,
  `nombre_carpeta` varchar(1000) NOT NULL,
  `nombre_archivo` varchar(1000) NOT NULL,
  `id_publicacion_informacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `publicacion_producto` (
  `id_publicacion_producto` int(11) NOT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `fecha_publicacion` datetime NOT NULL,
  `fecha_modificación` datetime DEFAULT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `precio` double NOT NULL,
  `stock` int(11) NOT NULL,
  `calificacion` int(11) DEFAULT NULL,
  `id_estado_producto` int(11) NOT NULL DEFAULT 1,
  `id_categoria_producto` int(11) NOT NULL,
  `id_usuario_emprendedor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `archivo_publicacion_producto` (
  `id_archivo_publicacion` int(11) NOT NULL,
  `nombre_carpeta` varchar(1000) NOT NULL,
  `nombre_archivo` varchar(1000) NOT NULL,
  `id_publicacion_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `pregunta_respuesta` (
  `id_pregunta_respuesta` int(11) NOT NULL,
  `pregunta` varchar(250) NOT NULL,
  `fecha_pregunta` datetime NOT NULL,
  `respuesta` varchar(1000) DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `seguimiento` (
  `id_seguimiento` int(11) NOT NULL,
  `fecha_seguimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario_seguidor` int(11) NOT NULL,
  `id_usuario_emprendedor_seguido` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `fecha_notificacion` datetime NULL,
  `leido` tinyint(4) NOT NULL DEFAULT 0,
  `id_usuario_notificar` int(11) NOT NULL,
  `id_usuario_interaccion` int(11) NOT NULL,
  `id_pregunta` int(11) DEFAULT NULL,
  `id_respuesta` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_tipo_notificacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `usuario_administrador` (`id_usuario_administrador`, `nombre_usuario`, `nombres`, `apellidos`, `email`, `contrasenia`, `activado`, `token_contrasenia`, `contrasenia_pedido`, `fecha`, `tipo_usuario_admin`) VALUES
(1, 'admin', 'Roberto Nicolas Agustin', 'Torrico', 'roberto.torrico@davinci.edu.ar', '$2y$10$TA3v1n8RVeKSyG8nvnByV.YSV/k/vtRPLrtVO1t9jGp/KbdfN7JCC', 1, NULL, 0, '2024-04-14 18:13:53', 'admin');


INSERT INTO `tipo_notificacion` (`id_tipo_notificacion`, `tipo`) VALUES
(1, 'Seguimiento'),
(2, 'Pregunta'),
(3, 'Respuesta');

INSERT INTO `tipo_usuario` (`id_tipo_usuario`, `nombre_tipo_usuario`) VALUES
(1, 'Común'),
(2, 'Emprendedor');

INSERT INTO `estado_producto` (`id_estado_producto`, `estado`) VALUES
(1, 'Disponible'),
(2, 'Pausado'),
(3, 'Finalizado');


INSERT INTO `categoria_producto` (`id_categoria_producto`, `nombre_categoria`) VALUES
(1, 'Decoracion del hogar'),
(3, 'Productos organicos'),
(4, 'Libros'),
(5, 'Accesorios para bebes'),
(6, 'juguetes hechos a mano'),
(7, 'Pintura y cuadros'),
(8, 'Artesenia'),
(9, 'Otros'),
(11, 'Joyeria');


INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `nombres`, `apellidos`, `email`, `contrasenia`, `fecha`, `token`, `token_contrasenia`, `contrasenia_pedido`, `nuevo_email`, `token_nuevo_email`, `activado`, `baneado`, `id_tipo_usuario`) VALUES
(5, 'nom_usuario', 'Roberto Nicolas', 'Torrico', 'roberto.torrico@davinci.edu.ar', '$2y$10$sCK2axdRhp/.coYDo3LbN.t9tVhHBeIrOXUdLtFJfriUHaVcXlHEG', '2024-07-29 17:21:06', NULL, '70e807e60de650371fcdc0b541b9f536', 1, NULL, NULL, 1, 0, 2),
(6, 'nom_usuario2', 'Roberto Nicolas Agustin', 'Torrico', 'roberto.nicolas.torrico@davinci.edu.ar', '$2y$10$vxz9kW6R/Ly1PygJJxwn.erpAEhvjanFohmEgxjNXqYCixoLWB9v2', '2024-11-27 21:25:34', 'ed428977d613f9f6beaa72278ed1173b', NULL, 0, NULL, NULL, 1, 0, 1),
(7, 'emprendimiento_prueb', 'Roberto Nicolas Agustin', 'Torrico', 'nico_t27@hotmail.com', '$2y$10$FQxpOMiZkcQOJ44e4SCigebjbt/AtJT3Y3PHlvUuhU40NVgY739aK', '2024-11-27 22:48:20', NULL, NULL, 0, NULL, NULL, 1, 0, 2),
(8, 'robert_torrico', 'Roberto', 'Torrico', 'nicolastorrico0@gmail.com', '$2y$10$qR4QW.p5ff02AXl8Yy.mRO8wKeHy9itim0c5E8UcUtvSFXyswzbsK', '2024-11-27 23:09:17', 'beb15e6a258fc61c5bdabebb2ef466d5', NULL, 0, NULL, NULL, 1, 0, 2),
(18, 'nom_usuario3', 'Roberto', 'Torrico', 'roberto.torricooaa@davinci.edu.ar', '$2y$10$bvl5WQZAtcx0FJj1nzHFyuxS1m/1USv2cF7.wfVTQ5wN.Kz4GYNri', '2024-12-06 18:25:20', NULL, NULL, 0, NULL, NULL, 1, 0, 1);


INSERT INTO `usuario_emprendedor` (`id_usuario_emprendedor`, `nombre_emprendimiento`, `descripcion`, `calificacion_emprendedor`, `foto_perfil_nombre`, `id_usuario`) VALUES
(3, 'nom_emprendimiento', 'Bienvenidos', NULL, '2024-12-07_19-36-52.jpg', 5),
(4, 'remeras_subli_empren', NULL, NULL, '2024-12-05_12-50-00.png', 7),
(5, 'nombre_emp', NULL, NULL, NULL, 8);

INSERT INTO `publicacion_informacion` (`id_publicacion_informacion`, `descripcion`, `fecha_publicacion`, `map_latitud`, `map_longitud`, `id_usuario_emprendedor`) VALUES
(4, 'Nuevas tazas disponibles', '2024-11-27 18:40:58', NULL, NULL, 3),
(5, 'Nuevas tazas disponibles', '2024-11-27 18:43:15', -34.6129061, -58.4018542, 3),
(6, 'Nuevas tazas disponibles', '2024-11-27 18:46:29', NULL, NULL, 3),
(7, 'Nuevas tazas disponibles', '2024-11-27 18:47:26', -34.6129061, -58.4018542, 3),
(8, 'Nuevas remeras disponibles', '2024-12-05 13:30:49', NULL, NULL, 4);


INSERT INTO `archivo_publicacion_informacion` (`id_archivo_publicacion`, `nombre_carpeta`, `nombre_archivo`, `id_publicacion_informacion`) VALUES
(7, '2024-11-27_18-46-29', '2024-11-27_18-48-09_0.jpg', 6),
(8, '2024-11-27_18-46-29', '2024-11-27_18-48-09_1.jpg', 6),
(9, '2024-11-27_18-46-29', '2024-11-27_18-48-09_2.jpg', 6),
(10, '2024-11-27_18-47-26', '2024-11-27_18-48-32_0.jpg', 7),
(11, '2024-12-05_13-30-49', '2024-12-05_13-30-49_0.mp4', 8);


INSERT INTO `publicacion_producto` (`id_publicacion_producto`, `nombre_producto`, `fecha_publicacion`, `fecha_modificación`, `descripcion`, `precio`, `stock`, `calificacion`, `id_estado_producto`, `id_categoria_producto`, `id_usuario_emprendedor`) VALUES
(1, 'Tazas de demon slayer', '2024-11-17 16:11:53', '2024-12-08 23:55:41', 'Taza de cerámica personalizadas sublimadas con los mas lindos diseños de demon slayer', 700, 100, NULL, 1, 1, 3),
(3, 'Remeras sublimadas de Attack On Titan', '2024-10-23 08:10:03', '2024-12-05 13:40:44', 'Remeras personalizadas sublimadas con diseños de Attack On Titan', 20000, 20, NULL, 3, 9, 4);


INSERT INTO `archivo_publicacion_producto` (`id_archivo_publicacion`, `nombre_carpeta`, `nombre_archivo`, `id_publicacion_producto`) VALUES
(5, '2024-11-26_19-55-53', '2024-11-21_14-11-00_0.jpg', 1),
(7, '2024-11-27_19-51-03', '2024-11-27_19-52-54_0.jpg', 3);

INSERT INTO `pregunta_respuesta` (`id_pregunta_respuesta`, `pregunta`, `fecha_pregunta`, `respuesta`, `fecha_respuesta`, `id_usuario`, `id_producto`) VALUES
(5, 'Hola Buenas ¿Aún tenés tazas de Tanjiro?', '2024-11-27 22:17:15', NULL, NULL, 6, 1),
(29, 'Hola Buenas ¿Aún tenés tazas de Tanjiro?', '2024-12-07 14:41:35', NULL, NULL, 18, 1),
(33, 'Hola Buenas ¿Aún tenés tazas de Tanjiro?', '2024-12-09 02:55:56', NULL, NULL, 8, 1),
(34, 'Hola Buenas ¿Aún tenés tazas de Tanjiro?', '2024-12-09 02:56:30', NULL, NULL, 7, 1),
(35, 'Hola Buenas ¿Aún tenés tazas de Tanjiro?', '2024-12-09 02:57:04', NULL, NULL, 7, 1);



INSERT INTO `seguimiento` (`id_seguimiento`, `fecha_seguimiento`, `id_usuario_seguidor`, `id_usuario_emprendedor_seguido`) VALUES
(69, '2024-12-07 14:38:42', 18, 4),
(74, '2024-12-07 22:50:37', 5, 4),
(75, '2024-12-09 01:05:25', 7, 5),
(76, '2024-12-09 01:05:26', 7, 3);


INSERT INTO `notificaciones` (`id_notificacion`, `fecha_notificacion`, `leido`, `id_usuario_notificar`, `id_usuario_interaccion`, `id_pregunta`, `id_respuesta`, `id_producto`, `id_tipo_notificacion`) VALUES
(123, '2024-12-07 22:50:37', 1, 7, 5, NULL, NULL, NULL, 1),
(127, '2024-12-09 02:55:56', 0, 5, 8, 33, NULL, 1, 2),
(128, '2024-12-09 02:56:30', 0, 5, 7, 34, NULL, 1, 2),
(129, '2024-12-09 02:57:04', 0, 5, 7, 35, NULL, 1, 2),
(132, '2024-12-09 01:05:25', 0, 8, 7, NULL, NULL, NULL, 1),
(133, '2024-12-09 01:05:26', 0, 5, 7, NULL, NULL, NULL, 1);


--
-- Indices de la tabla `archivo_publicacion_informacion`
--
ALTER TABLE `archivo_publicacion_informacion`
  ADD PRIMARY KEY (`id_archivo_publicacion`),
  ADD KEY `id_publicacion_informacion` (`id_publicacion_informacion`);

--
-- Indices de la tabla `archivo_publicacion_producto`
--
ALTER TABLE `archivo_publicacion_producto`
  ADD PRIMARY KEY (`id_archivo_publicacion`),
  ADD KEY `id_publicacion_producto` (`id_publicacion_producto`);

--
-- Indices de la tabla `categoria_producto`
--
ALTER TABLE `categoria_producto`
  ADD PRIMARY KEY (`id_categoria_producto`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `notificaciones_ibfk_2` (`id_usuario_notificar`),
  ADD KEY `notificaciones_ibfk_3` (`id_usuario_interaccion`),
  ADD KEY `notificaciones_ibfk_4` (`id_pregunta`);

--
-- Indices de la tabla `pregunta_respuesta`
--
ALTER TABLE `pregunta_respuesta`
  ADD PRIMARY KEY (`id_pregunta_respuesta`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `publicacion_informacion`
--
ALTER TABLE `publicacion_informacion`
  ADD PRIMARY KEY (`id_publicacion_informacion`),
  ADD KEY `id_usuario_emprendedor` (`id_usuario_emprendedor`);

--
-- Indices de la tabla `publicacion_producto`
--
ALTER TABLE `publicacion_producto`
  ADD PRIMARY KEY (`id_publicacion_producto`),
  ADD KEY `id_usuario_emprendedor` (`id_usuario_emprendedor`),
  ADD KEY `id_categoria_producto` (`id_categoria_producto`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id_seguimiento`),
  ADD KEY `seguimiento_ibfk_1` (`id_usuario_seguidor`),
  ADD KEY `id_usuario_emprendedor_seguido` (`id_usuario_emprendedor_seguido`);

--
-- Indices de la tabla `tipo_notificacion`
--
ALTER TABLE `tipo_notificacion`
  ADD PRIMARY KEY (`id_tipo_notificacion`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`id_tipo_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `usuario_administrador`
--
ALTER TABLE `usuario_administrador`
  ADD PRIMARY KEY (`id_usuario_administrador`);

--
-- Indices de la tabla `usuario_emprendedor`
--
ALTER TABLE `usuario_emprendedor`
  ADD PRIMARY KEY (`id_usuario_emprendedor`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivo_publicacion_informacion`
--
ALTER TABLE `archivo_publicacion_informacion`
  MODIFY `id_archivo_publicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `archivo_publicacion_producto`
--
ALTER TABLE `archivo_publicacion_producto`
  MODIFY `id_archivo_publicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `categoria_producto`
--
ALTER TABLE `categoria_producto`
  MODIFY `id_categoria_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT de la tabla `pregunta_respuesta`
--
ALTER TABLE `pregunta_respuesta`
  MODIFY `id_pregunta_respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `publicacion_informacion`
--
ALTER TABLE `publicacion_informacion`
  MODIFY `id_publicacion_informacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `publicacion_producto`
--
ALTER TABLE `publicacion_producto`
  MODIFY `id_publicacion_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT de la tabla `tipo_notificacion`
--
ALTER TABLE `tipo_notificacion`
  MODIFY `id_tipo_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `id_tipo_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `usuario_administrador`
--
ALTER TABLE `usuario_administrador`
  MODIFY `id_usuario_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario_emprendedor`
--
ALTER TABLE `usuario_emprendedor`
  MODIFY `id_usuario_emprendedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivo_publicacion_informacion`
--
ALTER TABLE `archivo_publicacion_informacion`
  ADD CONSTRAINT `archivo_publicacion_informacion_ibfk_1` FOREIGN KEY (`id_publicacion_informacion`) REFERENCES `publicacion_informacion` (`id_publicacion_informacion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `archivo_publicacion_producto`
--
ALTER TABLE `archivo_publicacion_producto`
  ADD CONSTRAINT `archivo_publicacion_producto_ibfk_1` FOREIGN KEY (`id_publicacion_producto`) REFERENCES `publicacion_producto` (`id_publicacion_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `publicacion_producto` (`id_publicacion_producto`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`id_usuario_notificar`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_ibfk_3` FOREIGN KEY (`id_usuario_interaccion`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_ibfk_4` FOREIGN KEY (`id_pregunta`) REFERENCES `pregunta_respuesta` (`id_pregunta_respuesta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pregunta_respuesta`
--
ALTER TABLE `pregunta_respuesta`
  ADD CONSTRAINT `pregunta_respuesta_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `pregunta_respuesta_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `publicacion_producto` (`id_publicacion_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicacion_informacion`
--
ALTER TABLE `publicacion_informacion`
  ADD CONSTRAINT `publicacion_informacion_ibfk_1` FOREIGN KEY (`id_usuario_emprendedor`) REFERENCES `usuario_emprendedor` (`id_usuario_emprendedor`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicacion_producto`
--
ALTER TABLE `publicacion_producto`
  ADD CONSTRAINT `publicacion_producto_ibfk_1` FOREIGN KEY (`id_usuario_emprendedor`) REFERENCES `usuario_emprendedor` (`id_usuario_emprendedor`) ON DELETE CASCADE,
  ADD CONSTRAINT `publicacion_producto_ibfk_2` FOREIGN KEY (`id_categoria_producto`) REFERENCES `categoria_producto` (`id_categoria_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD CONSTRAINT `seguimiento_ibfk_1` FOREIGN KEY (`id_usuario_seguidor`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguimiento_ibfk_2` FOREIGN KEY (`id_usuario_emprendedor_seguido`) REFERENCES `usuario_emprendedor` (`id_usuario_emprendedor`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario_emprendedor`
--
ALTER TABLE `usuario_emprendedor`
  ADD CONSTRAINT `usuario_emprendedor_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;





