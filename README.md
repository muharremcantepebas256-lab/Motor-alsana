# Motor Alsana (PHP + MySQL)

Kucuk bir motosiklet saticisi icin basit ilan sistemi:
- Ilan ekleme
- Ilan listeleme ve detay
- Kullanici kayit/giris
- Favori ekleme
- Fiyat dusunce e-posta bildirimi (`check_prices.php`)

## Dosya ve klasorlar

- `config/db.php`: MySQL baglantisi.
- `config/auth.php`: Session ve giris kontrol fonksiyonlari.
- `index.php`: Ana sayfa, tum ilanlar listelenir.
- `add_listing.php`: Giris yapan kullanici yeni ilan ekler.
- `listing.php`: Ilan detay sayfasi + favori + iletisime gec.
- `favorite.php`: Ilani favorilere ekler, o anki fiyati `last_price` olarak kaydeder.
- `my_favorites.php`: Kullanicinin favori ilanlari.
- `register.php`: Kullanici kayit.
- `login.php`: Kullanici giris.
- `logout.php`: Cikis.
- `check_prices.php`: Fiyat dususu kontrolu yapar, e-posta yollar, `last_price` gunceller.
- `schema.sql`: Veritabani tablolari.
- `uploads/`: Yuklenen fotograf dosyalari.
- `assets/style.css`: Basit arayuz stilleri.

## Adim adim kurulum

1. XAMPP'te `Apache` ve `MySQL` servislerini baslat.
2. `phpMyAdmin` ac (`http://localhost/phpmyadmin`).
3. `schema.sql` dosyasini calistir.
4. Gerekirse `config/db.php` icindeki veritabani bilgilerini guncelle:
   - host: `127.0.0.1`
   - user: `root`
   - pass: ``
   - db: `motor_alsana`
5. Tarayicida su adrese git:
   - `http://localhost/Motor%20alsana/`
6. Once kayit ol (`register.php`), sonra giris yap (`login.php`).
7. `Motor Ekle` sayfasindan ilan olustur, fotograf yukle.
8. Ana sayfadan ilani acip `Favoriye Ekle` butonuna bas.

## Fiyat dususu bildirim mantigi

1. Kullanici bir ilani favoriye ekledigi anda o ilanin anlik fiyati `favorites.last_price` alanina kaydedilir.
2. `check_prices.php` calisinca:
   - tum favorileri gezer,
   - `listings.price` ile `favorites.last_price` degerini karsilastirir,
   - yeni fiyat daha dusukse `mail()` ile e-posta yollar,
   - sonra `last_price` alanini yeni fiyata gunceller.

## check_prices.php nasil calistirilir?

Manuel test:
```bash
php check_prices.php
```

Cron (Linux) ornegi:
```bash
*/10 * * * * /usr/bin/php /path/to/project/check_prices.php
```

Windows Task Scheduler ile de benzer sekilde periyodik calistirabilirsiniz.

## Notlar

- SQL injection icin tum kritik sorgular `prepared statement` ile yazilmistir.
- Giris/kayit/ilan ekleme alanlarinda temel input kontrolu vardir.
- `mail()` fonksiyonunun localhost'ta calismasi icin SMTP ayari gerekir (php.ini / sendmail).
