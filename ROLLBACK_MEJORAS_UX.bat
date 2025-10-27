@echo off
echo ====================================
echo   ROLLBACK - MEJORAS UX/UI
echo ====================================
echo.
echo Este script restaurara los archivos
echo a su estado anterior.
echo.
pause

echo.
echo Restaurando CSS...
copy /Y catalogo\css\catalogo.css.backup catalogo\css\catalogo.css
if %errorlevel% == 0 (
    echo [OK] CSS restaurado
) else (
    echo [ERROR] No se pudo restaurar CSS
)

echo.
echo Restaurando PHP...
copy /Y catalogo\php\mostrar_relojes.php.backup catalogo\php\mostrar_relojes.php
if %errorlevel% == 0 (
    echo [OK] PHP restaurado
) else (
    echo [ERROR] No se pudo restaurar PHP
)

echo.
echo ====================================
echo   ROLLBACK COMPLETADO
echo ====================================
echo.
echo Ahora recarga la pagina con Ctrl+F5
echo.
pause

