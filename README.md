# Flyttekasse styring

En lille web-app til at holde styr på flyttekasser, rum og indhold – med søgning og en print-/PDF-venlig oversigt pr. flyttekasse.

Appen er lavet til at køre “server-only” på et simpelt lokalt webserver-setup (fx MAMP), så data kan gemmes stabilt i en `data.json` ved siden af appen.

## Funktioner

- Opret **Rum**
- Opret **Flyttekasser** (autonummerering) med rum, dato og note
- Opret **Indhold** (varer) med antal + enhed (stk., kg., sæt, pk.) og note
- **Søg** efter varer og se hvilke kasser de ligger i
- **Slå kasse op** og se indhold i en liste
- **Rediger/Slet** rum, flyttekasser og varer
- **Print / Gem som PDF** med pæn opsætning
- **Projekt**: indtast projektnavn/adresse (vises i toppen og på print)

## Struktur

```
flyttekasser/
  index.html
  data.json
  load.php
  save.php
  README.md
```

- `data.json` er den eneste datafil.
- `load.php` læser `data.json`
- `save.php` overskriver `data.json` ved ændringer (autosave)

## Kom i gang med MAMP (macOS)

1. Installer og start **MAMP** (kun Apache er nødvendig)
2. Kopiér mappen `flyttekasser` til:

   `/Applications/MAMP/htdocs/`

3. Åbn i browser:

   `http://localhost:8888/flyttekasser/`

### Test af endpoints

- `http://localhost:8888/flyttekasser/load.php`
- `http://localhost:8888/flyttekasser/data.json`

## Åbn på iPhone/iPad på samme netværk

1. Find din Macs lokale IP-adresse (fx `192.168.1.23`)
2. Sørg for at MAMP/Apache kører
3. Åbn på mobilen:

   `http://<DIN_IP>:8888/flyttekasser/`

> Tip: macOS firewall kan blokere. Tillad indgående forbindelser til Apache/MAMP hvis nødvendigt.

## Print / PDF

På siden “Kasseopslag” kan du vælge en kasse og trykke **Print**.
I browserens print-dialog kan du vælge **Gem som PDF**.

## Dataformat

`data.json` indeholder:

- `projectName` (string)
- `rooms[]` (id + name)
- `boxes[]` (id + boxNr + roomId + createdAt + note)
- `items[]` (id + boxNr + name + qty + unit + note + createdAt)

## GitHub forslag

- Tilføj en licensfil (fx **MIT**) som `LICENSE`
- Overvej at tilføje et screenshot i `README.md` (fx `docs/screenshot.png`)
- Brug GitHub Releases hvis du vil distribuere zip-filer

## Licens

Vælg selv en licens (fx MIT) og tilføj `LICENSE` fil, hvis du deler projektet offentligt.
