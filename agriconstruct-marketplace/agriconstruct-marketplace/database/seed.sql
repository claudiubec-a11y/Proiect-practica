-- ============================================================================
-- UtilajePro — database/seed.sql
-- ----------------------------------------------------------------------------
-- Date de test (fictive) pentru toate tabelele din schema.sql.
-- Rulează DUPĂ schema.sql:  mysql -u root -p utilajepro < database/schema.sql
--                           mysql -u root -p utilajepro < database/seed.sql
--
-- Parola pentru TOȚI utilizatorii de test (inclusiv administratori) este:
--   Parola123!
-- Hash-ul de mai jos a fost generat cu:
--   password_hash('Parola123!', PASSWORD_BCRYPT)
-- și este verificabil direct cu password_verify() în PHP 8.
-- ============================================================================

USE `utilajepro`;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 1. Utilizatori (10 utilizatori + 2 administratori)
-- ----------------------------------------------------------------------------
INSERT INTO `users`
    (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `phone`, `city`, `county`, `status`, `created_at`)
VALUES
    (1,  'Ion',    'Popescu',    'ion.popescu@exemplu.ro',     '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0722111222', 'Cluj-Napoca', 'Cluj',      'active',  '2022-03-14 10:00:00'),
    (2,  'Andrei',  'Vasilescu', 'andrei.vasilescu@exemplu.ro','$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0722345678', 'Oradea',      'Bihor',     'active',  '2021-06-02 09:30:00'),
    (3,  'Radu',    'Georgescu', 'radu.georgescu@exemplu.ro',  '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0733222111', 'Timișoara',   'Timiș',     'blocked', '2023-01-20 14:12:00'),
    (4,  'Maria',   'Popescu',   'maria.popescu@exemplu.ro',   '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0744555666', 'Iași',        'Iași',      'active',  '2023-05-11 08:45:00'),
    (5,  'Cristina','Munteanu',  'cristina.munteanu@exemplu.ro','$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq','user', '0755111222', 'Sibiu',       'Sibiu',     'active',  '2022-08-19 11:20:00'),
    (6,  'Dan',     'Pop',       'dan.pop@exemplu.ro',         '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0766222333', 'Brașov',      'Brașov',    'active',  '2022-11-02 16:05:00'),
    (7,  'Laura',   'Radu',      'laura.radu@exemplu.ro',      '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0777333444', 'Constanța',   'Constanța', 'active',  '2023-02-27 13:40:00'),
    (8,  'Mihai',   'Ionescu',   'mihai.ionescu@exemplu.ro',   '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0788444555', 'Arad',        'Arad',      'active',  '2023-04-15 10:10:00'),
    (9,  'Elena',   'Stan',      'elena.stan@exemplu.ro',      '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0799555666', 'Craiova',     'Dolj',      'active',  '2023-06-08 15:55:00'),
    (10, 'Vasile',  'Marin',     'vasile.marin@exemplu.ro',    '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'user', '0700111333', 'Ploiești',    'Prahova',   'active',  '2023-07-21 09:00:00'),
    (98, 'Admin',   'Secundar',  'admin2@utilajepro.ro',       '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'admin', '0740123457', 'Cluj-Napoca', 'Cluj',      'active',  '2021-01-01 09:00:00'),
    (99, 'Admin',   'UtilajePro','admin@utilajepro.ro',        '$2b$10$RLwadq0/78fYNUSNP0WS2OcUKbXRc0/FdsMvvghDSZysS6coHahqq', 'admin', '0740123456', 'Cluj-Napoca', 'Cluj',      'active',  '2021-01-01 09:00:00');

-- ----------------------------------------------------------------------------
-- 2. Categorii
-- ----------------------------------------------------------------------------
INSERT INTO `categories` (`id`, `slug`, `name`) VALUES
    (1, 'agricole', 'Agricole'),
    (2, 'constructii', 'Construcții');

-- ----------------------------------------------------------------------------
-- 3. Tipuri de utilaje (10 agricole + 10 construcții)
-- ----------------------------------------------------------------------------
INSERT INTO `machinery_types` (`id`, `category_id`, `slug`, `name`) VALUES
    (1,  1, 'tractor',                     'Tractor'),
    (2,  1, 'combina',                     'Combină'),
    (3,  1, 'presa-balotat',               'Presă de balotat'),
    (4,  1, 'semanatoare',                 'Semănătoare'),
    (5,  1, 'plug',                        'Plug'),
    (6,  1, 'grapa-discuri',               'Grapă cu discuri'),
    (7,  1, 'pulverizator',                'Pulverizator pentru culturi'),
    (8,  1, 'remorca-agricola',            'Remorcă agricolă'),
    (9,  1, 'cositoare',                   'Cositoare'),
    (10, 1, 'incarcator-frontal-agricol',  'Încărcător frontal agricol'),
    (11, 2, 'excavator',                   'Excavator'),
    (12, 2, 'buldoexcavator',              'Buldoexcavator'),
    (13, 2, 'buldozer',                    'Buldozer'),
    (14, 2, 'incarcator-frontal',          'Încărcător frontal'),
    (15, 2, 'mini-excavator',              'Mini-excavator'),
    (16, 2, 'macara-mobila',               'Macara mobilă'),
    (17, 2, 'compactor',                   'Compactor'),
    (18, 2, 'betoniera',                   'Betonieră'),
    (19, 2, 'platforma-ridicatoare',       'Platformă ridicătoare'),
    (20, 2, 'generator-industrial',        'Generator industrial');

-- ----------------------------------------------------------------------------
-- 4. Anunțuri (20 de utilaje: 10 agricole + 10 construcții)
-- id-urile (2001-2020) sunt identice cu cele din datele mock ale
-- frontend-ului (js/main.js), pentru compatibilitate directă.
-- ----------------------------------------------------------------------------
INSERT INTO `listings`
    (`id`, `user_id`, `machinery_type_id`, `title`, `description`, `manufacturer`, `model`,
     `sale_price`, `rental_price_per_day`, `manufacturing_year`, `operating_hours`, `engine_power`,
     `condition`, `offer_type`, `status`, `approval_status`, `city`, `county`, `created_at`)
VALUES
    (2001, 1, 1,  'Tractor New Holland T7.190', 'Tractor în stare foarte bună, unic proprietar, service la reprezentanță.', 'New Holland', 'T7.190', 62000.00, NULL, 2020, 2100, 190, 'used', 'sale',   'available', 'approved', 'Cluj-Napoca', 'Cluj', '2024-01-10 09:00:00'),
    (2002, 1, 2,  'Combină Case IH Axial-Flow 7130', 'Combină verificată tehnic, folosită exclusiv la recoltat cereale păioase.', 'Case IH', 'Axial-Flow 7130', 98500.00, NULL, 2015, 3400, 375, 'used', 'sale',   'sold',      'approved', 'Turda', 'Cluj', '2024-01-15 11:00:00'),
    (2003, 1, 3,  'Presă de balotat Krone Comprima F155', 'Presă fixă, funcționează impecabil, disponibilă și la închiriere pe sezon.', 'Krone', 'Comprima F155', 24000.00, 180.00, 2018, 1800, NULL, 'used', 'rental', 'available', 'approved', 'Dej', 'Cluj', '2024-02-02 10:30:00'),
    (2004, 2, 4,  'Semănătoare Kverneland Optima', 'Semănătoare de precizie, 8 rânduri, stare foarte bună.', 'Kverneland', 'Optima', 18500.00, NULL, 2019, 900, NULL, 'used', 'sale', 'available', 'approved', 'Oradea', 'Bihor', '2024-02-10 08:20:00'),
    (2005, 3, 5,  'Plug Lemken Juwel 8', 'Plug reversibil, 4 trupițe, aproape nou.', 'Lemken', 'Juwel 8', 9800.00, NULL, 2021, 300, NULL, 'new', 'sale', 'available', 'approved', 'Timișoara', 'Timiș', '2024-02-14 09:00:00'),
    (2006, 4, 6,  'Grapă cu discuri Vaderstad Carrier 500', 'Grapă cu discuri independente, lățime de lucru 5 m.', 'Vaderstad', 'Carrier 500', 14200.00, NULL, 2017, 1600, NULL, 'used', 'sale', 'available', 'approved', 'Iași', 'Iași', '2024-02-20 15:10:00'),
    (2007, 1, 7,  'Pulverizator Amazone UX 5200', 'Pulverizator tractat, 24 m lățime de lucru, disponibil la închiriere.', 'Amazone', 'UX 5200', 21000.00, 150.00, 2016, 2200, NULL, 'used', 'rental', 'available', 'approved', 'Baia Mare', 'Maramureș', '2024-03-01 12:00:00'),
    (2008, 2, 8,  'Remorcă agricolă Fliegl TMK 266', 'Remorcă basculantă, capacitate 16 tone.', 'Fliegl', 'TMK 266', 11500.00, NULL, 2020, 500, NULL, 'used', 'sale', 'available', 'approved', 'Sibiu', 'Sibiu', '2024-03-05 09:45:00'),
    (2009, 3, 9,  'Cositoare Pottinger Novacat 262', 'Cositoare frontală, disc dublu, disponibilă la închiriere.', 'Pottinger', 'Novacat 262', 8900.00, 80.00, 2019, 700, NULL, 'used', 'rental', 'reserved', 'approved', 'Alba Iulia', 'Alba', '2024-03-12 10:15:00'),
    (2010, 4, 10, 'Încărcător frontal agricol Manitou MLT 630', 'Utilaj versatil pentru fermă, disponibil pentru închiriere pe termen scurt.', 'Manitou', 'MLT 630', 34000.00, 220.00, 2018, 2600, 136, 'used', 'rental', 'available', 'approved', 'Bistrița', 'Bistrița-Năsăud', '2024-03-18 14:00:00'),
    (2011, 2, 11, 'Excavator Caterpillar 320D', 'Excavator verificat tehnic, echipat cu cupă standard și cupă de taluz.', 'Caterpillar', '320D', 68000.00, 450.00, 2017, 5400, 121, 'used', 'rental', 'available', 'approved', 'Oradea', 'Bihor', '2024-01-08 09:00:00'),
    (2012, 4, 12, 'Buldoexcavator JCB 3CX', 'Buldoexcavator complet funcțional, ideal pentru lucrări urbane.', 'JCB', '3CX', 55000.00, 380.00, 2020, 2100, 109, 'used', 'rental', 'rented', 'approved', 'Iași', 'Iași', '2024-01-22 11:30:00'),
    (2013, 1, 13, 'Buldozer Komatsu D65', 'Buldozer robust pentru lucrări de terasamente ample.', 'Komatsu', 'D65', 89000.00, NULL, 2014, 6200, 220, 'used', 'sale', 'available', 'approved', 'Cluj-Napoca', 'Cluj', '2024-02-01 10:00:00'),
    (2014, 3, 14, 'Încărcător frontal Volvo L120H', 'Încărcător frontal de mare capacitate, folosit în carieră.', 'Volvo', 'L120H', 112000.00, NULL, 2019, 3100, 200, 'used', 'sale', 'available', 'approved', 'Timișoara', 'Timiș', '2024-02-11 13:20:00'),
    (2015, 2, 15, 'Mini-excavator Kubota KX057', 'Mini-excavator compact, ideal pentru spații înguste.', 'Kubota', 'KX057', 42500.00, 220.00, 2021, 800, 47, 'new', 'rental', 'available', 'approved', 'Brașov', 'Brașov', '2024-02-19 09:40:00'),
    (2016, 1, 16, 'Macara mobilă Liebherr LTM 1050', 'Macară mobilă, capacitate ridicare 50 tone, disponibilă cu operator.', 'Liebherr', 'LTM 1050', 245000.00, 900.00, 2013, 7400, 367, 'used', 'rental', 'available', 'approved', 'București', 'Ilfov', '2024-03-03 16:00:00'),
    (2017, 4, 17, 'Compactor Bomag BW 213', 'Compactor cilindru neted, ideal pentru drumuri și platforme.', 'Bomag', 'BW 213', 36000.00, 270.00, 2018, 2900, 130, 'used', 'rental', 'available', 'approved', 'Constanța', 'Constanța', '2024-03-09 08:30:00'),
    (2018, 3, 18, 'Betonieră Putzmeister M36', 'Pompă de beton pe șasiu, braț 36 m.', 'Putzmeister', 'M36', 78000.00, NULL, 2016, 4100, 250, 'used', 'sale', 'sold', 'approved', 'Craiova', 'Dolj', '2024-03-15 12:10:00'),
    (2019, 2, 19, 'Platformă ridicătoare Genie GS-3232', 'Platformă foarfecă electrică, înălțime maximă 10 m.', 'Genie', 'GS-3232', 16500.00, 120.00, 2019, 1200, NULL, 'used', 'rental', 'available', 'approved', 'Ploiești', 'Prahova', '2024-03-20 09:50:00'),
    (2020, 1, 20, 'Generator industrial Caterpillar C15', 'Generator diesel, 500 kVA, aproape nou.', 'Caterpillar', 'C15', 29000.00, NULL, 2020, 950, 500, 'new', 'sale', 'available', 'approved', 'Arad', 'Arad', '2024-03-25 15:30:00'),

    -- Câteva anunțuri suplimentare, unele nerevizuite încă (approval_status = pending),
    -- pentru a putea testa fluxul de aprobare/respingere din dashboard-ul administrator.
    (2021, 5, 1, 'Tractor Fendt 720 Vario', 'Tractor performant, cutie Vario, climatizare.', 'Fendt', '720 Vario', 115000.00, NULL, 2021, 1400, 205, 'used', 'sale', 'available', 'pending', 'Sibiu', 'Sibiu', '2024-06-01 10:00:00'),
    (2022, 6, 11, 'Excavator Hitachi ZX210', 'Excavator pe șenile, cupă multifuncțională inclusă.', 'Hitachi', 'ZX210', 74000.00, 420.00, 2018, 4800, 150, 'used', 'rental', 'available', 'pending', 'Brașov', 'Brașov', '2024-06-05 11:20:00');

-- ----------------------------------------------------------------------------
-- 5. Imagini pentru anunțuri
-- Fișierele fizice corespunzătoare se află în public/uploads/images/listings/
-- (o imagine ilustrativă, generată pentru fiecare anunț în parte).
-- ----------------------------------------------------------------------------
INSERT INTO `listing_images` (`listing_id`, `image_path`, `is_primary`, `sort_order`) VALUES
    (2001, 'public/uploads/images/listings/2001-tractor.jpg', 1, 1),
    (2002, 'public/uploads/images/listings/2002-combina.jpg', 1, 1),
    (2003, 'public/uploads/images/listings/2003-presa-balotat.jpg', 1, 1),
    (2004, 'public/uploads/images/listings/2004-semanatoare.jpg', 1, 1),
    (2005, 'public/uploads/images/listings/2005-plug.jpg', 1, 1),
    (2006, 'public/uploads/images/listings/2006-grapa-discuri.jpg', 1, 1),
    (2007, 'public/uploads/images/listings/2007-pulverizator.jpg', 1, 1),
    (2008, 'public/uploads/images/listings/2008-remorca-agricola.jpg', 1, 1),
    (2009, 'public/uploads/images/listings/2009-cositoare.jpg', 1, 1),
    (2010, 'public/uploads/images/listings/2010-incarcator-frontal-agricol.jpg', 1, 1),
    (2011, 'public/uploads/images/listings/2011-excavator.jpg', 1, 1),
    (2012, 'public/uploads/images/listings/2012-buldoexcavator.jpg', 1, 1),
    (2013, 'public/uploads/images/listings/2013-buldozer.jpg', 1, 1),
    (2014, 'public/uploads/images/listings/2014-incarcator-frontal.jpg', 1, 1),
    (2015, 'public/uploads/images/listings/2015-mini-excavator.jpg', 1, 1),
    (2016, 'public/uploads/images/listings/2016-macara-mobila.jpg', 1, 1),
    (2017, 'public/uploads/images/listings/2017-compactor.jpg', 1, 1),
    (2018, 'public/uploads/images/listings/2018-betoniera.jpg', 1, 1),
    (2019, 'public/uploads/images/listings/2019-platforma-ridicatoare.jpg', 1, 1),
    (2020, 'public/uploads/images/listings/2020-generator-industrial.jpg', 1, 1),
    (2021, 'public/uploads/images/listings/2021-tractor.jpg', 1, 1),
    (2022, 'public/uploads/images/listings/2022-excavator.jpg', 1, 1);

-- ----------------------------------------------------------------------------
-- 6. Favorite
-- ----------------------------------------------------------------------------
INSERT INTO `favorites` (`user_id`, `listing_id`) VALUES
    (1, 2011), (1, 2003), (1, 2016),
    (2, 2001), (2, 2020),
    (4, 2011), (4, 2013),
    (5, 2002);

-- ----------------------------------------------------------------------------
-- 7. Conversații și mesaje
-- ----------------------------------------------------------------------------
INSERT INTO `conversations` (`id`, `buyer_id`, `seller_id`, `listing_id`, `created_at`) VALUES
    (1, 1, 2, 2011, '2024-04-01 09:50:00'),
    (2, 4, 1, 2001, '2024-04-02 16:35:00'),
    (3, 3, 1, 2002, '2024-04-03 11:10:00');

INSERT INTO `messages` (`conversation_id`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
    (1, 1, 'Bună ziua! Excavatorul este disponibil pentru închiriere în perioada dorită?', 1, '2024-04-01 09:58:00'),
    (1, 2, 'Bună ziua! Da, este liber începând de sâmbătă. Pentru câte zile aveți nevoie?', 1, '2024-04-01 10:05:00'),
    (1, 1, 'Aș avea nevoie de el pentru 4 zile, de sâmbătă până marți.', 1, '2024-04-01 10:12:00'),
    (1, 2, 'Da, utilajul este disponibil weekendul acesta.', 0, '2024-04-01 10:24:00'),
    (2, 4, 'Bună ziua, tractorul mai este disponibil?', 1, '2024-04-02 16:40:00'),
    (2, 1, 'Da, este disponibil. Pot trimite mai multe fotografii dacă doriți.', 1, '2024-04-02 17:02:00'),
    (2, 4, 'Mulțumesc pentru informații!', 0, '2024-04-02 17:10:00'),
    (3, 3, 'Se poate și cu transport inclus?', 0, '2024-04-03 11:15:00');

-- ----------------------------------------------------------------------------
-- 8. Închirieri (exemple)
-- ----------------------------------------------------------------------------
INSERT INTO `rentals` (`listing_id`, `renter_id`, `start_date`, `end_date`, `total_price`, `rental_status`, `created_at`) VALUES
    (2012, 4, '2024-06-12', '2024-06-16', 1900.00, 'completed', '2024-06-05 10:00:00'),
    (2011, 1, '2024-07-05', '2024-07-08', 1350.00, 'confirmed', '2024-07-01 09:00:00'),
    (2009, 5, '2024-05-20', '2024-05-22', 160.00, 'pending', '2024-05-18 14:00:00');

-- ----------------------------------------------------------------------------
-- 9. Jurnal de acțiuni administrative (exemple)
-- ----------------------------------------------------------------------------
INSERT INTO `admin_logs` (`admin_id`, `action`, `target_type`, `target_id`, `created_at`) VALUES
    (99, 'approve_listing', 'listing', 2001, '2024-01-10 09:15:00'),
    (99, 'approve_listing', 'listing', 2011, '2024-01-08 09:15:00'),
    (98, 'block_user', 'user', 3, '2023-01-25 10:00:00');

SET FOREIGN_KEY_CHECKS = 1;
