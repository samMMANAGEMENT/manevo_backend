# manevo Backend: Core Operations Engine

Este es el motor central de **manevo**, construido sobre **Laravel 11**. Proporciona una arquitectura robusta, segura y escalable para la automatización de procesos SaaS.

## 🏗 Arquitectura de Módulos

El backend sigue una estructura modular ubicada en `app/Http/Modules/`, diseñada para separar las responsabilidades de cada vertical de negocio:

*   **Billing**: Gestión de facturación electrónica. Comunicación directa con el API de DataInvoice y control de resoluciones DIAN.
*   **Inventory**: Lógica de productos, categorías y trazabilidad de movimientos.
*   **Services**: Gestión de órdenes de servicio, programación y cálculo de rendimientos operativos.
*   **Entity**: El núcleo multi-entidad que asegura que cada empresa tenga su propio entorno de datos aislado.

## 🔋 Características Técnicas
1.  **Multi-Tenancy Global**: Uso del trait `BelongsToEntity` para aplicar scopes automáticos por entidad en cada consulta.
2.  **API Sanctum**: Autenticación segura y ligera para la comunicación con el frontend.
3.  **Service-Controller Pattern**: Toda la lógica compleja reside en Servicios, manteniendo los Controladores limpios y enfocados en las peticiones.
4.  **DIAN Ready**: Integración completa para radicación de facturas electrónicas con soporte para PDF y XML.

## 🛠 Instalación y Uso
1.  Configurar el entorno en `.env`.
2.  Ejecutar migraciones y seeders básicos:
    ```bash
    php artisan migrate --seed
    ```
3.  Servir la aplicación:
    ```bash
    php artisan serve
    ```

---
© 2026 manevo • Precision in Motion
