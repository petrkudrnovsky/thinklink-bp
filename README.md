# ThinkLink - bakalářská práce

## Popis projektu
ThinkLink je webová aplikace, která umožňuje uživatelům nahrávat a sdílet svoje poznámky na webu. Nejzajímavější součástí aplikace je automatické propojování poznámek na základě obsahové podobnosti. Tímto způsobem může uživatel objevit nové souvislosti mezi svými poznámkami a získat nové poznatky.

## Set-up projektu
#### 1. Naklonování repozitáře
``` 
git clone git@gitlab.fit.cvut.cz:kudrnpe3/thinklink.git thinklink
cd thinklink
```
#### 2. Nastavení prostředí
Ve souboru `docker-compose.yml` si nastavte Vašeho uživatele a skupinu. Je to důležité, aby soubory vytvořené v rámci Dockeru (např. migrace, [Symfony MakerBundle](https://symfony.com/bundles/SymfonyMakerBundle/current/index.html) apod.) měly správného vlastníka a šly upravovat i mimo kontejner Dockeru.
```
# příklad nastavení pro uživatele petr s UID 1000 a GID 1000
args:
    - USER_ID=${UID:-1000}
    - GROUP_ID=${GID:-1000}
    - USER_NAME=${USER_NAME:-petr}
    - GROUP_NAME=${GROUP_NAME:-petr}
```
#### 3. Spuštění Dockeru
```
docker compose up -d
```
#### 4. Instalace závislostí
```
docker compose exec -it thinklink-app composer install
```
#### 5. Vytvoření databáze
```
# to-do
```
#### 6. Webový server
Aplikace běží na adrese [http://localhost:8080](http://localhost:8080).
## Autor práce a vedoucí práce
Autorem práce je Petr Kudrnovský, student ČVUT FIT.

Vedoucím práce je Ing. David Bernhauer, Ph.D.