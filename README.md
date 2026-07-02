Rezumat proiect – Marketplace pentru utilaje agricole și utilaje pentru construcții
Titlul proiectului

AgriConstruct Marketplace – Platformă web pentru vânzarea și închirierea utilajelor agricole și a utilajelor pentru construcții

Descriere generală

Proiectul constă în dezvoltarea unei aplicații web de tip Marketplace, destinată persoanelor și companiilor care doresc să cumpere, să vândă sau să închirieze utilaje agricole și utilaje pentru construcții.

Platforma permite utilizatorilor să publice anunțuri pentru utilaje, să caute și să filtreze anunțurile existente, să contacteze vânzătorii și să gestioneze procesul de închiriere prin intermediul unui calendar de disponibilitate și al unui sistem automat de calcul al costurilor.

Aplicația include atât funcționalități pentru utilizatorii obișnuiți, cât și un panou administrativ pentru gestionarea platformei.

Tehnologii utilizate
Frontend
HTML5 – pentru structura paginilor web;
CSS3 – pentru design și stilizare;
JavaScript (Vanilla JavaScript) – pentru interactivitate și validarea formularelor.
Backend
PHP 8 – utilizat pentru logica aplicației, autentificare, gestionarea anunțurilor și comunicarea cu baza de date.
Baza de date
MySQL – utilizată pentru stocarea utilizatorilor, anunțurilor, mesajelor, favoritelor și închirierilor.
Server local de dezvoltare
XAMPP:
Apache Web Server;
MySQL Database Server;
phpMyAdmin pentru administrarea bazei de date.
Arhitectura aplicației

Aplicația este dezvoltată folosind arhitectura MVC (Model – View – Controller).

Model

Gestionează accesul la baza de date și operațiile CRUD.

Exemple:

User.php
Listing.php
Rental.php
Message.php
View

Reprezintă interfața utilizatorului și este implementată folosind HTML, CSS și JavaScript.

Controller

Conține logica aplicației și gestionează cererile utilizatorilor.

Exemple:

AuthController.php
ListingController.php
UserController.php
AdminController.php
Funcționalități implementate
Gestionarea conturilor
înregistrare utilizatori;
autentificare și deconectare;
roluri de utilizator și administrator.
Gestionarea anunțurilor
adăugare anunț;
modificare anunț;
ștergere anunț;
încărcare imagini;
alegerea tipului de anunț:
vânzare;
închiriere.
Căutare și filtrare
după categorie;
după tipul utilajului;
după preț;
după localitate;
după anul fabricației;
după starea utilajului.
Pagina de detalii
galerie foto;
descriere completă;
specificații tehnice;
date de contact ale vânzătorului.
Favorite
salvarea anunțurilor preferate.
Mesagerie internă
comunicare directă între cumpărător și vânzător.
Funcționalități pentru închiriere
calendar de disponibilitate;
alegerea perioadei de închiriere;
calcul automat al costului total;
afișarea statusului utilajului:
disponibil;
rezervat;
închiriat;
vândut.
Funcționalități administrative
dashboard cu statistici;
aprobarea anunțurilor;
ștergerea anunțurilor neconforme;
blocarea utilizatorilor.
Utilaje disponibile în aplicație
Utilaje agricole
Tractor
Combină agricolă
Semănătoare
Plug
Grapă cu discuri
Cultivator
Presă de balotat
Mașină de erbicidat
Remorcă agricolă
Încărcător frontal agricol
Utilaje pentru construcții
Excavator
Buldoexcavator
Buldozer
Macara
Încărcător frontal
Cilindru compactor
Finisor asfalt
Autobetonieră
Miniexcavator
Stivuitor
Designul aplicației

Interfața utilizează un design modern și responsive, adaptat atât pentru desktop, cât și pentru dispozitive mobile.

Caracteristicile designului:

interfață minimalistă;
culori moderne și profesionale;
componente responsive;
experiență de utilizare intuitivă.
Concluzie

AgriConstruct Marketplace reprezintă o soluție completă pentru digitalizarea procesului de vânzare și închiriere a utilajelor agricole și a utilajelor pentru construcții, oferind utilizatorilor o platformă modernă, intuitivă și eficientă pentru gestionarea anunțurilor și a tranzacțiilor.
