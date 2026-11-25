# Bezares Chat: plugin de WordPress

Plugin **mínimo** (un solo archivo PHP) que muestra el icono flotante del chat de [Bezares Chat](https://github.com/igorneumann/Bezares-chat) en la web.

- No crea tablas ni guarda mensajes en WordPress: solo guarda una opción con 4 ajustes.
- Solo añade una línea `<script>` al pie de página. Todo lo demás (estilos, textos, mensajes) llega desde `chat.bezares.com`.

## Instalación

1. Descarga este repositorio como ZIP (*Code → Download ZIP*).
2. En WordPress: *Plugins → Añadir nuevo → Subir plugin*, elige el ZIP y actívalo.
3. Ve a *Ajustes → Bezares Chat* y configura:
   - **Servidor de chat**: `https://chat.bezares.com`;
   - **Esquina del icono**: inferior derecha (por defecto), inferior izquierda, superior derecha o superior izquierda;
   - **Separación del borde** en píxeles;
   - **Mostrar en**: ordenadores (sobremesa y portátiles), tablets o móviles. El navegador no distingue un portátil de un sobremesa.
4. Desactiva Crisp.

Mensajes, horario, WhatsApp, usuarios y estadísticas se configuran en `https://chat.bezares.com/admin`.

## Requisitos

WordPress 5.0+ y PHP 7.0+.
