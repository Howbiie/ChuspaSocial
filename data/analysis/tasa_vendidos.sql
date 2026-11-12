-- =========================================================
-- Consulta para medir metricas de exito: tasa de libros vendidos por mes
-- Tasa = libros vendidos en el mes / libros publicados en el mes
-- Solo devuelve agregados mensuales: no expone datos personales.
--
-- Supuestos (nombres genericos, ajustar al esquema real):
--   tabla_libros(fecha_publicacion, fecha_venta)
--   fecha_venta es NULL mientras el libro no se ha vendido.
-- =========================================================

SELECT
    anio,
    mes,
    SUM(es_publicado) AS libros_publicados,
    SUM(es_vendido) AS libros_vendidos,
    CASE
        WHEN SUM(es_publicado) = 0 THEN 0.00
        ELSE ROUND(SUM(es_vendido) * 1.0 / SUM(es_publicado), 2)
    END AS tasa_vendidos
FROM (
    SELECT
        EXTRACT(YEAR FROM fecha_publicacion) AS anio,
        EXTRACT(MONTH FROM fecha_publicacion) AS mes,
        1 AS es_publicado,
        0 AS es_vendido
    FROM tabla_libros

    UNION ALL

    SELECT
        EXTRACT(YEAR FROM fecha_venta) AS anio,
        EXTRACT(MONTH FROM fecha_venta) AS mes,
        0 AS es_publicado,
        1 AS es_vendido
    FROM tabla_libros
    WHERE fecha_venta IS NOT NULL
) AS movimientos
GROUP BY
    anio,
    mes
ORDER BY
    anio,
    mes;

/*
=========================================
EJEMPLO DE RESULTADO (Criterio de aceptacion)
=========================================
anio | mes | libros_publicados | libros_vendidos | tasa_vendidos
----------------------------------------------------------------
2026 | 7   | 40                | 12              | 0.30
2026 | 8   | 55                | 31              | 0.56
2026 | 9   | 48                | 29              | 0.60
=========================================
*/