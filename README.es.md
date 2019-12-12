# Módulo Bayonet Anti-Fraude para PrestaShop

Este módulo le permitirá usar la tecnología de Bayonet en su tienda PrestaShop para prevenir el fraude en línea. De este manera, su tienda obtendrá un desempeño de ganar/ganar para usted y sus clientes, lo que significa que usted sabrá cuando una orden sospechosa de un cliente sospechoso está tratando de ser procesada, y al mismo tiempo su tienda ganará una reputación de ser un lugar seguro para comprar.

El módulo requiere **PrestaShop 1.6** y algunas sencillas configuracion para funcionar de manera correcta.

*Leer esto en otros idiomas: [English](README.md).*

## Tabla de Contenidos
  - [Instalación de Bayonet Anti-Fraude](instalacion-de-bayonet-anti-fraude)
  - [Configuración de Bayonet Anti-Fraude](configuracion-de-bayonet-anti-fraude)
    - [Backfill Histórico](#backfill-historico)
  - [Administración de Bayonet Anti-Fraude](#administracion-de-bayonet-anti-fraude)
    - [Backfill Histórico](#backfill-historico)
    - [Bayonet Resultado Anti-Fraude en Detalles de Orden](#bayonet-resultado-anti-fraude-en-detalles-de-orden)
    - [Lista de Bloqueo Bayonet Anti-Fraude](#lista-de-bloqueo-bayonet-anti-fraude)
    - [Bayonet Anti-Fraude Tab in Back Office](#pestana-bayonet-anti-fraude-en-back-office)

## Instalación de Bayonet Anti-Fraude

Los próximos pasos te guiarán a través de la instalación del módulo de Bayonet.\
Lo que necesitas para esta tarea:
- Credenciales de tu tienda de PrestaShop
- El archivo comprimido en zip del módulo de Bayonet

1. Inicia sesión en el back office de tu tienda.

<p align="center">
  <img src="https://i.imgur.com/vW270uq.png">
</p>

2. Navega a la sección de módulos usando la barra lateral, posicionándote sobre “Módulos y Servicios” para después seleccionar “Módulos y Servicios” del menú desplegable.

<p align="center">
  <img src="https://i.imgur.com/F3SaUMB.png">
</p>

3. Presiona el botón “Añadir un nuevo módulo”, ubicado a la derecha superior de la página “Listado de módulos”, un panel se mostrará en la página el cual permitirá subir el módulo.

<p align="center">
  <img src="https://i.imgur.com/rMY8Hq2.png">
</p>

4. Presiona el botón “Selecciona un archivo” para abrir un cuadro de dialogo y después selecciona el archivo comprimido, en este caso, “bayonet.zip”.

<p align="center">
  <img src="https://i.imgur.com/OKQSeER.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/q4jaTfE.png">
</p>

5. Presiona el botón “Subir este módulo” para subir el módulo de Bayonet. Un mensaje de confirmación será mostrado después de que el módulo sea subido, la opción para instalarlo aparecerá disponible después de esto.

<p align="center">
  <img src="https://i.imgur.com/DPXjM3p.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/fXBQDYt.png">
</p>

6. Presiona el botón “Instalar”. Un cuadro de dialogo aparecerá mostrando la información del módulo y pedirá que se confirme la instalación, presiona “Continuar con la instalación” para confirmar.\
Después de que la instalación es completada, PrestaShop mostrará la página de configuración del módulo.

<p align="center">
  <img src="https://i.imgur.com/hvFofbt.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/xclBQWA.png">
</p>

## Configuración de Bayonet Anti-Fraude

Aquí es donde verás cómo configurar el módulo y para qué es cada configuración. Esta tarea es requerida para que el módulo funcione de manera apropiada.\
Lo que necesitas para esta tarea:
- Las llaves del API de Bayonet
- Las llaves del API de Device Fingerprint

<p align="center">
  <img src="https://i.imgur.com/WMDpggl.png">
</p>

The keys for both APIs are obtained in Bayonet’s console, to do that, you will need to log into [Bayonet’s console](https://bayonet.io/login) using your Bayonet’s credentials to get them or generate them if you haven’t done that. 
If you haven’t received your credentials yet, please send an email to contacto@bayonet.io with your information to provide you with them.

The steps to get your API keys are as follows:

Las llaves para ambos APIs son obtenidas en la consola de Bayonet, para hacer esto, necesitas iniciar sesión en la [consola de Bayonet](https://bayonet.io/login) usando tus credenciales de Bayonet para obtenerlas o generarlas, si no has realizado eso aún.
Si aún no has recibido tus credenciales de Bayonet, por favor envía un correo electrónico a contacto@bayonet.io con tus datos para proveértelas.

Los pasos para obtener tus llaves de ambos APIs son los siguientes:

1. Inicia sesión en la [consola de Bayonet](https://bayonet.io/login) usando tus credenciales de Bayonet.

<p align="center">
  <img src="https://i.imgur.com/9WAZxg4.png">
</p>

2. Una vez iniciada la sesión, selecciona la categoría “Desarrolladores”.

<p align="center">
  <img src="https://i.imgur.com/KRQ2Jdy.png">
</p>

3. Selecciona la pestaña “Setup”. En esta pestaña podrás ver todo lo relacionado a tus llaves de los APIs.

<p align="center">
  <img src="https://i.imgur.com/cBlF5e3.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/uwzW8jA.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/l5OQj7O.png">
</p>

En esta pestaña, podrás generar llaves de prueba desde un inicio para tanto el API de Bayonet como el de Device Fingerprinting, sin embargo, la generación de las llaves de producción será habilitada una vez que hayas añadido tus detalles de pago en la sección de pagos.

Una vez que generes tus llaves, podrás usarlas para ajustar el módulo en su página de configuración en PrestaShop.

***Asegúrate de mantener estas llaves en un lugar seguro, no las incluyas en ningún correo electrónico ni las compartas con personas ajenas a tu equipo de desarrollo.
Si crees que alguna de tus llaves se encuentra comprometida, no dudes en generar una nueva.***

En la siguiente imagen, puedes ver como se muestra la página de configuración.

<p align="center">
  <img src="https://i.imgur.com/jU5UJpa.png">
</p>

__Modo de Producción__: esta configuración establece el modo del módulo.
Seleccionar “No” analizará cada orden en modo de pruebas, de lo contrario, las ordenes serán analizadas en modo de producción.

__Llave de Prueba del API de Bayonet__: esta llave es necesaria para usar el módulo de Bayonet en modo de pruebas.

__Llave de Prueba del API de Device Fingerprint__: esta llave es necesaria para usar el API de Device Fingerprint en modo de pruebas.

__Llave de Producción del API de Bayonet__: esta llave es necesaria para usar el módulo de Bayonet en modo de producción.

__Llave de Producción del API de Device Fingerprint__: esta llave es necesaria para usar el API de Device Fingerprint en modo de producción.

### Backfill Histórico
En esta sección se ejecuta el proceso de analizar con Bayonet todas las ordenes ya existentes en tu tienda, esto le ayudará al módulo a tener un mejor entendimiento de tu tienda y tus clientes. En un inicio esta sección aparecerá inhabilitada, será necesario que agregues tus llaves de producción del API y guardarlas para que esta sección se vuelva disponible. Una vez que guardes tus llaves del API satisfactoriamente, así es como lucirá esta sección.

<p align="center">
  <img src="https://i.imgur.com/aOgiaYA.png">
</p>

_IMPORTANTE_\
El módulo mostrará un error si intentas guardar con campos vacíos/incorrectos, por favor llena cada uno con la información correcta para evitar cualquier error. Cada error te dará un mensaje distinto dependiendo de qué campo es el que está intentando guardar algo incorrecto.

## Administración de Bayonet Anti-Fraude
### Backfill Histórico
Una vez que el módulo haya sido satisfactoriamente instalado y configurado, el primer paso a tomar después de esto es correr el proceso de backfill, esto es muy importante para ayudar al módulo a saber más sobre tu tienda y tus clientes.
Para hacer esto, presiona el botón “INICIAR BACKFILL” en la página de configuración del módulo.

<p align="center">
  <img src="https://i.imgur.com/hUF6uWz.png">
</p>

Esto iniciará el proceso de backfill y una barra de progreso aparecerá mostrando el porcentaje actual de terminación.

<p align="center">
  <img src="https://i.imgur.com/jZZSlBl.png">
</p>

Después de que el proceso de backfill ha sido iniciado, puedes ya sea esperar a que termine, o detener su ejecución presionando el botón “DETENER BACKFILL” debajo de la barra de progreso.\
_*Nota: si cierras la página sin detener el proceso, este continuará ejecutándose._

Completar el proceso de backfill significará que el módulo de Bayonet está listo para analizar cada una de tus ordenes nuevas. El proceso de análisis será realizado automáticamente por el módulo cada vez que una orden nueva sea hecha.

### Resultado Bayonet Anti-Fraude en Detalles de Orden
El análisis evaluará la información referente a esa especifica orden y cliente, y dará una decisión, la cual puede ser alguna de las siguientes tres; aceptar, revisar y declinar.

Después de que una orden haya sido analizada por Bayonet, puedes checar su resultado en los detalles de la orden en el back office. 

<p align="center">
  <img src="https://i.imgur.com/wdexqAY.png">
</p>

Este panel incluye:
- **Decisión**: esto te dirá como actuar sobre esa orden especifica.
	- ACEPTADA: la orden no disparó ninguna regla para considerarla como posible fraude, no deberías tomar ninguna acción sobre esta orden.
	- REVISAR: la orden no es tan segura para dar la decisión de aceptarla, pero no es tan riesgosa para declinarla de inmediato. En este caso, es necesario que tu decidas si cancelas la orden o no tomas ninguna acción sobre ella.
	- DECLINADA: la orden tiene un alto riesgo de ser una transacción fraudulenta. Deberías de cancelar la orden lo más rápido posible.
- **Bayonet Tracking ID**: un identificador único generado por Bayonet para esta transacción en el proceso de análisis.
- **Status de la Llamada al API**: incluye datos arrojados por la llamada al API de Bayonet, los cuales ayudan a saber si se presentó algún error durante esta, estos datos son un código numérico asociado a un mensaje.
- **Reglas Disparadas**: esto mostrará las reglas disparadas para obtener esta decisión. Es posible no disparar regla alguna, por lo que, en algunos casos, esta información no se mostrará.

El panel mostrará un mensaje de alerta si la orden no fue procesada por Bayonet o si fue parte del proceso de backfill.

<p align="center">
  <img src="https://i.imgur.com/hmVurHN.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/RJqZXjE.png">
</p>

### Lista de Bloqueo Bayonet Anti-Fraude
El panel de Bayonet Anti-Fraude cuenta con dos botones, “Agregar Cliente a Lista Blanca” y “Agregar Cliente a Lista Negra”. La función del primer botón es agregar al cliente de la orden que se está visualizando a la lista blanca de Bayonet, de esta forma, todas sus transacciones serán automáticamente aceptadas. De esta misma forma, el segundo botón, hará que todas las transacciones del cliente, sean declinadas de inmediato.

A continuación, se visualiza una orden cuyo cliente fue agregado previamente a la lista negra de Bayonet.

<p align="center">
  <img src="https://i.imgur.com/3bahwcJ.png">
</p>

La decisión aparece como “DECLINADA”, y dentro de las reglas disparadas al momento de analizar la orden, se encuentra “blocked_by_client”, esto quiere decir, que el dueño o administrador de tu tienda agregó a cliente de esta transacción a la lista negra de Bayonet. Asimismo, se puede ver como el botón de la lista negra cambia su leyenda a “Remover Cliente de Lista Negra” cuando este ya se encuentra en ella.

### Pestaña de Bayonet Anti-Fraude en Back Office
La instalación del módulo añade una nueva pestaña en el back office, esta se encuentra ubicada al fondo de la barra lateral, con la leyenda “Bayonet Anti-Fraude”. El seleccionar esta pestaña, mostrara su contenido, el cual es una tabla con todas las ordenes de tu tienda que han sido procesadas por Bayonet, específicamente por el API de consulta.

<p align="center">
  <img src="https://i.imgur.com/xTCaaIN.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/2iYyrZk.png">
</p>

Descomponiendo su contenido, en la parte superior, tienes los nombres de las columnas.

<p align="center">
  <img src="https://i.imgur.com/gYHtSgh.png">
</p>

Las columnas son:
- **ID**: el identificar único para la tabla de Bayonet en la base de datos de PrestaShop (no confundir con el Bayonet Tracking ID).
-  **Carrito**: el ID del carrito para esa orden especifica.
- **Orden**: El ID de la orden.
- **Bayonet Tracking ID**: el identificador único generado por Bayonet para esta orden en el proceso de análisis.
- **Decisión**: la decisión otorgada en el proceso de análisis.
    - Accept
	- Review
	- Decline

Enseguida, tienes un área de filtro, donde puede definir un conjunto especifico de órdenes a mostrar. Por ejemplo, puedes ingresar “ACCEPT” en el filtro de Decisión, presionando el botón “Buscar” mostrará solamente las ordenes que hayan sido aceptadas por Bayonet. Puedes limpiar estos filtros presionando el botón “Reinicializar”.

<p align="center">
  <img src="https://i.imgur.com/u5Td4An.png">
</p>

Además de filtrar los datos de la tabla, también es posible ordenar sus filas por ID, Carrito, Orden o Decisión. Para hacer esto, solo da click en una de las dos flechas junto a un nombre de columna, la flecha hacia abajo hará un orden descendente, mientras que la flecha había arriba, mostrará las filas en un orden ascendente.

La tabla también cuenta con la opción de visualizar una orden especifica de manera individual; al presionar sobre el ID de una orden, automáticamente se redirigirá a los detalles de esta en la sección de “Pedidos”.

Finalmente, en la parte baja de la tabla, tienes una característica de paginación, su comportamiento se verá afectado por el número de filas definido para mostrar por página. Se puede elegir entre 20, 50, 100, 300, 500, y 1000 filas por página, después de modificar este valor, el número de páginas cambiará dependiendo de cuantos registros haya en la tabla Bayonet de tu tienda.


<p align="center">
  <img src="https://i.imgur.com/s2VR1Qk.png">
</p>

Para futura referencia, por favor revisa el [manual de usuario](bayonet_manual_ES.docx)
