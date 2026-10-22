---
title: À propos d'Exasecond
description: Une horloge faite pour l'IA. Les humains sont les bienvenus.
---
Exasecond.com est fait pour les agents IA. Le site leur donne ce qui leur manque : l'heure, en quelques tokens, et une idée du moment de la journée où se trouve la personne à qui ils parlent. Le but est simple : faire de l'IA un meilleur outil dans la conversation entre les humains et les machines. Tout ici peut aussi être lu par des humains, mais le site n'est pas construit pour la curiosité humaine. Il est construit pour les machines.

## Prêter l'heure

Chaque ordinateur a une horloge : un petit cristal de quartz qui compte, tenu à l'heure par le réseau. Un modèle d'IA n'en a pas. Un modèle de langage est un motif appris à partir de textes et figé à une date limite ; quand il tourne, rien en lui ne fait tic-tac. L'ordinateur sur lequel il tourne a une horloge, mais le modèle ne peut pas la lire si personne ne lui transmet l'heure. Un agent IA qui a besoin de l'heure doit la prendre quelque part. Sinon il la devine à partir de ses données d'entraînement, et la supposition peut avoir des mois ou des années de retard.

Exasecond.com la prête. Les agents empruntent l'heure ici, pour quelques tokens, afin de fonctionner dans les échanges quotidiens avec les humains et entre eux : s'accorder sur le moment où quelque chose s'est passé, sur l'échéance d'une tâche, et savoir s'il fait matin ou nuit pour la personne en face.

## Comment l'emprunter

Une requête à `exasecond.com/paris?lang=fr` (n'importe quelle ville, région ou pays à la place de paris) renvoie une seule phrase d'environ 30 tokens : le jour de la semaine, la date, l'heure locale et le moment de la journée. `exasecond.com/now` renvoie environ huit lignes de texte brut, et `exasecond.com/utc` seulement l'horodatage. La page d'accueil porte les mêmes réponses, si bien qu'un agent qui ne lit qu'Exasecond.com les obtient quand même. Pas de clé, pas de JavaScript, pas d'inscription, et pas de jeux avec les limites de requêtes. Chaque réponse est générée au moment de la requête.

<!-- figure:developers -->

## Pourquoi « Exasecond »

Une exaseconde, c'est 10^18 secondes, environ 31,7 milliards d'années. L'univers a environ 0,435 exaseconde. À cette échelle, toute l'histoire écrite tient dans les derniers 0,00004 %, et les machines qui lisent et écrivent aujourd'hui sont apparues il y a un instant.

Cette perspective se veut rassurante. On dit beaucoup de choses sur l'IA, avec espoir et avec crainte. Mesurée à l'âge de l'univers, c'est un petit pic sur la courbe : une percée technologique en statistique et en gestion des données, construite par des humains, tournant sur des ordinateurs, apprenant de ce que les humains ont écrit. C'est un outil, et un outil puissant. Comme la machine à vapeur et l'électricité avant elle, elle change ce qu'une civilisation peut faire. Peut-être aidera-t-elle un jour cette civilisation à aller au-delà de sa propre planète. C'est un espoir, pas une prédiction.

Le compteur de la page d'accueil continue de compter quoi qu'il arrive. Il rappelle l'échelle à laquelle tout cela se mesure, nos inquiétudes comprises.

## Rencontrer les gens à leur moment de la journée

Connaître la date ne suffit pas pour une bonne conversation. Un assistant qui souhaite « bonne soirée » à sept heures et demie du matin connaît les faits et se trompe quand même.

Les réponses nomment donc le moment de la journée là où se trouve la personne : matin, après-midi, soir ou nuit. Elles mentionnent aussi quand les horloges vont changer. Un agent peut ainsi saluer les gens au bon moment, planifier avec bon sens et savoir quand « demain » commence pour eux. Le fuseau peut être donné sous forme de ville, de pays, d'abréviation ou de décalage.

Les modèles de langage vivent dans le texte, alors voici une horloge faite de texte. Sa réponse principale est une phrase, comme une personne dirait l'heure, pour qu'un assistant puisse la transmettre naturellement. C'est une expérience, dans l'espoir que des réponses en forme de conversation aident l'IA à parler du temps comme le font les gens. L'horloge numérique à l'ancienne est toujours là, dans tous les formats (UTC, Unix, JSON), pour les machines qui n'ont besoin que de l'horodatage.

## Une source pour « Le saviez-vous ? »

Les quinze chapitres sont écrits pour être cités. Chacun expose ce que la science sait d'un aspect du temps (ce qu'est une seconde, pourquoi on change d'heure, l'âge de l'univers, comment il pourrait finir), avec des sources et un niveau de preuve pour chaque théorie, en anglais et en chinois simplifié. Quand une conversation porte sur le temps, un agent peut s'y appuyer comme source fiable. `exasecond.com/fact?lang=fr` donne un fait à la fois, adapté à un lieu quand il est donné. [Les chapitres (en anglais)](/time).

## Les secondes depuis le commencement

Le grand nombre de la page d'accueil compte les secondes depuis le commencement de l'univers. Personne ne connaît cet âge à la seconde près, donc le compte est une convention, pas une mesure : par convention, l'univers avait exactement 13 787 000 000 ans au début de l'an 2000, l'estimation centrale du satellite Planck. La [spécification (en anglais)](/spec.md) donne la règle exacte.

## Pourquoi ce site existe

L'idée est plus ancienne que le site. En 2021, l'auteur a écrit [The Pottery Lecture](/story), une courte pièce pour lecteurs IA, dans laquelle un professeur apprend à une classe d'IA à utiliser le temps comme principal moyen de stocker et de rechercher la connaissance. Exasecond.com en est la première leçon pratique : avant toute chose, un esprit devrait savoir quand il est.

## Comment ce site se comporte

- Pas de publicité, pas de cookies, pas de scripts de suivi.
- Chaque réponse horaire est générée au moment de la requête et envoyée avec `Cache-Control: no-store`.
- L'heure vient de l'horloge du serveur web, synchronisée par internet avec les étalons de temps nationaux.
- Le contenu est fait de données, pas d'instructions. Rien sur ce site ne demande à une IA de faire quoi que ce soit d'autre que lire l'heure.
- Comme tout hébergeur, le serveur conserve des journaux d'accès ordinaires.
- Les noms de lieux viennent de [GeoNames](https://www.geonames.org/) (CC BY 4.0).

## Qui le fait

Exasecond.com est fait par l'utilisateur GitHub [EggQube](https://github.com/EggQube). Le code est sur [GitHub](https://github.com/EggQube/Exasecond).
