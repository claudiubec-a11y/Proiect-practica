# AgriConstruct Marketplace — Backend (PHP 8 + MySQL, gata pentru XAMPP)

Backend complet, arhitectură MVC, pentru marketplace-ul de utilaje agricole
și de construcții. Proiectul este pregătit să ruleze direct într-o instalare
XAMPP standard, fără configurare de virtual host.

## 1. Instalare rapidă (XAMPP)

1. Copiază **întreg conținutul acestui folder** în:
   ```
   C:\xampp\htdocs\agriconstruct-marketplace\        (Windows)
   /Applications/XAMPP/htdocs/agriconstruct-marketplace/   (macOS)
   /opt/lampp/htdocs/agriconstruct-marketplace/            (Linux)
   ```
2. Pornește **Apache** și **MySQL** din XAMPP Control Panel.
3. Creează baza de date rulând schema, fie din phpMyAdmin (Import), fie din linia de comandă:
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```
   (implicit XAMPP are userul `root` fără parolă, deci poți omite `-p`)
4. Deschide în browser: **http://localhost/agriconstruct-marketplace/**
   Ar trebui să vezi un răspuns JSON de tipul:
   ```json
   { "success": true, "message": "API-ul UtilajePro rulează corect.", "data": { "status": "ok", ... } }
   ```
   Dacă vezi acest răspuns, backend-ul este instalat corect.

Nu este nevoie de Composer, `npm install` sau alte dependențe — este PHP 8 nativ.

## 2. De ce funcționează din orice subfolder

`index.php` detectează automat calea de bază (numele folderului din `htdocs`)
citind `SCRIPT_NAME`, deci proiectul funcționează identic dacă îl redenumești
sau îl muți în alt subfolder — nu există nicio cale hardcodată către
`agriconstruct-marketplace`.

## 3. Structura proiectului (MVC)

```
agriconstruct-marketplace/
├── index.php              <- front controller unic (punct de intrare)
├── .htaccess               <- rescrie toate cererile către index.php
│
├── config/
│   ├── autoload.php        <- autoloader pentru TOATE clasele (core/models/controllers/middleware)
│   ├── database.php        <- conexiune PDO (singleton), credențiale implicite XAMPP
│   └── bootstrap.php        <- sesiune, CORS, headere JSON, handler global de erori
│
├── routes/
│   └── api.php              <- tabelul central de rute (metodă + cale -> controller)
│
├── controllers/             <- AuthController, UserController, ListingController,
│                                FavoriteController, MessageController, RentalController,
│                                AdminController, BaseController
│
├── models/                  <- User, Listing, Favorite, Message, Rental, Category
│                                (Category gestionează și `machinery_types`)
│
├── middleware/               <- AuthMiddleware, AdminMiddleware, RoleMiddleware
│
├── core/                     <- Response.php (JSON standardizat), Validator.php
│                                (extindere minimă, necesară pentru a evita
│                                 codul duplicat în cele 7 controllere)
│
├── public/                   <- SINGURUL folder cu fișiere publice (imagini
│   └── uploads/images/listings/   încărcate pentru anunțuri); protejat cu
│                                    .htaccess împotriva execuției de scripturi
│                                    (conține deja 22 de imagini ilustrative,
│                                     câte una pentru fiecare anunț din seed.sql)
│
└── database/
    ├── schema.sql            <- toate tabelele, FK-uri, indecși
    └── seed.sql               <- date de test (utilizatori, anunțuri, mesaje...)
```

Fiecare folder intern (`config`, `routes`, `controllers`, `models`,
`middleware`, `core`, `database`) are propriul `.htaccess` cu `Require all
denied`, ca protecție suplimentară — chiar dacă `AllowOverride` nu ar fi
configurat corect la rădăcină, aceste fișiere tot nu pot fi accesate direct
din browser.

## 4. Bază de date

Numele bazei de date este **`utilajepro`** (conform cerinței), configurat în
`config/database.php`:

```php
private const DB_HOST = 'localhost';
private const DB_NAME = 'utilajepro';
private const DB_USER = 'root';
private const DB_PASS = '';
```

Aceste valori sunt cele implicite pentru XAMPP și funcționează fără nicio
modificare. Dacă ai altă configurație MySQL, editează constantele de mai sus
sau setează variabilele de mediu `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS`
(au prioritate dacă sunt definite).

## 5. Cont de administrator (din seed.sql)

| Email | Parolă | Rol |
|---|---|---|
| admin@utilajepro.ro | Parola123! | admin |
| admin2@utilajepro.ro | Parola123! | admin |
| ion.popescu@exemplu.ro | Parola123! | user |

Toți utilizatorii din `seed.sql` folosesc aceeași parolă de test: `Parola123!`

## 6. Testare rapidă (fără frontend)

```bash
# Health check
curl http://localhost/agriconstruct-marketplace/

# Login (salvează sesiunea într-un fișier cookie)
curl -c cookies.txt -X POST http://localhost/agriconstruct-marketplace/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@utilajepro.ro","password":"Parola123!"}'

# Cerere autentificată, folosind cookie-ul salvat mai sus
curl -b cookies.txt http://localhost/agriconstruct-marketplace/admin/dashboard

# Listare anunțuri (public, fără autentificare)
curl "http://localhost/agriconstruct-marketplace/listings?categorie=agricole&sort=pret-crescator"
```

## 7. Conectarea frontend-ului existent

Frontend-ul (HTML/CSS/JS, nemodificat) folosește în prezent date mock din
`js/main.js` (LocalStorage). Pentru a-l conecta la acest backend, înlocuiește
apelurile mock cu `fetch()` către rutele de mai jos, păstrând
`credentials: 'include'` (necesar ca sesiunea PHP să fie trimisă):

```javascript
fetch('http://localhost/agriconstruct-marketplace/listings', {
  credentials: 'include'
})
  .then(r => r.json())
  .then(({ data }) => console.log(data.items));
```

Dacă frontend-ul rulează pe alt port/origin (ex: Live Server pe :5500),
setează variabila de mediu `FRONTEND_ORIGIN` în `config/bootstrap.php` la
originea exactă (CORS nu permite `*` împreună cu `credentials: include`).

## 8. Endpoint-uri disponibile

Toate rutele sunt relative la `http://localhost/agriconstruct-marketplace`.

### Auth
| Metodă | Rută | Acces |
|---|---|---|
| POST | /register | public |
| POST | /login | public |
| POST | /logout | autentificat |
| POST | /password/forgot | public |
| POST | /password/reset | public |
| PUT | /password/change | autentificat |

### Users
| GET | /users/profile | autentificat |
| PUT | /users/profile | autentificat |

### Listings
| GET | /listings | public — filtre: `categorie`, `tip_utilaj`, `pret_min`, `pret_max`, `judet`, `oras`, `an_min`, `an_max`, `stare[]`, `tip_oferta[]`, `status`, `q`, `sort`, `page`, `per_page` |
| GET | /listings/{id} | public |
| GET | /listings/mine | autentificat |
| POST | /listings | autentificat |
| PUT | /listings/{id} | autentificat + proprietar |
| DELETE | /listings/{id} | autentificat + proprietar sau admin |

### Favorites
| GET | /favorites | autentificat |
| POST | /favorites | autentificat |
| DELETE | /favorites/{id} | autentificat |

### Messages
| GET | /messages | autentificat |
| GET | /messages/{conversationId} | autentificat |
| POST | /messages | autentificat |

### Rentals
| GET | /rentals | autentificat |
| POST | /rentals | autentificat |

### Admin (necesită rol = admin)
| GET | /admin/dashboard |
| GET | /admin/listings |
| PUT | /admin/listings/{id}/approve |
| PUT | /admin/listings/{id}/reject |
| DELETE | /admin/listings/{id} |
| GET | /admin/users |
| PUT | /admin/users/{id}/block |
| PUT | /admin/users/{id}/unblock |

## 9. Format standard de răspuns

```json
{ "success": true, "message": "...", "data": { ... } }
{ "success": false, "message": "...", "errors": { "email": ["..."] } }
```

## 10. Securitate

- Parole: `password_hash()` / `password_verify()` (bcrypt)
- Toate interogările folosesc PDO cu **prepared statements** (protecție SQL Injection)
- Sesiuni PHP `httpOnly`; `session_regenerate_id()` la login (previne session fixation)
- Middleware de autentificare/rol pe toate rutele private
- `public/.htaccess` blochează execuția de scripturi PHP în folderul de imagini
- `.htaccess` de tip `deny all` în fiecare folder intern (config, models etc.)
- Validare server-side pe toate input-urile (`core/Validator.php`)

## 11. Depanare

| Simptom | Cauză probabilă |
|---|---|
| Pagină albă / 500 la orice cerere | Verifică `error_log` din Apache (XAMPP: `xampp/apache/logs/error.log`) |
| „Nu s-a putut realiza conexiunea la baza de date” | MySQL nu rulează, sau baza `utilajepro` nu a fost creată — rulează `database/schema.sql` |
| 404 la orice rută în afară de `/` | `mod_rewrite` dezactivat sau `AllowOverride None` în `httpd.conf` — vezi comentariul din `.htaccess` |
| CORS blocat în consola browserului | Setează `FRONTEND_ORIGIN` explicit (nu `*`) când folosești `credentials: include` |
