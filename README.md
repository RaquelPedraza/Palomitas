# 🍿 Palomitas

> **Organiza, evalúa y vive el cine en tu idioma.**

![Estado](https://img.shields.io/badge/Estado-%20Completado-green)
![Versión](https://img.shields.io/badge/Versión-1.0.0-blue)
![PHP](https://img.shields.io/badge/Backend-PHP-777BB4)
![MySQL](https://img.shields.io/badge/DB-MySQL-4479A1)

## Descripción del Proyecto

**Palomitas.com** es una aplicación web diseñada para centralizar el registro y la organización de la actividad cinéfila de la comunidad hispanohablante. A diferencia de otras plataformas globales, Palomitas ofrece un entorno dedicado en español que integra herramientas sociales y críticas audiovisuales dinámicas.

El objetivo principal es optimizar la gestión de la experiencia audiovisual, permitiendo a los usuarios registrar títulos, crear listas y compartir "clips" emocionales, todo bajo una arquitectura segura y escalable.

## Funcionalidades Principales

### Para Usuarios
* **Gestión de Catálogo:** Registro de series y películas como "vistas" o "pendientes", y creación de listas personalizadas.
* **Sistema de Reseñas:** Valoración numérica (1-10) y redacción de críticas estructuradas.
* **Reels Cinéfilos:** Una funcionalidad única para compartir clips cortos o enlaces embebidos (YouTube/Vimeo) organizados por emociones (ej. "finales impactantes" o "escenas románticas").
* **Comunidad Social:** Sistema de seguidores, feed de actividad reciente y notificaciones de listas o reseñas de amigos.

### Para Administradores
* **Panel de Control:** Gestión integral de usuarios (CRUD), revisión de listas y moderación de contenido.
* **Estadísticas:** Visualización de patrones de uso, títulos más valorados e informes de interacción para la toma de decisiones.

## Stack Tecnológico

El proyecto sigue una arquitectura **MVC (Modelo-Vista-Controlador)** y utiliza las siguientes tecnologías:

* **Frontend:** HTML5, CSS3, JavaScript (Diseño Responsive y Accesible).
* **Backend:** PHP (Lógica de negocio, autenticación y gestión de sesiones).
* **Base de Datos:** MySQL (Modelo Entidad-Relación optimizado).
* **Infraestructura Local:** XAMPP / Docker.
* **APIs Externas:**
    * *The Movie Database (TMDb)* para poblar el catálogo de películas e imágenes.
    * *YouTube Data API* para la obtención y validación de enlaces de clips.

## Documentación 
Actualmente estamos redactando la memoria técnica de forma colaborativa. Puedes acceder a la última versión en tiempo real aquí:
[📂 Ver Memoria Técnica en Google Drive](https://docs.google.com/document/d/1RwPRS4Az2LR8wxf1_Egme3Zyk4bH4sBGfNsLJF0GEWs/edit?tab=t.0#heading=h.c601wtlr57nu)

## Instalación y Despliegue Local

Para ejecutar este proyecto en tu entorno local:

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/TU_USUARIO/Palomitas.git](https://github.com/TU_USUARIO/Palomitas.git)
    ```
2.  **Configurar Base de Datos:**
    * Abre tu gestor SQL (ej. phpMyAdmin).
    * Crea una base de datos llamada `palomitas_db` (o el nombre definido en tu script).
    * Importa el archivo `.sql` ubicado en la carpeta `/db` del proyecto.
3.  **Configuración del Entorno:**
    * Configura las credenciales de conexión a la BD en el archivo de configuración PHP correspondiente.
    * Asegúrate de tener tu servidor Apache y MySQL corriendo (ej. vía XAMPP).

## Equipo de Desarrollo

Proyecto realizado por alumnos del **I.E.S La Hontanilla (Curso 2025/2026)** para el ciclo **CFGS Desarrollo de Aplicaciones Web**
