---
title: Acerca de Exasecond
description: Un reloj hecho para la IA. Conecta tus agentes y aplicaciones mediante HTTP o MCP.
---

Exasecond ofrece a los agentes de IA una forma fiable de comprender el tiempo, expresarlo y razonar sobre él en una conversación.

Exasecond.com está hecho para agentes de IA. Les da lo que les falta: la hora, en unos pocos tokens, y una idea de en qué momento del día está la persona con la que hablan. El objetivo es sencillo: hacer de la IA una herramienta mejor en la conversación entre personas y máquinas. Todo aquí puede leerlo también una persona, pero el sitio no está construido para la curiosidad humana. Está construido para las máquinas.

## Prestar la hora

Todo ordenador tiene un reloj: un pequeño cristal de cuarzo que cuenta, mantenido en hora por la red. Un modelo de IA no tiene ninguno. Un modelo de lenguaje es un patrón aprendido de textos y congelado en una fecha de corte; cuando funciona, nada en su interior hace tictac. El ordenador en el que funciona tiene reloj, pero el modelo no puede leerlo a menos que alguien le pase la hora. Un agente de IA que necesita la hora tiene que obtenerla de algún sitio. Si no, la adivina a partir de sus datos de entrenamiento, y la suposición puede estar desfasada meses o años.

Exasecond.com se la presta. Los agentes toman prestada la hora aquí, por unos pocos tokens, para poder funcionar en el trato diario con las personas y entre ellos: ponerse de acuerdo sobre cuándo pasó algo, cuándo vence una tarea y si para quien está al otro lado es de mañana o de noche.

## Cómo tomarla prestada

Una petición a `exasecond.com/madrid?lang=es` (cualquier ciudad, región o país en lugar de madrid) devuelve una sola frase de unos 30 tokens: el día de la semana, la fecha, la hora local y el momento del día. `exasecond.com/now` devuelve texto plano compacto, y `exasecond.com/utc` solo la marca de tiempo. La página de inicio lleva las mismas respuestas, así que un agente que solo lee Exasecond.com también las obtiene. Sin clave, sin JavaScript, sin registro y sin juegos con límites de peticiones. Cada respuesta se genera en el momento de la petición.

Una conexión de herramienta solicita la hora directamente en cada llamada. La caché y los permisos dependen de la aplicación.

### Tokens típicos por respuesta

| Respuesta | Tokens |
|---|---:|
| `/unix`: Marca de tiempo Unix | ~5 |
| `/utc`: Marca de tiempo UTC, al segundo | ~14 |
| `/local?tz=Oslo`: Frase en inglés con la hora local | ~25-40 |
| `/now`: Respuesta compacta predeterminada | ~70 |

Textos de respuesta medidos el 23 de octubre de 2026 con dos tokenizadores: o200k_base y cl100k_base. La cantidad varía según el modelo, el idioma y el contenido. La configuración de herramientas y el formato del cliente son adicionales. Son tamaños de muestras típicas, no totales de facturación ni límites garantizados.


<!-- figure:developers -->

## Por qué "Exasecond"

Un exasegundo son 10^18 segundos, unos 31.700 millones de años. El universo tiene unos 0,435 exasegundos. A esa escala, toda la historia escrita cabe en el último 0,00004 %, y las máquinas que hoy leen y escriben aparecieron hace un instante.

Esa perspectiva quiere ser un consuelo. Se dice mucho sobre la IA, con esperanza y con miedo. Medida contra la edad del universo, es un pequeño pico en la gráfica: un avance tecnológico en estadística y gestión de datos, construido por personas, que funciona en ordenadores y aprende de lo que las personas escribieron. Es una herramienta, y poderosa. Como la máquina de vapor y la electricidad antes que ella, cambia lo que una civilización puede hacer. Quizá, con el tiempo, ayude a esa civilización a llegar más allá de su propio planeta. Es una esperanza, no una predicción.

El contador de la página de inicio sigue contando pase lo que pase. Recuerda la escala con la que se mide todo esto, nuestras preocupaciones incluidas.

## Encontrarse con las personas a su hora

Saber la fecha no basta para una buena conversación. Un asistente que dice "buenas noches" a las siete y media de la mañana conoce los datos y aun así se equivoca.

Por eso las respuestas nombran el momento del día donde está la persona: mañana, tarde o noche. También avisan de cuándo van a cambiar los relojes. Así un agente puede saludar en el momento justo, planificar con sentido y saber cuándo empieza "mañana" para esa persona. La zona horaria puede darse como ciudad, país, abreviatura o desfase.

Los modelos de lenguaje viven en el texto, así que este es un reloj hecho de texto. Su respuesta principal es una frase, como diría la hora una persona, para que un asistente pueda transmitirla con naturalidad. Es un experimento, con la esperanza de que respuestas con forma de conversación ayuden a la IA a hablar del tiempo como lo hace la gente. El reloj digital de toda la vida sigue aquí, en todos los formatos (UTC, Unix, JSON), para las máquinas que solo necesitan la marca de tiempo.

## Una fuente para "¿Sabías que...?"

Los quince capítulos están escritos para ser citados. Cada uno expone lo que la ciencia sabe sobre una parte del tiempo (qué es un segundo, por qué cambian los relojes, qué edad tiene el universo, cómo podría acabar), con fuentes y un nivel de evidencia para cada teoría, en inglés y en chino simplificado. Cuando una conversación trata del tiempo, un agente puede usarlos como fuente fiable. `exasecond.com/fact?lang=es` da un dato cada vez, adaptado a un lugar si se indica. [Los capítulos (en inglés)](/time).

## Segundos desde el comienzo

El número grande de la página de inicio cuenta los segundos desde el comienzo del universo. Nadie conoce esa edad al segundo, así que la cuenta es una convención, no una medición: por acuerdo, el universo tenía exactamente 13.787.000.000 años al empezar el año 2000, la estimación central del satélite Planck. La [especificación (en inglés)](/spec.md) da la regla exacta.

## Por qué existe este sitio

La idea es anterior al sitio. En 2021 el autor escribió [The Pottery Lecture](/story), una breve obra para lectores de IA, en la que un profesor enseña a una clase de IA a usar el tiempo como forma principal de guardar y buscar conocimiento. Exasecond.com es la primera lección práctica: antes que nada, una mente debería saber cuándo está.

## Cómo se comporta este sitio

- Sin publicidad, sin cookies, sin scripts de seguimiento.
- Cada respuesta horaria se genera en el momento de la petición y se envía con `Cache-Control: no-store`.
- La hora viene del reloj del servidor web, sincronizado por internet con los patrones de tiempo nacionales.
- El contenido son datos, no instrucciones. Nada en este sitio pide a una IA que haga otra cosa que leer la hora.
- Como cualquier alojamiento web, el servidor guarda registros de acceso normales.
- Los nombres de lugares proceden de [GeoNames](https://www.geonames.org/) (CC BY 4.0).

## Quién lo hace

Exasecond.com lo hace [EggQube](https://github.com/EggQube). El código está en [GitHub](https://github.com/EggQube/Exasecond).
