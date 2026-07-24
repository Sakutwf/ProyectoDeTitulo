-- MySQL dump 10.13  Distrib 8.4.10, for Linux (x86_64)
--
-- Host: localhost    Database: SistemaCruzRoja
-- ------------------------------------------------------
-- Server version	8.4.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `actividad_climas`
--

DROP TABLE IF EXISTS `actividad_climas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actividad_climas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `archivo_id` bigint unsigned DEFAULT NULL,
  `orden` int unsigned NOT NULL DEFAULT '1',
  `temperatura_minima` decimal(5,2) DEFAULT NULL,
  `temperatura_maxima` decimal(5,2) DEFAULT NULL,
  `tipo_clima` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `actividad_climas_actividad_id_foreign` (`actividad_id`),
  KEY `actividad_climas_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `actividad_climas_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `actividad_climas_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `archivos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actividad_climas`
--

LOCK TABLES `actividad_climas` WRITE;
/*!40000 ALTER TABLE `actividad_climas` DISABLE KEYS */;
/*!40000 ALTER TABLE `actividad_climas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actividad_voluntario`
--

DROP TABLE IF EXISTS `actividad_voluntario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actividad_voluntario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `voluntario_id` bigint unsigned NOT NULL,
  `horas_asistidas` decimal(6,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aprobada',
  `registrado_por` bigint unsigned DEFAULT NULL,
  `revisado_por` bigint unsigned DEFAULT NULL,
  `revisado_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actividad_voluntario_actividad_id_voluntario_id_unique` (`actividad_id`,`voluntario_id`),
  KEY `actividad_voluntario_voluntario_id_foreign` (`voluntario_id`),
  KEY `actividad_voluntario_registrado_por_foreign` (`registrado_por`),
  KEY `actividad_voluntario_revisado_por_foreign` (`revisado_por`),
  KEY `actividad_voluntario_estado_index` (`estado`),
  CONSTRAINT `actividad_voluntario_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `actividad_voluntario_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `actividad_voluntario_revisado_por_foreign` FOREIGN KEY (`revisado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `actividad_voluntario_voluntario_id_foreign` FOREIGN KEY (`voluntario_id`) REFERENCES `voluntarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actividad_voluntario`
--

LOCK TABLES `actividad_voluntario` WRITE;
/*!40000 ALTER TABLE `actividad_voluntario` DISABLE KEYS */;
/*!40000 ALTER TABLE `actividad_voluntario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actividades`
--

DROP TABLE IF EXISTS `actividades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actividades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filial_id` bigint unsigned NOT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivo` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` date NOT NULL,
  `fecha_termino` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_termino` time DEFAULT NULL,
  `lugar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horas_totales` decimal(6,2) NOT NULL DEFAULT '0.00',
  `colaborador_externo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `actividades_filial_id_foreign` (`filial_id`),
  KEY `actividades_creado_por_foreign` (`creado_por`),
  CONSTRAINT `actividades_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `actividades_filial_id_foreign` FOREIGN KEY (`filial_id`) REFERENCES `filiales` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actividades`
--

LOCK TABLES `actividades` WRITE;
/*!40000 ALTER TABLE `actividades` DISABLE KEYS */;
/*!40000 ALTER TABLE `actividades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `albumes`
--

DROP TABLE IF EXISTS `albumes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `albumes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `actividad_id` bigint unsigned NOT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `albumes_actividad_id_foreign` (`actividad_id`),
  KEY `albumes_creado_por_foreign` (`creado_por`),
  CONSTRAINT `albumes_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `albumes_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `albumes`
--

LOCK TABLES `albumes` WRITE;
/*!40000 ALTER TABLE `albumes` DISABLE KEYS */;
/*!40000 ALTER TABLE `albumes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `analisis_contexto`
--

DROP TABLE IF EXISTS `analisis_contexto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analisis_contexto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `documento_actividad_id` bigint unsigned NOT NULL,
  `numero_documento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objetivo` text COLLATE utf8mb4_unicode_ci,
  `descripcion_evento` text COLLATE utf8mb4_unicode_ci,
  `plan_traslado` text COLLATE utf8mb4_unicode_ci,
  `coordinacion_emergencia` text COLLATE utf8mb4_unicode_ci,
  `centros_salud_cercanos` text COLLATE utf8mb4_unicode_ci,
  `conclusion` text COLLATE utf8mb4_unicode_ci,
  `elaborado_por` bigint unsigned DEFAULT NULL,
  `fecha_elaboracion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `analisis_contexto_actividad_id_unique` (`actividad_id`),
  UNIQUE KEY `analisis_contexto_documento_actividad_id_unique` (`documento_actividad_id`),
  KEY `analisis_contexto_elaborado_por_foreign` (`elaborado_por`),
  CONSTRAINT `analisis_contexto_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `analisis_contexto_documento_actividad_id_foreign` FOREIGN KEY (`documento_actividad_id`) REFERENCES `documentos_actividad` (`id`) ON DELETE CASCADE,
  CONSTRAINT `analisis_contexto_elaborado_por_foreign` FOREIGN KEY (`elaborado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `analisis_contexto`
--

LOCK TABLES `analisis_contexto` WRITE;
/*!40000 ALTER TABLE `analisis_contexto` DISABLE KEYS */;
/*!40000 ALTER TABLE `analisis_contexto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archivos`
--

DROP TABLE IF EXISTS `archivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entidad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` bigint unsigned NOT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamano` bigint unsigned DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `subido_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archivos_subido_por_foreign` (`subido_por`),
  KEY `archivos_entidad_entidad_id_index` (`entidad`,`entidad_id`),
  CONSTRAINT `archivos_subido_por_foreign` FOREIGN KEY (`subido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archivos`
--

LOCK TABLES `archivos` WRITE;
/*!40000 ALTER TABLE `archivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `archivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boletas_viatico`
--

DROP TABLE IF EXISTS `boletas_viatico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `boletas_viatico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `voluntario_id` bigint unsigned NOT NULL,
  `archivo_id` bigint unsigned DEFAULT NULL,
  `detalle_compra` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_compra` date DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_revision` text COLLATE utf8mb4_unicode_ci,
  `fecha_pago` date DEFAULT NULL,
  `observacion_revision` text COLLATE utf8mb4_unicode_ci,
  `revisado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `boletas_viatico_actividad_id_foreign` (`actividad_id`),
  KEY `boletas_viatico_voluntario_id_foreign` (`voluntario_id`),
  KEY `boletas_viatico_revisado_por_foreign` (`revisado_por`),
  KEY `boletas_viatico_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `boletas_viatico_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `boletas_viatico_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `archivos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `boletas_viatico_revisado_por_foreign` FOREIGN KEY (`revisado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `boletas_viatico_voluntario_id_foreign` FOREIGN KEY (`voluntario_id`) REFERENCES `voluntarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boletas_viatico`
--

LOCK TABLES `boletas_viatico` WRITE;
/*!40000 ALTER TABLE `boletas_viatico` DISABLE KEYS */;
/*!40000 ALTER TABLE `boletas_viatico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campanas_notificacion`
--

DROP TABLE IF EXISTS `campanas_notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campanas_notificacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asunto_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asunto_id` bigint unsigned DEFAULT NULL,
  `asunto_correo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci,
  `metadatos` json DEFAULT NULL,
  `huella` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'encolada',
  `autorizada_por` bigint unsigned DEFAULT NULL,
  `autorizada_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campanas_notificacion_asunto_type_asunto_id_index` (`asunto_type`,`asunto_id`),
  KEY `campanas_notificacion_autorizada_por_foreign` (`autorizada_por`),
  KEY `campanas_notificacion_tipo_asunto_type_asunto_id_index` (`tipo`,`asunto_type`,`asunto_id`),
  KEY `campanas_notificacion_huella_index` (`huella`),
  CONSTRAINT `campanas_notificacion_autorizada_por_foreign` FOREIGN KEY (`autorizada_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campanas_notificacion`
--

LOCK TABLES `campanas_notificacion` WRITE;
/*!40000 ALTER TABLE `campanas_notificacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `campanas_notificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargo_voluntario`
--

DROP TABLE IF EXISTS `cargo_voluntario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargo_voluntario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voluntario_id` bigint unsigned NOT NULL,
  `cargo_id` bigint unsigned NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_termino` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cargo_voluntario_voluntario_id_foreign` (`voluntario_id`),
  KEY `cargo_voluntario_cargo_id_foreign` (`cargo_id`),
  CONSTRAINT `cargo_voluntario_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `cargo_voluntario_voluntario_id_foreign` FOREIGN KEY (`voluntario_id`) REFERENCES `voluntarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargo_voluntario`
--

LOCK TABLES `cargo_voluntario` WRITE;
/*!40000 ALTER TABLE `cargo_voluntario` DISABLE KEYS */;
/*!40000 ALTER TABLE `cargo_voluntario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_cargo_id` bigint unsigned NOT NULL,
  `direccion_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cargos_tipo_cargo_id_foreign` (`tipo_cargo_id`),
  KEY `cargos_direccion_id_foreign` (`direccion_id`),
  CONSTRAINT `cargos_direccion_id_foreign` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cargos_tipo_cargo_id_foreign` FOREIGN KEY (`tipo_cargo_id`) REFERENCES `tipos_cargo` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargos`
--

LOCK TABLES `cargos` WRITE;
/*!40000 ALTER TABLE `cargos` DISABLE KEYS */;
/*!40000 ALTER TABLE `cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrusel_inicio`
--

DROP TABLE IF EXISTS `carrusel_inicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrusel_inicio` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titulo_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `bajada` text COLLATE utf8mb4_unicode_ci,
  `bajada_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `orden` int unsigned NOT NULL DEFAULT '0',
  `publicada` tinyint(1) NOT NULL DEFAULT '1',
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carrusel_inicio_creado_por_foreign` (`creado_por`),
  CONSTRAINT `carrusel_inicio_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrusel_inicio`
--

LOCK TABLES `carrusel_inicio` WRITE;
/*!40000 ALTER TABLE `carrusel_inicio` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrusel_inicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrusel_inicio_imagenes`
--

DROP TABLE IF EXISTS `carrusel_inicio_imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrusel_inicio_imagenes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `carrusel_inicio_id` bigint unsigned NOT NULL,
  `archivo_id` bigint unsigned NOT NULL,
  `orden` tinyint unsigned NOT NULL DEFAULT '0',
  `posicion_x` tinyint unsigned NOT NULL DEFAULT '50',
  `posicion_y` tinyint unsigned NOT NULL DEFAULT '50',
  `zoom` smallint unsigned NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrusel_inicio_imagenes_carrusel_inicio_id_archivo_id_unique` (`carrusel_inicio_id`,`archivo_id`),
  KEY `carrusel_inicio_imagenes_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `carrusel_inicio_imagenes_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `archivos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carrusel_inicio_imagenes_carrusel_inicio_id_foreign` FOREIGN KEY (`carrusel_inicio_id`) REFERENCES `carrusel_inicio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrusel_inicio_imagenes`
--

LOCK TABLES `carrusel_inicio_imagenes` WRITE;
/*!40000 ALTER TABLE `carrusel_inicio_imagenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrusel_inicio_imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clima_actividad`
--

DROP TABLE IF EXISTS `clima_actividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clima_actividad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `analisis_contexto_id` bigint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `temperatura_minima` decimal(5,2) DEFAULT NULL,
  `temperatura_maxima` decimal(5,2) DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clima_actividad_analisis_contexto_id_foreign` (`analisis_contexto_id`),
  CONSTRAINT `clima_actividad_analisis_contexto_id_foreign` FOREIGN KEY (`analisis_contexto_id`) REFERENCES `analisis_contexto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clima_actividad`
--

LOCK TABLES `clima_actividad` WRITE;
/*!40000 ALTER TABLE `clima_actividad` DISABLE KEYS */;
/*!40000 ALTER TABLE `clima_actividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cursos_voluntario`
--

DROP TABLE IF EXISTS `cursos_voluntario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cursos_voluntario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoja_vida_anual_id` bigint unsigned NOT NULL,
  `archivo_id` bigint unsigned DEFAULT NULL,
  `nombre_curso` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entregado_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_curso` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cursos_voluntario_hoja_vida_anual_id_foreign` (`hoja_vida_anual_id`),
  KEY `cursos_voluntario_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `cursos_voluntario_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `archivos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cursos_voluntario_hoja_vida_anual_id_foreign` FOREIGN KEY (`hoja_vida_anual_id`) REFERENCES `hoja_vida_anual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cursos_voluntario`
--

LOCK TABLES `cursos_voluntario` WRITE;
/*!40000 ALTER TABLE `cursos_voluntario` DISABLE KEYS */;
/*!40000 ALTER TABLE `cursos_voluntario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destinatarios_notificacion`
--

DROP TABLE IF EXISTS `destinatarios_notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `destinatarios_notificacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campana_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `error` text COLLATE utf8mb4_unicode_ci,
  `enviado_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `destinatarios_notificacion_campana_id_correo_unique` (`campana_id`,`correo`),
  KEY `destinatarios_notificacion_user_id_foreign` (`user_id`),
  CONSTRAINT `destinatarios_notificacion_campana_id_foreign` FOREIGN KEY (`campana_id`) REFERENCES `campanas_notificacion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `destinatarios_notificacion_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destinatarios_notificacion`
--

LOCK TABLES `destinatarios_notificacion` WRITE;
/*!40000 ALTER TABLE `destinatarios_notificacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `destinatarios_notificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direcciones`
--

DROP TABLE IF EXISTS `direcciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `direcciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `direcciones_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direcciones`
--

LOCK TABLES `direcciones` WRITE;
/*!40000 ALTER TABLE `direcciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `direcciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos_actividad`
--

DROP TABLE IF EXISTS `documentos_actividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos_actividad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `tipo_documento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Documento sin titulo',
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `fecha_documento` date DEFAULT NULL,
  `datos_contexto` json DEFAULT NULL,
  `contenido` json DEFAULT NULL,
  `generado_por` bigint unsigned DEFAULT NULL,
  `fecha_generacion` date DEFAULT NULL,
  `ruta_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documentos_actividad_actividad_id_foreign` (`actividad_id`),
  KEY `documentos_actividad_generado_por_foreign` (`generado_por`),
  CONSTRAINT `documentos_actividad_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documentos_actividad_generado_por_foreign` FOREIGN KEY (`generado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos_actividad`
--

LOCK TABLES `documentos_actividad` WRITE;
/*!40000 ALTER TABLE `documentos_actividad` DISABLE KEYS */;
/*!40000 ALTER TABLE `documentos_actividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filiales`
--

DROP TABLE IF EXISTS `filiales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filiales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cut` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comite_regional` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comuna` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filiales_cut_unique` (`cut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filiales`
--

LOCK TABLES `filiales` WRITE;
/*!40000 ALTER TABLE `filiales` DISABLE KEYS */;
/*!40000 ALTER TABLE `filiales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `galerias_actividad`
--

DROP TABLE IF EXISTS `galerias_actividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galerias_actividad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `archivo_id` bigint unsigned DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha` date DEFAULT NULL,
  `subido_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `galerias_actividad_actividad_id_foreign` (`actividad_id`),
  KEY `galerias_actividad_subido_por_foreign` (`subido_por`),
  KEY `galerias_actividad_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `galerias_actividad_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `galerias_actividad_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `archivos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `galerias_actividad_subido_por_foreign` FOREIGN KEY (`subido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `galerias_actividad`
--

LOCK TABLES `galerias_actividad` WRITE;
/*!40000 ALTER TABLE `galerias_actividad` DISABLE KEYS */;
/*!40000 ALTER TABLE `galerias_actividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoja_vida_anual`
--

DROP TABLE IF EXISTS `hoja_vida_anual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoja_vida_anual` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voluntario_id` bigint unsigned NOT NULL,
  `anio` year NOT NULL,
  `cargo_clave` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_nombre` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_grupo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_direccion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asistencia_anual_horas` decimal(6,2) NOT NULL DEFAULT '0.00',
  `asistencia_anual_porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
  `asistencia_anual_ajuste_horas` decimal(6,2) NOT NULL DEFAULT '0.00',
  `asistencia_reuniones_filial_ajuste_horas` decimal(6,2) NOT NULL DEFAULT '0.00',
  `asistencia_actividades_voluntariado_ajuste_horas` decimal(6,2) NOT NULL DEFAULT '0.00',
  `asistencia_horas_filial_ajuste_horas` decimal(6,2) NOT NULL DEFAULT '0.00',
  `asistencia_horas_formativas_ajuste_horas` decimal(6,2) NOT NULL DEFAULT '0.00',
  `estuvo_comision_servicio` tinyint(1) NOT NULL DEFAULT '0',
  `comision_fecha_inicio` date DEFAULT NULL,
  `comision_fecha_termino` date DEFAULT NULL,
  `comision_lugar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comision_actividad` text COLLATE utf8mb4_unicode_ci,
  `comentarios` text COLLATE utf8mb4_unicode_ci,
  `generada_por` bigint unsigned DEFAULT NULL,
  `fecha_generacion` date DEFAULT NULL,
  `ruta_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hoja_vida_anual_voluntario_id_anio_unique` (`voluntario_id`,`anio`),
  KEY `hoja_vida_anual_generada_por_foreign` (`generada_por`),
  CONSTRAINT `hoja_vida_anual_generada_por_foreign` FOREIGN KEY (`generada_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hoja_vida_anual_voluntario_id_foreign` FOREIGN KEY (`voluntario_id`) REFERENCES `voluntarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoja_vida_anual`
--

LOCK TABLES `hoja_vida_anual` WRITE;
/*!40000 ALTER TABLE `hoja_vida_anual` DISABLE KEYS */;
/*!40000 ALTER TABLE `hoja_vida_anual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `informe_narrativo`
--

DROP TABLE IF EXISTS `informe_narrativo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `informe_narrativo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `documento_actividad_id` bigint unsigned NOT NULL,
  `fecha_informe` date DEFAULT NULL,
  `objetivo_general` text COLLATE utf8mb4_unicode_ci,
  `objetivo_especifico` text COLLATE utf8mb4_unicode_ci,
  `descripcion_general` text COLLATE utf8mb4_unicode_ci,
  `situaciones_interes` text COLLATE utf8mb4_unicode_ci,
  `numero_atenciones_ppaa` int unsigned NOT NULL DEFAULT '0',
  `numero_asistencias_movilidad` int unsigned NOT NULL DEFAULT '0',
  `numero_votos_asistidos` int unsigned NOT NULL DEFAULT '0',
  `numero_traslados` int unsigned NOT NULL DEFAULT '0',
  `numero_hombres` int unsigned NOT NULL DEFAULT '0',
  `numero_mujeres` int unsigned NOT NULL DEFAULT '0',
  `numero_puestos` int unsigned NOT NULL DEFAULT '0',
  `numero_voluntarios` int unsigned NOT NULL DEFAULT '0',
  `numero_coordinadores` int unsigned NOT NULL DEFAULT '0',
  `numero_staff_medico` int unsigned NOT NULL DEFAULT '0',
  `numero_psicologos` int unsigned NOT NULL DEFAULT '0',
  `numero_enfermeria` int unsigned NOT NULL DEFAULT '0',
  `numero_tens` int unsigned NOT NULL DEFAULT '0',
  `numero_logisticos` int unsigned NOT NULL DEFAULT '0',
  `observaciones_generales` text COLLATE utf8mb4_unicode_ci,
  `logros` text COLLATE utf8mb4_unicode_ci,
  `desafios_dificultades` text COLLATE utf8mb4_unicode_ci,
  `recomendaciones` text COLLATE utf8mb4_unicode_ci,
  `percepcion` text COLLATE utf8mb4_unicode_ci,
  `autorizacion_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `elaborado_por` bigint unsigned DEFAULT NULL,
  `fecha_elaboracion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `informe_narrativo_actividad_id_unique` (`actividad_id`),
  UNIQUE KEY `informe_narrativo_documento_actividad_id_unique` (`documento_actividad_id`),
  KEY `informe_narrativo_elaborado_por_foreign` (`elaborado_por`),
  CONSTRAINT `informe_narrativo_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `informe_narrativo_documento_actividad_id_foreign` FOREIGN KEY (`documento_actividad_id`) REFERENCES `documentos_actividad` (`id`) ON DELETE CASCADE,
  CONSTRAINT `informe_narrativo_elaborado_por_foreign` FOREIGN KEY (`elaborado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `informe_narrativo`
--

LOCK TABLES `informe_narrativo` WRITE;
/*!40000 ALTER TABLE `informe_narrativo` DISABLE KEYS */;
/*!40000 ALTER TABLE `informe_narrativo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0000_04_11_045906_create_roles_table',1),(2,'0001_01_01_000000_create_users_table',1),(3,'0001_01_01_000001_create_cache_table',1),(4,'0001_01_01_000002_create_jobs_table',1),(5,'2025_04_10_003419_create_personal_access_tokens_table',1),(6,'2026_05_21_000001_create_permissions_table',1),(7,'2026_05_21_000002_create_permission_role_table',1),(8,'2026_05_21_000003_create_role_user_table',1),(9,'2026_06_23_000101_create_filiales_table',1),(10,'2026_06_23_000102_create_voluntarios_table',1),(11,'2026_06_23_000103_create_hoja_vida_anual_y_detalles_table',1),(12,'2026_06_23_000104_create_actividades_y_relaciones_table',1),(13,'2026_06_23_000105_create_documentacion_actividad_table',1),(14,'2026_06_23_000106_create_cargos_tables',1),(15,'2026_06_23_000107_create_archivos_y_novedades_table',1),(16,'2026_06_25_000108_add_foto_perfil_to_voluntarios_table',1),(17,'2026_06_25_000109_move_voluntario_profile_photos_to_archivos_table',1),(18,'2026_06_25_000110_add_archivo_references_to_activity_assets_tables',1),(19,'2026_06_25_000111_add_archivo_references_to_hoja_vida_detail_tables',1),(20,'2026_06_25_120000_add_asistencia_anual_ajuste_horas_to_hoja_vida_anual_table',1),(21,'2026_06_25_140000_add_attendance_adjustment_columns_to_hoja_vida_anual_table',1),(22,'2026_06_25_201000_add_cargo_periodo_to_hoja_vida_anual_table',1),(23,'2026_06_26_093500_make_creado_por_nullable_on_actividades_table',1),(24,'2026_06_26_113000_create_documentos_actividad_table',1),(25,'2026_06_29_000001_create_albumes_table',1),(26,'2026_07_01_000001_create_actividad_climas_table',1),(27,'2026_07_05_000200_create_otros_documentos_voluntario_table',1),(28,'2026_07_06_090000_add_payment_tracking_to_boletas_viatico_table',1),(29,'2026_07_12_190000_add_home_configuration_to_novedades',1),(30,'2026_07_12_210000_add_vertical_position_to_home_images',1),(31,'2026_07_12_220000_add_horizontal_position_to_home_images',1),(32,'2026_07_12_230000_add_zoom_to_home_images',1),(33,'2026_07_12_240000_create_portada_ajustes_table',1),(34,'2026_07_13_000000_add_footer_to_portada_ajustes',1),(35,'2026_07_13_010000_add_location_to_portada_ajustes',1),(36,'2026_07_13_020000_add_carousel_text_color_to_portada_ajustes',1),(37,'2026_07_13_030000_add_heading_colors_to_portada_ajustes',1),(38,'2026_07_13_040000_add_contact_email_to_portada_ajustes',1),(39,'2026_07_13_050000_add_business_hours_to_portada_ajustes',1),(40,'2026_07_13_060000_add_text_colors_to_carousel_slides',1),(41,'2026_07_13_070000_adapt_default_news_colors_to_red_background',1),(42,'2026_07_13_080000_restore_news_heading_colors',1),(43,'2026_07_13_090000_repair_cp850_mojibake_in_utf8_text',1),(44,'2026_07_13_100000_classify_and_clean_temporary_climate_images',1),(45,'2026_07_13_110000_create_notification_and_approval_workflows',1),(46,'2026_07_13_120000_add_notification_email_to_users',1),(47,'2026_07_13_130000_backfill_admin_notification_email',1),(48,'2026_07_14_000000_add_whatsapp_to_portada_ajustes',1),(49,'2026_07_14_010000_add_approval_to_actividad_voluntario_table',1),(50,'2026_07_14_020000_normalize_zero_hour_activity_enrollments',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `novedades`
--

DROP TABLE IF EXISTS `novedades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `novedades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actividad_id` bigint unsigned NOT NULL,
  `archivo_portada_id` bigint unsigned DEFAULT NULL,
  `posicion_x` tinyint unsigned NOT NULL DEFAULT '50',
  `posicion_y` tinyint unsigned NOT NULL DEFAULT '50',
  `zoom` smallint unsigned NOT NULL DEFAULT '100',
  `slug` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resumen` text COLLATE utf8mb4_unicode_ci,
  `contenido` text COLLATE utf8mb4_unicode_ci,
  `orden` int unsigned NOT NULL DEFAULT '0',
  `ancho` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tercio',
  `campos_visibles` json DEFAULT NULL,
  `publicada` tinyint(1) NOT NULL DEFAULT '1',
  `personas_ayudadas` int unsigned DEFAULT NULL,
  `creado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `novedades_slug_unique` (`slug`),
  KEY `novedades_actividad_id_foreign` (`actividad_id`),
  KEY `novedades_archivo_portada_id_foreign` (`archivo_portada_id`),
  KEY `novedades_creado_por_foreign` (`creado_por`),
  CONSTRAINT `novedades_actividad_id_foreign` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `novedades_archivo_portada_id_foreign` FOREIGN KEY (`archivo_portada_id`) REFERENCES `archivos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `novedades_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `novedades`
--

LOCK TABLES `novedades` WRITE;
/*!40000 ALTER TABLE `novedades` DISABLE KEYS */;
/*!40000 ALTER TABLE `novedades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otros_documentos_voluntario`
--

DROP TABLE IF EXISTS `otros_documentos_voluntario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otros_documentos_voluntario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoja_vida_anual_id` bigint unsigned NOT NULL,
  `archivo_id` bigint unsigned DEFAULT NULL,
  `nombre_documento` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otros_documentos_voluntario_hoja_vida_anual_id_foreign` (`hoja_vida_anual_id`),
  KEY `otros_documentos_voluntario_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `otros_documentos_voluntario_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `archivos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `otros_documentos_voluntario_hoja_vida_anual_id_foreign` FOREIGN KEY (`hoja_vida_anual_id`) REFERENCES `hoja_vida_anual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otros_documentos_voluntario`
--

LOCK TABLES `otros_documentos_voluntario` WRITE;
/*!40000 ALTER TABLE `otros_documentos_voluntario` DISABLE KEYS */;
/*!40000 ALTER TABLE `otros_documentos_voluntario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_role_permission_id_role_id_unique` (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_clave_unique` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portada_ajustes`
--

DROP TABLE IF EXISTS `portada_ajustes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portada_ajustes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `carrusel_etiqueta` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Historias que nos unen',
  `novedades_etiqueta` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Actualidad de nuestra comunidad',
  `novedades_titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Novedades de Cruz Roja',
  `novedades_descripcion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Conoce las actividades y el impacto de nuestros voluntarios.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `telefono` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `directorio` json DEFAULT NULL,
  `enlaces_relacionados` json DEFAULT NULL,
  `direccion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion_url` varchar(700) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carrusel_texto_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `carrusel_etiqueta_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `novedades_etiqueta_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#d72732',
  `novedades_titulo_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#011e41',
  `novedades_descripcion_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#5f6b7c',
  `correo_contacto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_atencion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portada_ajustes`
--

LOCK TABLES `portada_ajustes` WRITE;
/*!40000 ALTER TABLE `portada_ajustes` DISABLE KEYS */;
/*!40000 ALTER TABLE `portada_ajustes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconocimientos_voluntario`
--

DROP TABLE IF EXISTS `reconocimientos_voluntario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconocimientos_voluntario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoja_vida_anual_id` bigint unsigned NOT NULL,
  `servicio_extraordinario` tinyint(1) NOT NULL DEFAULT '0',
  `abnegacion` tinyint(1) NOT NULL DEFAULT '0',
  `medalla_honor_3` tinyint(1) NOT NULL DEFAULT '0',
  `medalla_honor_2` tinyint(1) NOT NULL DEFAULT '0',
  `medalla_honor_1` tinyint(1) NOT NULL DEFAULT '0',
  `vittorio_cucchini` tinyint(1) NOT NULL DEFAULT '0',
  `promesa` tinyint(1) NOT NULL DEFAULT '0',
  `juramento` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reconocimientos_voluntario_hoja_vida_anual_id_unique` (`hoja_vida_anual_id`),
  CONSTRAINT `reconocimientos_voluntario_hoja_vida_anual_id_foreign` FOREIGN KEY (`hoja_vida_anual_id`) REFERENCES `hoja_vida_anual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconocimientos_voluntario`
--

LOCK TABLES `reconocimientos_voluntario` WRITE;
/*!40000 ALTER TABLE `reconocimientos_voluntario` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconocimientos_voluntario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riesgos_actividad`
--

DROP TABLE IF EXISTS `riesgos_actividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `riesgos_actividad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `analisis_contexto_id` bigint unsigned NOT NULL,
  `nombre_riesgo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `probabilidad` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `impacto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medidas_mitigacion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `riesgos_actividad_analisis_contexto_id_foreign` (`analisis_contexto_id`),
  CONSTRAINT `riesgos_actividad_analisis_contexto_id_foreign` FOREIGN KEY (`analisis_contexto_id`) REFERENCES `analisis_contexto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riesgos_actividad`
--

LOCK TABLES `riesgos_actividad` WRITE;
/*!40000 ALTER TABLE `riesgos_actividad` DISABLE KEYS */;
/*!40000 ALTER TABLE `riesgos_actividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_user_role_id_user_id_unique` (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_clave_unique` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sanciones_voluntario`
--

DROP TABLE IF EXISTS `sanciones_voluntario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sanciones_voluntario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoja_vida_anual_id` bigint unsigned NOT NULL,
  `tipo_sancion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date DEFAULT NULL,
  `resumen_sancion` text COLLATE utf8mb4_unicode_ci,
  `apelacion` text COLLATE utf8mb4_unicode_ci,
  `fecha_apelacion` date DEFAULT NULL,
  `decision_cig` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sanciones_voluntario_hoja_vida_anual_id_foreign` (`hoja_vida_anual_id`),
  CONSTRAINT `sanciones_voluntario_hoja_vida_anual_id_foreign` FOREIGN KEY (`hoja_vida_anual_id`) REFERENCES `hoja_vida_anual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sanciones_voluntario`
--

LOCK TABLES `sanciones_voluntario` WRITE;
/*!40000 ALTER TABLE `sanciones_voluntario` DISABLE KEYS */;
/*!40000 ALTER TABLE `sanciones_voluntario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_hoja_vida`
--

DROP TABLE IF EXISTS `solicitudes_hoja_vida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_hoja_vida` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoja_vida_anual_id` bigint unsigned NOT NULL,
  `voluntario_id` bigint unsigned NOT NULL,
  `tipo_registro` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registro_id` bigint unsigned DEFAULT NULL,
  `datos` json NOT NULL,
  `archivo_ids_conservados` json DEFAULT NULL,
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `solicitada_por` bigint unsigned DEFAULT NULL,
  `revisada_por` bigint unsigned DEFAULT NULL,
  `motivo_revision` text COLLATE utf8mb4_unicode_ci,
  `revisada_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitudes_hoja_vida_hoja_vida_anual_id_foreign` (`hoja_vida_anual_id`),
  KEY `solicitudes_hoja_vida_voluntario_id_foreign` (`voluntario_id`),
  KEY `solicitudes_hoja_vida_solicitada_por_foreign` (`solicitada_por`),
  KEY `solicitudes_hoja_vida_revisada_por_foreign` (`revisada_por`),
  KEY `solicitudes_hoja_vida_estado_voluntario_id_index` (`estado`,`voluntario_id`),
  CONSTRAINT `solicitudes_hoja_vida_hoja_vida_anual_id_foreign` FOREIGN KEY (`hoja_vida_anual_id`) REFERENCES `hoja_vida_anual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitudes_hoja_vida_revisada_por_foreign` FOREIGN KEY (`revisada_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `solicitudes_hoja_vida_solicitada_por_foreign` FOREIGN KEY (`solicitada_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `solicitudes_hoja_vida_voluntario_id_foreign` FOREIGN KEY (`voluntario_id`) REFERENCES `voluntarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_hoja_vida`
--

LOCK TABLES `solicitudes_hoja_vida` WRITE;
/*!40000 ALTER TABLE `solicitudes_hoja_vida` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_hoja_vida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_cargo`
--

DROP TABLE IF EXISTS `tipos_cargo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_cargo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_cargo_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_cargo`
--

LOCK TABLES `tipos_cargo` WRITE;
/*!40000 ALTER TABLE `tipos_cargo` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipos_cargo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `titulos_voluntario`
--

DROP TABLE IF EXISTS `titulos_voluntario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titulos_voluntario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hoja_vida_anual_id` bigint unsigned NOT NULL,
  `archivo_id` bigint unsigned DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entregado_por` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_titulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `titulos_voluntario_hoja_vida_anual_id_foreign` (`hoja_vida_anual_id`),
  KEY `titulos_voluntario_archivo_id_foreign` (`archivo_id`),
  CONSTRAINT `titulos_voluntario_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `archivos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `titulos_voluntario_hoja_vida_anual_id_foreign` FOREIGN KEY (`hoja_vida_anual_id`) REFERENCES `hoja_vida_anual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `titulos_voluntario`
--

LOCK TABLES `titulos_voluntario` WRITE;
/*!40000 ALTER TABLE `titulos_voluntario` DISABLE KEYS */;
/*!40000 ALTER TABLE `titulos_voluntario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo_notificaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voluntarios`
--

DROP TABLE IF EXISTS `voluntarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voluntarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `filial_id` bigint unsigned NOT NULL,
  `registro_filial` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nacionalidad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_incorporacion` date DEFAULT NULL,
  `nivel_escolaridad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocupacion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupo_sanguineo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo_electronico` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domicilio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enfermedades` text COLLATE utf8mb4_unicode_ci,
  `alergias` text COLLATE utf8mb4_unicode_ci,
  `contacto_emergencia_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_emergencia_numero` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voluntarios_filial_id_registro_filial_unique` (`filial_id`,`registro_filial`),
  UNIQUE KEY `voluntarios_rut_unique` (`rut`),
  UNIQUE KEY `voluntarios_user_id_unique` (`user_id`),
  CONSTRAINT `voluntarios_filial_id_foreign` FOREIGN KEY (`filial_id`) REFERENCES `filiales` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `voluntarios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voluntarios`
--

LOCK TABLES `voluntarios` WRITE;
/*!40000 ALTER TABLE `voluntarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `voluntarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-24 11:34:06
