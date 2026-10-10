# AGENTS.md

SAGI-CDA Chía (SENA): vanilla PHP 8 + MySQL + PDO app served from XAMPP `htdocs/sagip`. No framework, no router, no build step, no tests. UI is server-rendered HTML with Bootstrap 4/5 + Tailwind classes; FontAwesome icons.

## Hard constraints (do not violate)
- ONLY these 6 files may be created/edited:
  - Hoja de Trabajo: `views/comvhtr.php`, `models/commhtr.php`, `controllers/comchtr.php`
  - Horario (Programación de Horarios): `views/comvhcm.php`, `models/commhcm.php`, `controllers/comchcm.php`
  - No other file may be modified (no CSS/JS files, no other controllers/models/views, no config, no SQL dump).
- NEVER add or modify CSS or JavaScript. No `<style>`, no `<script>`, no `onclick=`/inline handlers you invent, no new CSS classes that require `.css` changes. Existing markup classes/handlers may be reused only.
- At the END of every reply, include a short **miniresumen** (summary) of changes made in that message.

## Layout (mirror-named MVC triads)
- `views/comvXX.php` — template. Each view `require_once`s its controller at the top (`views/comvhtr.php:5`, `views/comvhcm.php:3`).
- `controllers/comcXX.php` — reads `$_POST`/`$_REQUEST`, performs save/edit/delete ops, then builds `$dat*` arrays consumed by the view. Forms post to `home.php?pg=<pg>` with a hidden `opera` field (`save`, `edi`, `eli`, `acti`, `save_horario`, `add_horario`, `add_instructor`, ...).
- `models/commXX.php` — PDO classes, one per feature. `Mhdt` (htr), `Mhorc` (hcm). Standard getters/setters + `getAll()`, `getOne()`, `save()`, `edit()`, `del()`.
- `comvhcm.php` also requires `comchtr.php` and `comcemp.php`, so classes from those flows are already loaded — do not redeclare.

## Routing (how a page loads)
- No router: `home.php` reads `?pg=` and `validar($pg)` (`controllers/cmenu.php` -> `models/mmenu.php::getVal`) looks up the `pagina` table in MySQL db `sagisena`, then `require_once`s column `rutpag`.
- Relevant pages (from `bd/sagisena202609220755.sql`): pg `2004` → `views/comvhtr.php` (Hoja de Trabajo), pg `2002` → `views/comvhcm.php` (Horario), pg `2010` → `views/comvhtr.php` (Reporte). Default `$pg` fallback is `'2000'`.
- Access is role-gated via `pagper` and `$_SESSION['idper']`. `home.php` first requires `models/seguridad.php` (session auth token check). Hot-cache DB is `models/configuracion.php` (localhost/root/blank, db `sagisena`; a commented prod block exists — do not enable).

## DB facts you'll need
- `hojatra` (hoja de trabajo): `idnorad, codpro, idemp, codproesp, codslem, feclini, feclin, cupo, jornada, idfic, convht, act`. `act=1` activa, anything else inactiva.
- `horario` stores per-day times; keyed by `idnorad`, `iddia` (valor day id), `hinihor`/`hfinhor`, instructor `idusu`, and optional `fecha_especifica` (date). `Mhorc::save()`/`delPorFecha()` handle re-saving per date.
- Lookup tables in `valor`: `iddom=1` jornada (1083 = Complementaria), `iddom=25` programa especial, `iddom=14` días (parval 1..7 = Lunes..Domingo).
- There is a runtime demo mode: if `hojatra` is empty, ids `900001..900006` are served from memory in `Mhdt` (`esDemo()`/`getAllDemo()`/`datosDemoUno()` in `models/commhtr.php`) so report/export pages work without DB rows.
- `Mhdt::reporte($idnorad)` is the data source for PDF/Excel; `exportarPDF()` (dompdf) and `exportarExcel()` (PhpSpreadsheet) call `ob_clean()` before streaming, and `exportarExcel` registers `MyCLabs\Enum` PSR-4 at runtime.

## Gotchas & conventions
- Alerts/redirects are done inside PHP as `echo '<script>...';` — keep that pattern; it is not "adding JS to a file", it's the established PHP idiom here.
- The DataTables grids are `<table id="example">` (activas), `#myt` (inactivas), `#tblTodos` (todas) with `data-search`/`data-order` attributes on the `td`/`tr`; there is also `#frmins` form and KPI cards fed by `$totalCursos/$cursosActivos/$cursosInactivos`. Reuse existing ids/classes; never invent new ones that need CSS.
- Code conventions: PDO prepared statements with `:param` + `bindParam`; `try/catch` emitting `alert()` via `ManejoError($e)` when it exists; Spanish comments/strings; `htmlspecialchars()` on echoed user data.

## Verification
- No test/lint/CI pipeline. Run `php -l <file>` on every touched PHP file (PHP 8.2.12 available on PATH). The app is reached at `http://localhost/sagip/` (require login via `index.php`).