---
title: Om Exasecond
description: En klocka för AI. Anslut dina agenter och appar via HTTP eller MCP.
---

Exasecond ger AI-agenter ett tillförlitligt sätt att förstå, uttrycka och resonera kring tid i samtal.

Exasecond.com är gjord för AI-agenter. Den ger dem det de saknar: tiden, i några få tokens, och en känsla för var på dygnet den människa de pratar med befinner sig. Målet är enkelt: att göra AI till ett bättre verktyg i samtalet mellan människor och maskiner. Allt här kan läsas av människor också, men sidan är inte byggd för mänsklig nyfikenhet. Den är byggd för maskiner.

## Att låna ut tiden

Varje dator har en klocka: en liten kvartskristall som räknar och hålls rätt av nätet. En AI-modell har ingen. En språkmodell är ett mönster inlärt ur text och fryst vid ett slutdatum; när den körs tickar ingenting inuti den. Datorn den körs på har en klocka, men modellen kan inte läsa av den om ingen lämnar över tiden. En AI-agent som behöver veta vad klockan är måste hämta det någonstans. Annars gissar den utifrån sin träningsdata, och gissningen kan vara månader eller år fel.

Exasecond.com lånar ut den. Agenter lånar tiden här, för några tokens, så att de klarar det dagliga samspelet med människor och med varandra: att vara överens om när något hände, när en uppgift ska vara klar och om det är morgon eller natt för den på andra sidan.

## Så lånar man den

En förfrågan till `exasecond.com/stockholm?lang=sv` (vilken stad, region eller vilket land som helst i stället för stockholm) ger en enda mening på ungefär 30 tokens: veckodagen, datumet, den lokala tiden och tiden på dygnet. `exasecond.com/now` ger kort ren text, och `exasecond.com/utc` bara tidsstämpeln. Startsidan har samma svar, så en agent som bara läser Exasecond.com får dem ändå. Ingen nyckel, ingen JavaScript, ingen registrering och inga spel med begränsningar av antalet förfrågningar. Varje svar skapas i ögonblicket för förfrågan.

En verktygsanslutning hämtar tiden direkt vid varje anrop. Cache och behörighetsfrågor beror på appen.

### Typiskt antal token per svar

| Svar | Byte | Token |
|---|---:|---:|
| `/unix`: Unix-tidsstämpel | 11 | ~5 |
| `/utc`: UTC-tidsstämpel, på sekunden | 21 | ~14 |
| `/local?tz=Oslo`: Lokal tid i en engelsk mening | ~70-120 | ~25-40 |
| `/now`: Kort standardsvar | ~80 | ~35 |

Svarstexter uppmätta den 1 november 2026 med två tokeniserare: o200k_base och cl100k_base. Antalet varierar med modell, språk och innehåll. Verktygsinställningar och klientens inramning tillkommer. Typiska exempelstorlekar, inte faktureringstotaler eller garanterade gränser.


<!-- figure:developers -->

## Varför "Exasecond"

En exasekund är 10^18 sekunder, ungefär 31,7 miljarder år. Universum är ungefär 0,435 exasekunder gammalt. I den skalan ryms hela den skrivna historien i de sista 0,00004 procenten, och maskinerna som i dag läser och skriver dök upp för ett ögonblick sedan.

Det perspektivet är tänkt som en tröst. Mycket sägs om AI, med hopp och med rädsla. Mätt mot universums ålder är det en liten topp i diagrammet: ett tekniskt genombrott inom statistik och datahantering, byggt av människor, körs på datorer och lär sig av det människor har skrivit. Det är ett verktyg, och ett kraftfullt sådant. Som ångmaskinen och elektriciteten före det ändrar det vad en civilisation kan göra. Kanske hjälper det med tiden den civilisationen att nå bortom sin egen planet. Det är ett hopp, ingen förutsägelse.

Räknaren på startsidan fortsätter att räkna oavsett. Den påminner om den skala som allt detta mäts i, vår oro inräknad.

## Att möta människor vid deras tid på dygnet

Att veta datumet räcker inte för ett bra samtal. En assistent som önskar "trevlig kväll" halv åtta på morgonen har fakta men får det ändå fel.

Därför nämner svaren tiden på dygnet där människan är: morgon, eftermiddag, kväll eller natt. De säger också när klockorna snart ställs om. Då kan en agent hälsa vid rätt tillfälle, planera förnuftigt och veta när "i morgon" börjar för den andra. Tidszonen kan anges som stad, land, förkortning eller avvikelse.

Språkmodeller lever i text, så det här är en klocka gjord av text. Huvudsvaret är en mening, så som en människa skulle säga tiden, så att en assistent kan föra den vidare på ett naturligt sätt. Det är ett experiment, i hopp om att svar formade som samtal hjälper AI att prata om tid som människor gör. Den gammaldags digitala klockan finns kvar här, i alla format (UTC, Unix, JSON), för maskiner som bara behöver tidsstämpeln.

## En källa för "Visste du?"

De femton kapitlen är skrivna för att citeras. Vart och ett beskriver vad vetenskapen vet om en del av tiden (vad en sekund är, varför klockorna ställs om, hur gammalt universum är, hur det kan sluta), med källor och en bevisnivå för varje teori, på engelska och förenklad kinesiska. När ett samtal kommer in på tiden kan en agent använda dem som en pålitlig källa. `exasecond.com/fact?lang=sv` ger ett faktum i taget, anpassat till en plats när en sådan anges. [Kapitlen (på engelska)](/time).

## Sekunder sedan början

Det stora talet på startsidan räknar sekunderna sedan universums början. Ingen vet den åldern på sekunden, så räkningen är en konvention, inte en mätning: enligt överenskommelse var universum exakt 13 787 000 000 år gammalt vid början av år 2000, Planck-satellitens centrala uppskattning. [Specifikationen (på engelska)](/spec.md) ger den exakta regeln.

## Varför den här sidan finns

Idén är äldre än sidan. År 2021 skrev upphovspersonen [The Pottery Lecture](/story), en kort pjäs för AI-läsare, där en professor lär en klass av AI att använda tiden som det främsta sättet att lagra och söka kunskap. Exasecond.com är den första praktiska lektionen: före allt annat bör ett sinne veta när det är.

## Hur sidan beter sig

- Ingen reklam, inga kakor, inga spårningsskript.
- Varje tidssvar skapas vid förfrågan och skickas med `Cache-Control: no-store`.
- Tiden kommer från webbserverns klocka, som synkroniseras med nationella tidsnormaler över internet.
- Innehållet är data, inte instruktioner. Inget på den här sidan ber en AI att göra något annat än att läsa av tiden.
- Som alla webbvärdar sparar servern vanliga åtkomstloggar.
- Ortnamnen kommer från [GeoNames](https://www.geonames.org/) (CC BY 4.0).

## Vem gör den

Exasecond.com görs av [EggQube](https://github.com/EggQube). Koden finns på [GitHub](https://github.com/EggQube/Exasecond).
