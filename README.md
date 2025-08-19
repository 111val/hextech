# HEXTECH

Valentina Palacios, Tomás Fleitas, Cristofer Vela, Leonard Rubio

Título e imagen de portada;
Insignias;
Índice;
Descripción del Proyecto;
Estado del proyecto;
Demostración de funciones y aplicaciones;
Acceso al Proyecto;
Tecnologías utilizadas;
Personas Contribuyentes;
Personas Desarrolladoras del Proyecto;
Licencia.

VER: https://www.aluracursos.com/blog/como-escribir-un-readme-increible-en-tu-github


- Registrar y controlar el stock de cada producto.
- Registrar la composición de los kits (qué productos y cuántas unidades lleva cada kit).
- Al recibir un pedido, saber si hay stock suficiente para armar los kits solicitados.
- Generar alertas o reportes de faltantes.
- Manejar pedidos y actualizar el stock automáticamente.


Estructura final del proyecto:
tiendagimnasio/

├── index.php          <-- Mostrar productos y kits

├── conexion.php       <-- Conexión a MySQL

├── agregar.php        <-- Formulario para agregar productos

├── editar.php         <-- Editar producto existente

├── eliminar.php       <-- Eliminar producto

├── agregar_kit.php    <-- Crear kits

├── pedidos.php        <-- Gestionar pedidos (crear, listar)

├── editar_pedido.php  <-- Modificar pedidos existentes

├── eliminar_pedido.php <-- Eliminar pedidos (restaura stock)

├── ver_pedido.php     <-- Ver detalles de un pedido

├── reportes.php       <-- Reportes de faltantes y estadísticas

├── estilo.css         <-- Estilos

└── imagenes/          <-- Carpeta de imágenes
