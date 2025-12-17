# 📋 Guía de Importación de Estudiantes

## 📁 Archivos de Ejemplo

En la raíz del proyecto encontrarás dos archivos de ejemplo:

- **ejemplo_importar_estudiantes.csv** - Archivo CSV listo para usar
- **ejemplo_importar_estudiantes.xlsx** - Archivo Excel listo para usar

Ambos contienen 10 estudiantes de ejemplo con todas las columnas.

## 📊 Formato del Archivo

### Columnas Requeridas ✅
- **nombres** - Nombre(s) del estudiante
- **apellidos** - Apellidos del estudiante

### Columnas Opcionales 📝
- **dni** - Documento de identidad (8 dígitos)
- **fecha_nacimiento** - Formato: YYYY-MM-DD (ej: 2010-05-15)
- **direccion** - Dirección completa
- **telefono** - Teléfono de contacto
- **mencion** - Mención académica (ej: Ciencias, Letras, Humanidades)

## 🚀 Cómo Importar Estudiantes

### Paso 1: Ir a Importar Salón
1. Ve a **Estudiantes** en el menú
2. Haz clic en **Importar Salón**

### Paso 2: Completar el Formulario
1. **Docente** (*requerido*) - Selecciona el docente del salón
2. **Sección** (*requerido*) - Selecciona la sección
3. **Monto por estudiante** (*opcional*) - Ingresa el monto de pensión
4. **Archivo** (*requerido*) - Selecciona tu archivo CSV o Excel

### Paso 3: Ver Previsualización
- Haz clic en **Ver Previsualización**
- Revisa que los datos sean correctos
- Verás una tabla con todos los estudiantes a importar

### Paso 4: Subir Datos
- Si todo está correcto, haz clic en **Subir datos**
- Espera el mensaje de confirmación
- Verás cuántos estudiantes se importaron y cuántos se omitieron

## ✅ Mensajes de Resultado

### Importación Exitosa
```
✅ Importación exitosa: X estudiante(s) guardado(s). Y omitido(s) por DNI duplicado.
```

### Errores Comunes

**"Debe seleccionar docente y sección"**
- Asegúrate de seleccionar ambos campos antes de subir el archivo

**"No se encontraron filas válidas"**
- Verifica que el archivo tenga las columnas "nombres" y "apellidos"
- Asegúrate de que al menos una fila tenga datos

**"Para importar archivos Excel, instale phpoffice/phpspreadsheet"**
- Ejecuta: `composer require phpoffice/phpspreadsheet`

## 📝 Notas Importantes

- Los estudiantes con **DNI duplicado** se omiten automáticamente
- El sistema crea un nuevo **salón** automáticamente con los datos seleccionados
- Si no especificas **monto**, se asigna 0.00 por defecto
- Los estudiantes se crean con estado **activo** automáticamente

## 💡 Ejemplo de Archivo CSV

```csv
nombres,apellidos,dni,fecha_nacimiento,direccion,telefono,mencion
Juan Carlos,Pérez López,12345678,2010-05-15,Av. Principal 123,987654321,Ciencias
María Elena,García Torres,23456789,2010-08-20,Jr. Los Olivos 456,987654322,Letras
```

## 🔧 Solución de Problemas

### El archivo no se sube
- Verifica que el archivo sea .csv, .xlsx o .xls
- Asegúrate de que el tamaño no exceda el límite de PHP (generalmente 8MB)

### No aparecen los estudiantes
- Revisa que el archivo tenga encabezados en la primera fila
- Verifica que las columnas se llamen exactamente: nombres, apellidos (en minúsculas)

### Algunos estudiantes no se importaron
- Revisa el mensaje de confirmación para ver cuántos se omitieron
- Los omitidos generalmente tienen DNI duplicado con estudiantes existentes
